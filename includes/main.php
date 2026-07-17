<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once 'cookie-config.php';

class Main
{
    private const VERSION_OPTION = 'dock26_cookies_plugin_version';

    public static function init()
    {
        self::init_admin();

        \Dock26Cookies\Shortcode::init();

        add_action('admin_init', [__CLASS__, 'maybe_upgrade']);

        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        add_action('rest_api_init', [__CLASS__, 'init_rest_api']);

        add_filter('render_block_core/embed', [__CLASS__, 'render_block_or_prompt_consent'], 10, 2);
    }

    public static function maybe_upgrade()
    {
        $current_version = get_option(self::VERSION_OPTION);
        if ($current_version !== DOCK26_COOKIES_PLUGIN_VERSION) {
            // Per-version upgrade routines go here.
            update_option(self::VERSION_OPTION, DOCK26_COOKIES_PLUGIN_VERSION);
        }
    }

    public static function enqueue_assets()
    {
        wp_enqueue_script(
            'dock26_cookieconsent_js',
            plugins_url('frontend/dist/assets/js/frontend.iife.js', DOCK26_COOKIES_PLUGIN_FILE),
            [],
            DOCK26_COOKIES_PLUGIN_VERSION,
            true
        );
        wp_enqueue_style(
            'dock26_cookieconsent_css',
            plugins_url('frontend/dist/assets/css/frontend.css', DOCK26_COOKIES_PLUGIN_FILE),
            [],
            DOCK26_COOKIES_PLUGIN_VERSION
        );

        $Cookies = new CookieConfig();

        wp_localize_script('dock26_cookieconsent_js', 'dock26Cookies', [
            'config' => $Cookies->getConfig(),
            'customStyles' => $Cookies->getCustomStyles(),
        ]);
    }

    public static function init_rest_api()
    {
        $api_controller = new APIController();
        $api_controller->register_routes();
    }

    public static function init_admin()
    {
        Admin::init();
    }

    public static function render_block_or_prompt_consent($block_content, $block)
    {
        $block_attrs = $block['attrs'] ?? [];

        if (($block_attrs['providerNameSlug'] ?? '') !== 'youtube' || empty($block_attrs['url'])) {
            return $block_content;
        }

        $video_id = self::extract_youtube_id($block_attrs['url']);
        if ($video_id === null) {
            return $block_content;
        }

        $class = $block_attrs['className'] ?? 'wp-block-embed-youtube';

        return '<div
            class="' . esc_attr($class) . '"
            data-service="youtube"
            data-id="' . esc_attr($video_id) . '"
            data-iframe-class="' . esc_attr($class) . '"
            data-autoscale data-iframe-loading="lazy" data-iframe-frameborder="0"></div>';
    }

    private static function extract_youtube_id(string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,20})~', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function activate()
    {
        update_option(self::VERSION_OPTION, DOCK26_COOKIES_PLUGIN_VERSION);
    }

    public static function uninstall()
    {
        delete_option(self::VERSION_OPTION);
        CookieConfig::deleteAllOptions();
    }
}
