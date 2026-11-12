<?php

namespace Dock26Cookies;

if (!\defined('ABSPATH')) {
    exit;
}

final class Main
{
    private const VERSION_OPTION = 'dock26_cookies_plugin_version';

    private const SCRIPT_HANDLE = 'dock26_cookieconsent_js';

    private const STYLE_HANDLE = 'dock26_cookieconsent_css';

    public static function init(): void
    {
        Admin::init();
        Embeds::init();
        Shortcode::register();

        add_action('admin_init', [self::class, 'maybe_upgrade']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_assets']);
        add_action('rest_api_init', [APIController::class, 'register_routes']);
    }

    public static function activate(): void
    {
        self::maybe_upgrade();
    }

    public static function maybe_upgrade(): void
    {
        $installed_version = get_option(self::VERSION_OPTION);
        if ($installed_version === DOCK26_COOKIES_PLUGIN_VERSION) {
            return;
        }

        // Options were stored as JSON strings before 0.4.0. A fresh install
        // has no version yet and nothing to convert.
        if (\is_string($installed_version) && \version_compare($installed_version, '0.4.0', '<')) {
            CookieConfig::migrateLegacyOptions();
        }

        update_option(self::VERSION_OPTION, DOCK26_COOKIES_PLUGIN_VERSION);
    }

    public static function enqueue_assets(): void
    {
        $config = new CookieConfig();

        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            plugins_url('frontend/dist/assets/js/frontend.iife.js', DOCK26_COOKIES_PLUGIN_FILE),
            [],
            DOCK26_COOKIES_PLUGIN_VERSION,
            true
        );
        wp_add_inline_script(
            self::SCRIPT_HANDLE,
            self::js_global('dock26Cookies', ['config' => $config->getConfig()]),
            'before'
        );

        wp_enqueue_style(
            self::STYLE_HANDLE,
            plugins_url('frontend/dist/assets/css/frontend.css', DOCK26_COOKIES_PLUGIN_FILE),
            [],
            DOCK26_COOKIES_PLUGIN_VERSION
        );
        // Printed right after the stylesheet, so it wins over the vendor
        // defaults on document order.
        wp_add_inline_style(self::STYLE_HANDLE, $config->getCustomStylesCss());
    }

    /**
     * A `window.<name> = <json>;` statement for wp_add_inline_script().
     *
     * @param array<string, mixed> $data
     */
    public static function js_global(string $name, array $data): string
    {
        return \sprintf(
            'window.%s = %s;',
            $name,
            wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)
        );
    }
}
