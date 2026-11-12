<?php

namespace Dock26Cookies;

if (!\defined('ABSPATH')) {
    exit;
}

/**
 * [dock26_iframe_content service="youtube" id="VIDEO_ID" thumbnail="123"]
 *
 * `thumbnail` is optional: a media library attachment ID or an image URL on
 * this site.
 */
final class Shortcode
{
    private const TAG = 'dock26_iframe_content';

    public static function register(): void
    {
        add_shortcode(self::TAG, [self::class, 'render']);
    }

    /** @param array<string, string>|string $atts */
    public static function render($atts): string
    {
        $atts = shortcode_atts(['id' => '', 'service' => 'youtube', 'thumbnail' => ''], $atts, self::TAG);

        $html = Embeds::placeholder(
            sanitize_key($atts['service']),
            \trim((string) $atts['id']),
            self::resolve_thumbnail(\trim((string) $atts['thumbnail']))
        );

        if ($html === '' && current_user_can('edit_posts')) {
            // Only editors see why nothing is rendered.
            return '<p><em>' . esc_html('Dock26 Cookies: unbekannter Dienst oder ungültige ID im Shortcode.') . '</em></p>';
        }

        return $html;
    }

    /**
     * The placeholder image is loaded before the visitor consents, so only
     * first-party images are accepted: an attachment ID, a site-relative
     * path, or a URL on one of this site's own hosts.
     */
    private static function resolve_thumbnail(string $thumbnail): string
    {
        if ($thumbnail === '') {
            return '';
        }

        if (\ctype_digit($thumbnail)) {
            $url = wp_get_attachment_image_url((int) $thumbnail, 'large');

            return \is_string($url) ? $url : '';
        }

        $url = esc_url_raw($thumbnail, ['http', 'https']);
        if ($url === '') {
            return '';
        }

        if (\str_starts_with($url, '/')) {
            // "//host/…" is protocol-relative, i.e. possibly another host.
            return \str_starts_with($url, '//') ? '' : $url;
        }

        $host = wp_parse_url($url, PHP_URL_HOST);

        return \is_string($host) && \in_array(\strtolower($host), self::first_party_hosts(), true) ? $url : '';
    }

    /** @return list<string> */
    private static function first_party_hosts(): array
    {
        $hosts = [
            wp_parse_url(home_url(), PHP_URL_HOST),
            // Covers media offloaded to a CDN the site owner configured.
            wp_parse_url(wp_get_upload_dir()['baseurl'], PHP_URL_HOST),
        ];

        /**
         * Hosts a shortcode thumbnail may be loaded from before consent.
         *
         * @param list<string> $hosts
         */
        $hosts = (array) apply_filters('dock26_cookies_thumbnail_hosts', \array_filter($hosts, 'is_string'));

        return \array_values(\array_map('strtolower', \array_filter($hosts, 'is_string')));
    }
}
