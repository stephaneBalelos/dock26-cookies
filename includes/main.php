<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Main
{
    public static function install()
    {
        // Update the plugin version by changing the version number
        $new_version = DOCK26_COOKIES_PLUGIN_VERSION;

        // Check Version in the database
        $current_version = get_option('dock26_cookies_plugin_version');
        if (!$current_version) {
            // If no version is set, set it to the new version
            update_option('dock26_cookies_plugin_version', $new_version);
            $current_version = $new_version;
        }

        if (version_compare($current_version, $new_version, '<')) {
            // Update the plugin version in the database
            update_option('dock26_cookies_plugin_version', $new_version);
        }
    }

    public static function init()
    {
        // Register Custom Post Type for Consent Services
        self::register_consent_service_category_taxonomy();
        self::register_consent_service_cpt();


        self::init_admin();

        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        add_action('rest_api_init', [\Dock26Cookies\Main::class, 'init_rest_api']);
    }

    public static function register_consent_service_cpt()
    {
        // Register Consent Service Custom Post Type

        $labels = array(
            'name'               => 'Consent Services',
            'singular_name'      => 'Consent Service'
        );

        $args = array(
            'labels'            => $labels,
            'public'            => true,
            'show_ui'           => true,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-shield',
        );
        register_post_type('d26cookies_consent_service', $args);
    }

    public static function register_consent_service_category_taxonomy()
    {
        // Register Consent Service Category Taxonomy
        $labels = array(
            'name'              => 'Consent Service Categories',
            'singular_name'     => 'Consent Service Category',
        );
        $args = array(
            'labels'            => $labels,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'hierarchical'      => false,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'default_term' => array(
                'slug' => 'd26cookies_notwendige',
                'name' => 'Notwendige',
            )
        );
        register_taxonomy('d26cookies_consent_service_cat', ['d26cookies_consent_service'], $args);
    }

    public static function enqueue_assets()
    {
        // Only enqueue on frontend
        if (is_admin()) {
            return;
        }

        // Enqueue Scripts
        wp_enqueue_script('dock26_cookieconsent_js', plugins_url('../frontend/dist/assets/js/frontend.iife.js', __FILE__), []);
        // Enqueue Styles
        wp_enqueue_style('dock26_cookieconsent_css', plugins_url('../frontend/dist/assets/css/frontend.css', __FILE__), []);

        wp_localize_script('dock26_cookieconsent_js', 'dock26Cookies', [
            'settings' => array(),
            'categories' => self::get_categories(),
        ]);
    }

    public static function init_rest_api()
    {
        $api_controller = new \Dock26Cookies\APIController();
        $api_controller->register_routes();
    }

    public static function init_admin()
    {
        \Dock26Cookies\Admin::init();
    }

    public static function get_categories()
    {
        $categories = get_terms([
            'taxonomy' => 'd26cookies_consent_service_category',
            'number' => 99,
            'hide_empty' => false
        ]);

        return $categories;
    }

    public static function activate() {}

    public static function deactivate()
    {
        global $wpdb;
    }
    public static function uninstall()
    {
        global $wpdb;
    }
}
