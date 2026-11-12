<?php

namespace Dock26Cookies;

if (!\defined('ABSPATH')) {
    exit;
}

/**
 * Replaces third-party embeds with the placeholder element that the frontend
 * bundle (iframemanager) turns into a consent notice and, once the visitor
 * agrees, into the real iframe.
 */
final class Embeds
{
    public static function init(): void
    {
        add_filter('render_block_core/embed', [self::class, 'gate_embed_block'], 10, 2);
    }

    /**
     * The consent placeholder for one embed, or an empty string if the
     * service or id is not acceptable.
     *
     * @param string $thumbnail Optional preview image. It is requested before
     *                          the visitor consents, so callers must only
     *                          pass first-party URLs.
     */
    public static function placeholder(string $service, string $id, string $thumbnail = ''): string
    {
        if (!CookieConfig::isValidServiceId($service, $id)) {
            return '';
        }

        $attributes = \sprintf('data-service="%s" data-id="%s"', esc_attr($service), esc_attr($id));
        if ($thumbnail !== '') {
            $attributes .= \sprintf(' data-thumbnail="%s"', esc_url($thumbnail));
        }

        return '<div ' . $attributes . ' data-autoscale data-iframe-loading="lazy" data-iframe-frameborder="0"></div>';
    }

    /**
     * Swaps the iframe of a YouTube embed block for the consent placeholder.
     * The surrounding <figure> (alignment, caption) stays untouched.
     *
     * @param string $block_content
     * @param array<string, mixed> $block
     */
    public static function gate_embed_block($block_content, $block): string
    {
        $block_content = (string) $block_content;
        $attrs = $block['attrs'] ?? [];

        // Feeds and editor previews don't run the frontend bundle, so a
        // placeholder would stay empty there.
        if (is_feed() || is_admin() || wp_is_serving_rest_request()) {
            return $block_content;
        }

        if (($attrs['providerNameSlug'] ?? '') !== 'youtube' || empty($attrs['url']) || !\is_string($attrs['url'])) {
            return $block_content;
        }

        $video_id = self::extract_youtube_id($attrs['url']);
        if ($video_id === null) {
            return $block_content;
        }

        $placeholder = self::placeholder('youtube', $video_id);

        // The wrapper goes too: its aspect-ratio spacer would add a second
        // 16:9 box on top of the one the placeholder brings itself.
        $gated = \preg_replace(
            '~<div class="wp-block-embed__wrapper">.*?</div>~s',
            $placeholder,
            $block_content,
            1,
            $count
        );
        if ($gated !== null && $count === 1) {
            return $gated;
        }

        // Unexpected markup: make sure no iframe gets through ungated.
        return \preg_replace('~<iframe\b[^>]*>.*?</iframe>~is', $placeholder, $block_content) ?? '';
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
