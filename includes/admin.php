<?php

namespace Dock26Cookies;

if (!\defined('ABSPATH')) {
    exit;
}

final class Admin
{
    private const PAGE_SLUG = 'dock26-cookies';

    private const SCRIPT_HANDLE = 'dock26-cookies-admin-js';

    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets']);
    }

    public static function add_admin_menu(): void
    {
        add_menu_page(
            'Dock26 Cookies',
            'Dock26 Cookies',
            'manage_options',
            self::PAGE_SLUG,
            [self::class, 'render_page'],
            'dashicons-shield',
            25
        );
    }

    public static function render_page(): void
    {
        // Mount point of the Vue app (admin/src/main.ts).
        echo '<div id="dock26-cookies-admin-app" class="isolate"></div>';
    }

    public static function enqueue_admin_assets(string $hook): void
    {
        if ($hook !== 'toplevel_page_' . self::PAGE_SLUG) {
            return;
        }

        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            plugins_url('admin/dist/assets/js/admin.iife.js', DOCK26_COOKIES_PLUGIN_FILE),
            [],
            DOCK26_COOKIES_PLUGIN_VERSION,
            true
        );
        wp_enqueue_style(
            'dock26-cookies-admin-css',
            plugins_url('admin/dist/assets/css/admin.css', DOCK26_COOKIES_PLUGIN_FILE),
            [],
            DOCK26_COOKIES_PLUGIN_VERSION
        );

        $data = [
            'apiUrl' => esc_url_raw(rest_url(APIController::REST_NAMESPACE)),
            'nonce' => wp_create_nonce('wp_rest'),
        ] + (new CookieConfig())->toAdminData();

        wp_add_inline_script(self::SCRIPT_HANDLE, Main::js_global('dock26CookiesAdmin', $data), 'before');
    }
}
