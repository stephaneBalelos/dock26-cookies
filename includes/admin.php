<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Admin
{

    public static function init()
    {

        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    public static function add_admin_menu()
    {
        add_menu_page(
            'Dock26 Cookies',
            'Dock26 Cookies',
            'manage_options',
            'dock26-cookies',
            function () {
                echo '<div id="dock26-cookies-admin-app" class="isolate"></div>';
            },
            'dashicons-shield',
            25
        );
    }

    public static function enqueue_admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_dock26-cookies') {
            return;
        }

        // Add vue from CDN
        wp_enqueue_script('dock26-cookies-admin-js', plugins_url('../admin/dist/assets/js/admin.iife.js', __FILE__), array(), DOCK26_COOKIES_PLUGIN_VERSION, true);
        wp_enqueue_style('dock26-cookies-admin-css', plugins_url('../admin/dist/assets/css/admin.css', __FILE__), array(), DOCK26_COOKIES_PLUGIN_VERSION);

        $config = new CookieConfig([], []);

        // Localize script with data
        wp_localize_script('dock26-cookies-admin-js', 'dock26CookiesAdmin', [
            'apiUrl' => esc_url_raw(rest_url('dock26-cookies/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
            'config' => $config->getConfig()
        ]);
    }
}
