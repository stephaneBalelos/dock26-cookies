<?php

namespace Dock26Cookies\Providers;

use Illuminate\Support\ServiceProvider;
use Dock26Cookies\Main;

class PluginServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Install the plugin
        Main::install();
        // Initialize the plugin
        Main::init();
        // Register activation, deactivation, and uninstall hooks
        register_activation_hook(__FILE__, [\Dock26Cookies\Main::class, 'activate']);
        register_deactivation_hook(__FILE__, [\Dock26Cookies\Main::class, 'deactivate']);
        register_uninstall_hook(__FILE__, [\Dock26Cookies\Main::class, 'uninstall']);
    }
}
