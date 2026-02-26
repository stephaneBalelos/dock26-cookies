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

use Illuminate\Support\Facades\Log;
use Roots\Acorn\Application;
use Roots\Acorn\Configuration\Exceptions;
use Roots\Acorn\Configuration\Middleware;

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
        ->withMiddleware(function (Middleware $middleware) {
            $middleware->wordpress([
                Illuminate\Cookie\Middleware\EncryptCookies::class,
                Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                Illuminate\Session\Middleware\StartSession::class,
                Illuminate\View\Middleware\ShareErrorsFromSession::class,
                Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
                Illuminate\Routing\Middleware\SubstituteBindings::class,
            ]);
        })
        // ->withExceptions(function (Exceptions $exceptions) {
        //     // Configure exception handling
        //     $exceptions->reportable(function (\Throwable $e) {
        //         Log::error($e->getMessage());
        //     });
        // })
        ->withRouting(
            api: base_path('routes/api.php'),
        )
        ->boot();
}, 0);
