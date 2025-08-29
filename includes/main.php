<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dock26_Cookies_Main
{

    public static function install()
    {

        add_action('init', ['Dock26_Cookies_Main', 'register_shortcode']);
        add_action('wp_enqueue_scripts', ['Dock26_Cookies_Main', 'enqueue_scripts']);

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

    public static function enqueue_scripts()
    {
        // Enqueue Scripts
        wp_enqueue_script('dock26_cookies_main', plugins_url('../dist/assets/js/main.iife.js', __FILE__), [], DOCK26_COOKIES_PLUGIN_VERSION, true);
    }


    public static function register_shortcode()
    {
        add_shortcode('dock26_cookies', ['Dock26_Cookies_Shortcode', 'render']);
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
