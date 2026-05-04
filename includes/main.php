<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once 'cookie-config.php';

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
        // Register Custom Post Type for Consent Services
        self::register_consent_service_category_taxonomy();
        self::register_consent_service_cpt();


        self::init_admin();

        \Dock26Cookies\Shortcode::init();

        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        add_action('rest_api_init', [\Dock26Cookies\Main::class, 'init_rest_api']);

        add_filter('render_block_core/embed', [__CLASS__, 'render_block_or_prompt_consent'], 10, 2);

    }

    public static function register_consent_service_cpt()
    {
        // Register Consent Service Custom Post Type

        $labels = array(
            'name'               => 'Consent Services',
            'singular_name'      => 'Consent Service'
        );

        $args = array(
            'labels'            => $labels,
            'public'            => true,
            'show_ui'           => true,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-shield',
        );
        register_post_type('d26cookies_consent_service', $args);
    }

    public static function register_consent_service_category_taxonomy()
    {
        // Register Consent Service Category Taxonomy
        $labels = array(
            'name'              => 'Consent Service Categories',
            'singular_name'     => 'Consent Service Category',
        );
        $args = array(
            'labels'            => $labels,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'hierarchical'      => false,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'default_term' => array(
                'slug' => 'd26cookies_notwendige',
                'name' => 'Notwendige',
            )
        );
        register_taxonomy('d26cookies_consent_service_cat', ['d26cookies_consent_service'], $args);
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

        $Cookies = new CookieConfig();

        wp_localize_script('dock26_cookieconsent_js', 'dock26Cookies', [
            'config' => $Cookies->getConfig(),
        ]);
    }

    public static function init_rest_api()
    {
        $api_controller = new \Dock26Cookies\APIController();
        $api_controller->register_routes();
    }

    public static function init_admin()
    {
        \Dock26Cookies\Admin::init();
    }

    public static function render_block_or_prompt_consent($block_content, $block)
    {

        $block_attrs = $block['attrs'];
        return esc_html(json_encode($block_content));

        // Handle Youtube
        if ($block_attrs['providerNameSlug'] == 'youtube') {

            $query = parse_url($block_attrs['url'], PHP_URL_QUERY);
            $q = explode("=", $query);

            // return json_encode($q);

            return '<div 
            class="' . $block_attrs['className'] . '"
            data-service="' . $block_attrs['providerNameSlug'] . '"
            data-id="' . $q[1] . '"
            data-iframe-class="' . $block_attrs['className'] . '"
            data-autoscale data-iframe-id="iframeId" data-iframe-loading="lazy" data-iframe-frameborder="0"></div>';
        }

        return $block_content;
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
