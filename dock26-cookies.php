<?php
/*
Plugin Name: Dock26 Cookies
Description: Dock26 Cookies Plugin
Version: 0.3.3
Author: Dock26
*/

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit;
}


// Define plugin constants
define('DOCK26_COOKIES_PLUGIN_VERSION', '0.3.4');

require_once __DIR__ . '/vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/main.php';

// Install the plugin
Main::install();
// Initialize the plugin
Main::init();
// Register activation, deactivation, and uninstall hooks
register_activation_hook(__FILE__, [\Dock26Cookies\Main::class, 'activate']);
register_deactivation_hook(__FILE__, [\Dock26Cookies\Main::class, 'deactivate']);
register_uninstall_hook(__FILE__, [\Dock26Cookies\Main::class, 'uninstall']);
