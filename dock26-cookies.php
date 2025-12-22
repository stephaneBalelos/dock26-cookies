<?php
/*
Plugin Name: Dock26 Cookies
Description: Dock26 Cookies Plugin
Version: 0.3.1
Author: Dock26
*/

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DOCK26_COOKIES_PLUGIN_VERSION', '0.3.1');

// include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
// Load main plugin class
require_once plugin_dir_path(__FILE__) . 'includes/main.php';

// Initialize the plugin
class Dock26_Cookies
{
    public static function init()
    {
        Dock26_Cookies_Main::install();
    }
}
// Initialize the plugin
Dock26_Cookies::init();
// Register activation, deactivation, and uninstall hooks
register_activation_hook(__FILE__, ['Dock26_Cookies_Main', 'activate']);
register_deactivation_hook(__FILE__, ['Dock26_Cookies_Main', 'deactivate']);
register_uninstall_hook(__FILE__, ['Dock26_Cookies_Main', 'uninstall']);