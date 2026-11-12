<?php

// Runs when the plugin is deleted from the Plugins screen. The plugin itself
// is not loaded here, so the option names are listed explicitly — keep them in
// sync with CookieConfig::OPTION_NAMES and Main::VERSION_OPTION.

if (!\defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$dock26_cookies_options = [
    'dock26_cookies_plugin_version',
    'dock26_cookies_settings',
    'dock26_cookies_consent_modal_config',
    'dock26_cookies_preference_modal_config',
    'dock26_cookies_gui_settings',
    'dock26_cookies_custom_styles',
];

$dock26_cookies_site_ids = is_multisite() ? get_sites(['fields' => 'ids', 'number' => 0]) : [get_current_blog_id()];

foreach ($dock26_cookies_site_ids as $dock26_cookies_site_id) {
    if (is_multisite()) {
        switch_to_blog((int) $dock26_cookies_site_id);
    }

    foreach ($dock26_cookies_options as $dock26_cookies_option) {
        delete_option($dock26_cookies_option);
    }

    if (is_multisite()) {
        restore_current_blog();
    }
}
