<?php

namespace Dock26Cookies;

if (!\defined('ABSPATH')) {
    exit;
}

/**
 * Replaces third-party iframes with the placeholder element that the frontend
 * bundle (iframemanager) turns into a consent notice and, once the visitor
 * agrees, into the real iframe.
 */
final class Embeds
{
    /** Service for iframes of providers the plugin does not know. */
    public const GENERIC_SERVICE = 'iframe';

    /** Attributes of the original iframe that the loaded iframe gets back. */
    private const PASSED_IFRAME_ATTRIBUTES = ['title', 'allow', 'allowfullscreen', 'referrerpolicy', 'sandbox', 'name'];

    public static function init(): void
    {
        // Blocks, including Custom HTML and the templates of block themes.
        add_filter('render_block', [self::class, 'gate_block'], 20, 2);
        // Classic content; runs after shortcodes and auto-embeds.
        add_filter('the_content', [self::class, 'gate_iframes'], 20);
        add_filter('widget_text_content', [self::class, 'gate_iframes'], 20);
        add_filter('widget_custom_html_content', [self::class, 'gate_iframes'], 20);
    }

    /**
     * The consent placeholder for one embed, or an empty string if the
     * service or id is not acceptable.
     *
     * @param string $thumbnail Optional preview image. It is requested before
     *                          the visitor consents, so callers must only
     *                          pass first-party URLs.
     * @param array<string, string> $iframe_attributes Attributes for the iframe once it loads.
     * @param string $size Inline CSS sizing the placeholder (see size_css());
     *                     full width at 16:9 if empty.
     * @param string $params Query string appended to the service's embed URL.
     */
    public static function placeholder(
        string $service,
        string $id,
        string $thumbnail = '',
        array $iframe_attributes = [],
        string $size = '',
        string $params = ''
    ): string {
        if (!CookieConfig::isValidServiceId($service, $id)) {
            return '';
        }

        $attributes = \sprintf('data-service="%s" data-id="%s"', esc_attr($service), esc_attr($id));
        if ($service === self::GENERIC_SERVICE) {
            $attributes .= \sprintf(' data-d26-sig="%s"', esc_attr(self::sign($id)));
        }
        if ($thumbnail !== '') {
            $attributes .= \sprintf(' data-thumbnail="%s"', esc_url($thumbnail));
        }
        if ($params !== '') {
            $attributes .= \sprintf(' data-params="%s"', esc_attr($params));
        }
        if ($size !== '') {
            $attributes .= \sprintf(' style="%s"', esc_attr($size));
        }

        $iframe_attributes += ['loading' => 'lazy', 'frameborder' => '0'];
        foreach ($iframe_attributes as $name => $value) {
            $attributes .= \sprintf(' data-iframe-%s="%s"', esc_attr($name), esc_attr($value));
        }

        return '<div ' . $attributes . ' data-autoscale></div>';
    }

    /**
     * Gates the iframes of one rendered block.
     *
     * @param string $block_content
     * @param array<string, mixed> $block
     */
    public static function gate_block($block_content, $block): string
    {
        $block_content = (string) $block_content;

        if (($block['blockName'] ?? '') === 'core/embed' && !self::is_passthrough_request()) {
            // The wrapper goes: its aspect-ratio spacer would add a second
            // box on top of the one the placeholder brings itself.
            $block_content = \preg_replace(
                '~<div class="wp-block-embed__wrapper">\s*(<iframe\b[^>]*>.*?</iframe>)\s*</div>~is',
                '$1',
                $block_content
            ) ?? $block_content;

            // Embed blocks fill their container whatever width oEmbed reported.
            return self::replace_iframes($block_content, true);
        }

        return self::gate_iframes($block_content);
    }

    /**
     * Swaps every third-party iframe in $html for a consent placeholder.
     * Running it twice over the same markup changes nothing.
     *
     * @param string $html
     */
    public static function gate_iframes($html): string
    {
        return self::replace_iframes((string) $html, false);
    }

    /** @param bool $fluid Whether placeholders ignore the width of their iframe. */
    private static function replace_iframes(string $html, bool $fluid): string
    {
        if (self::is_passthrough_request()) {
            return $html;
        }

        $html = self::neutralise_foreign_placeholders($html);

        if (\stripos($html, '<iframe') === false) {
            return $html;
        }

        return \preg_replace_callback(
            '~<iframe\b[^>]*>.*?</iframe>~is',
            static fn (array $matches): string => self::gate_iframe($matches[0], $fluid),
            $html
        ) ?? '';
    }

    /**
     * Feeds and editor previews don't run the frontend bundle, so a
     * placeholder would stay empty there.
     */
    private static function is_passthrough_request(): bool
    {
        return is_feed() || is_admin() || wp_is_serving_rest_request();
    }

    /** The placeholder for one iframe element, or the iframe itself if it is first-party. */
    private static function gate_iframe(string $iframe, bool $fluid): string
    {
        $tag = new \WP_HTML_Tag_Processor($iframe);
        if (!$tag->next_tag(['tag_name' => 'iframe'])) {
            return '';
        }

        $src = $tag->get_attribute('src');
        $src = \is_string($src) ? \trim($src) : '';
        if (\str_starts_with($src, '//')) {
            $src = 'https:' . $src;
        }

        // No src, a relative one, about:blank, srcdoc only: nothing is
        // requested from a third party.
        if (\preg_match('~^https?://~i', $src) !== 1) {
            return $iframe;
        }

        $host = \strtolower((string) wp_parse_url($src, PHP_URL_HOST));
        if ($host === \strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST))) {
            return $iframe;
        }

        $iframe_attributes = [];
        foreach (self::PASSED_IFRAME_ATTRIBUTES as $name) {
            $value = $tag->get_attribute($name);
            if ($value !== null) {
                $iframe_attributes[$name] = $value === true ? '' : $value;
            }
        }

        $size = self::size_css($tag->get_attribute('width'), $tag->get_attribute('height'), $fluid);

        [$service, $id, $params] = self::resolve_service($src);
        $html = self::placeholder($service, $id, '', $iframe_attributes, $size, $params);

        if ($html === '' && $service !== self::GENERIC_SERVICE) {
            $html = self::placeholder(self::GENERIC_SERVICE, self::generic_id($src), '', $iframe_attributes, $size);
        }

        // An empty string drops the iframe: it never gets through ungated.
        return $html;
    }

    /**
     * Which service an iframe URL belongs to.
     *
     * @return array{0: string, 1: string, 2: string} Service, id and extra query string.
     */
    private static function resolve_service(string $src): array
    {
        $video_id = self::extract_youtube_id($src);
        if ($video_id !== null) {
            return ['youtube', $video_id, (string) wp_parse_url($src, PHP_URL_QUERY)];
        }

        // The raw `pb` value: iframemanager puts it into the embed URL as is.
        if (
            \preg_match('~^https?://(?:www\.)?google\.[a-z.]+/maps/embed\?(?:[^#]*&)?pb=([^&#]+)~i', $src, $matches)
            && CookieConfig::isValidServiceId('googlemaps', $matches[1])
        ) {
            return ['googlemaps', $matches[1], ''];
        }

        return [self::GENERIC_SERVICE, self::generic_id($src), ''];
    }

    /** The iframe URL as the id of the generic service. */
    private static function generic_id(string $src): string
    {
        // iframemanager inserts the id with String.replace(), where "$" has
        // a special meaning.
        return \str_replace('$', '%24', esc_url_raw($src, ['http', 'https']));
    }

    /**
     * Inline CSS that gives a placeholder the proportions of its iframe; the
     * --d26-ratio rule itself comes from CookieConfig::getIframeStylesCss().
     *
     * @param string|true|null $width
     * @param string|true|null $height
     */
    private static function size_css($width, $height, bool $fluid): string
    {
        if (!\is_string($height) || !\ctype_digit($height) || (int) $height === 0) {
            return '';
        }

        if (\is_string($width) && \ctype_digit($width) && (int) $width > 0) {
            $css = '--d26-ratio:' . \round((int) $height / (int) $width * 100, 4) . '%';

            return $fluid ? $css : $css . ';max-width:' . (int) $width . 'px';
        }

        // Fluid width ("100%") with a fixed height.
        return '--d26-ratio:' . (int) $height . 'px';
    }

    /**
     * Generic placeholders load any URL, so only the ones this class built
     * may stay. Anything else (kses lets authors write data attributes) loses
     * its data-service and with it the placeholder behaviour.
     */
    private static function neutralise_foreign_placeholders(string $html): string
    {
        if (\stripos($html, 'data-service') === false) {
            return $html;
        }

        $tags = new \WP_HTML_Tag_Processor($html);
        while ($tags->next_tag()) {
            $service = $tags->get_attribute('data-service');
            if (!\is_string($service) || \strtolower(\trim($service)) !== self::GENERIC_SERVICE) {
                continue;
            }

            $id = $tags->get_attribute('data-id');
            $signature = $tags->get_attribute('data-d26-sig');
            if (!\is_string($id) || !\is_string($signature) || !\hash_equals(self::sign($id), $signature)) {
                $tags->remove_attribute('data-service');
            }
        }

        return $tags->get_updated_html();
    }

    private static function sign(string $id): string
    {
        return \substr(wp_hash('dock26-cookies-iframe|' . $id), 0, 20);
    }

    private static function extract_youtube_id(string $url): ?string
    {
        $pattern = '~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]+)~';

        if (\preg_match($pattern, $url, $matches) && CookieConfig::isValidServiceId('youtube', $matches[1])) {
            return $matches[1];
        }

        return null;
    }
}
