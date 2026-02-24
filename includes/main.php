<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dock26_Cookies_Main
{

    public static function install()
    {

        add_action('init', ['Dock26_Cookies_Main', 'init']);
        add_action('wp_enqueue_scripts', ['Dock26_Cookies_Main', 'enqueue_assets'], 8);
        add_action('admin_init', ['Dock26_Cookies_Admin', 'dock26_cookies_register_settings']);

        add_filter( 'render_block_core/embed', ['Dock26_Cookies_Main', 'render_block_or_prompt_consent'], 10, 2 );


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

    public static function enqueue_assets()
    {
        //Enqueue Styles
        wp_enqueue_style('dock26_cookieconsent', plugins_url('../orestbida-cc/3.1.0/cookieconsent.css', __FILE__), [], DOCK26_COOKIES_PLUGIN_VERSION);
        // wp_enqueue_style('dock26_cookies_main', plugins_url('../dist/assets/css/main.css', __FILE__), ['dock26_cookieconsent'], DOCK26_COOKIES_PLUGIN_VERSION);
        // Enqueue Scripts
        wp_enqueue_script('dock26_cookieconsent_js', plugins_url('../orestbida-cc/3.1.0/cookieconsent.umd.js', __FILE__), [], DOCK26_COOKIES_PLUGIN_VERSION);
        wp_enqueue_script('dock26_cookies_main', plugins_url('../dist/assets/js/main.iife.js', __FILE__), ['dock26_cookieconsent_js'], DOCK26_COOKIES_PLUGIN_VERSION);

        wp_localize_script('dock26_cookies_main', 'dock26Cookies', [
            'settings' => get_option('dock26_cookies_options'),
            'categories' => self::get_categories(),
        ]);
    }

    public static function get_categories() {
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

    public static function init() {
        new Dock26_Cookies_Admin();

        self::register_shortcode();
    }

    public static function render_block_or_prompt_consent($block_content, $block) {
        $cookie = json_decode(stripslashes($_COOKIE['cc_cookie']));
        
        $consent_categories = self::get_categories();

        $externalConsent = [];

        for ($i=0; $i < count($consent_categories); $i++) { 
            $category = $consent_categories[$i];
            if($category['blockExternal']) {
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

    public static function register_shortcode()
    {
        add_shortcode('dock26_cookies', ['Dock26_Cookies_Shortcode', 'render']);
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
