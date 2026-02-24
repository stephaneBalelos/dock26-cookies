<?php

namespace Dock26Cookies\Providers;

use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        add_action('admin_menu', function () {
            add_menu_page(
                'Acorn Plugin',
                'Acorn Plugin',
                'manage_options',
                'dock26-cookies',
                function () {
                    echo view('admin');
                },
                'dashicons-admin-generic',
                20
            );
        });
    }
}