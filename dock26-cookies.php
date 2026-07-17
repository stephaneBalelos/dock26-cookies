<?php
/*
Plugin Name: Dock26 Cookies
Description: Dock26 Cookies Plugin
Version: 0.3.4
Author: Dock26
Text Domain: dock26-cookies
License: GPL-2.0-or-later
Requires at least: 6.0
Requires PHP: 7.4
*/

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit;
}

// Keep in sync with the Version plugin header above.
define('DOCK26_COOKIES_PLUGIN_VERSION', '0.3.4');
define('DOCK26_COOKIES_PLUGIN_FILE', __FILE__);

require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/api.php';
require_once plugin_dir_path(__FILE__) . 'includes/main.php';

// Initialize the plugin
add_action('init', [Main::class, 'init']);
// Register activation and uninstall hooks
register_activation_hook(__FILE__, [Main::class, 'activate']);
register_uninstall_hook(__FILE__, [Main::class, 'uninstall']);
