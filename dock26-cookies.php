<?php
/*
Plugin Name: Dock26 Cookies
Description: Dock26 Cookies Plugin
Version: 0.3.3
Author: Dock26
*/

if (!defined('ABSPATH')) {
    exit;
}

use Roots\Acorn\Application;



// Define plugin constants
define('DOCK26_COOKIES_PLUGIN_VERSION', '0.3.4');

require_once __DIR__ . '/vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/main.php';

add_action('after_setup_theme', function () {
    Application::configure()
        ->withProviders([
            \Dock26Cookies\Providers\PluginServiceProvider::class,
        ])
        ->boot();
}, 0);