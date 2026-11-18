<?php
/*
Plugin Name: Dock26 Cookies
Description: Dock26 Cookies Plugin
Version: 0.5.2
Author: Dock26
Text Domain: dock26-cookies
License: GPL-2.0-or-later
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.1
*/

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit;
}

// Keep in sync with the Version plugin header above.
define('DOCK26_COOKIES_PLUGIN_VERSION', '0.5.2');
define('DOCK26_COOKIES_PLUGIN_FILE', __FILE__);

require_once __DIR__ . '/includes/cookie-config.php';
require_once __DIR__ . '/includes/embeds.php';
require_once __DIR__ . '/includes/shortcodes.php';
require_once __DIR__ . '/includes/admin.php';
require_once __DIR__ . '/includes/api.php';
require_once __DIR__ . '/includes/main.php';

add_action('init', [Main::class, 'init']);
register_activation_hook(__FILE__, [Main::class, 'activate']);
// Uninstall cleanup lives in uninstall.php.
