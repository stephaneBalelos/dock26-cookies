<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Main
{

    public static function install()
    {

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

    public static function init()
    {
        new \Dock26Cookies\Admin();

        \Dock26Cookies\Shortcode::init();

        add_action('init', [\Dock26Cookies\Main::class, 'init']);

        self::enqueue_assets();

        add_filter('render_block_core/embed', [\Dock26Cookies\Main::class, 'render_block_or_prompt_consent'], 10, 2);
    }

    public static function enqueue_assets()
    {
        // Only enqueue on frontend
        if (is_admin()) {
            return;
        }

        // Enqueue Scripts
        wp_enqueue_script('dock26_cookieconsent_js', plugins_url('../frontend/dist/assets/js/frontend.iife.js', __FILE__), []);
        // Enqueue Styles
        wp_enqueue_style('dock26_cookieconsent_css', plugins_url('../frontend/dist/assets/css/frontend.css', __FILE__), []);

        wp_localize_script('dock26_cookieconsent_js', 'dock26Cookies', [
            'settings' => get_option('dock26_cookies_options'),
            'categories' => self::get_categories(),
        ]);
    }

    public static function get_categories()
    {
        $categories = array();
        $cats = get_posts([
            'post_type' => 'consent_category',
            'numberposts' => 99,
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        foreach ($cats as $key => $cat) {
            $cats[$key]->meta = get_post_meta($cat->ID);

            $categories[] = array(
                'name' => isset($cats[$key]->meta['_consent_name']) ? $cats[$key]->meta['_consent_name'][0] : 'category_' . $cat->ID,
                'description' => isset($cats[$key]->meta['_consent_description']) ? $cats[$key]->meta['_consent_description'][0] : '',
                'enabled' => isset($cats[$key]->meta['_consent_enabled']) ? $cats[$key]->meta['_consent_enabled'][0] === '1' : false,
                'readOnly' => isset($cats[$key]->meta['_consent_readonly']) ? $cats[$key]->meta['_consent_readonly'][0] === '1' : false,
                'blockExternal' => isset($cats[$key]->meta['_consent_block_external']) ? $cats[$key]->meta['_consent_block_external'][0] === '1' : false,
                'id' => $cat->ID
            );
        }
        return $categories;
    }
    public static function render_block_or_prompt_consent($block_content, $block)
    {

        if (!isset($_COOKIE['cc_cookie'])) {
            return '<button id="dock-26-cookies-trigger-cc" class="map-accept-cookies-button wp-block-button__link wp-element-button">Cookies-Einstellungen</button>';
        }

        $cookie = json_decode(stripslashes($_COOKIE['cc_cookie']));

        $consent_categories = self::get_categories();

        $externalConsent = [];

        for ($i = 0; $i < count($consent_categories); $i++) {
            $category = $consent_categories[$i];
            if ($category['blockExternal']) {
                if (in_array($category['id'], $cookie->categories)) {
                    $externalConsent[] = [$category['id'] => true];
                }
            }
        }

        if (count($externalConsent) > 0) {
            return $block_content;
        } else {
            return '<button id="dock-26-cookies-trigger-cc" class="map-accept-cookies-button wp-block-button__link wp-element-button">Cookies-Einstellungen</button>';
        }
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
