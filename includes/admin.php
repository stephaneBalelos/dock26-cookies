<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dock26_Cookies_Admin
{

    public function __construct()
    {
        self::dock26_cookies_register_consent_category_cpt();
        add_action('add_meta_boxes', ['Dock26_Cookies_Admin', 'dock26_cookies_add_consent_category_meta']);
        add_action('save_post', ['Dock26_Cookies_Admin', 'dock26_cookies_save_consent_category_meta']);

        add_action('admin_menu', ['Dock26_Cookies_Admin', 'dock26_cookies_register_admin_menu']);
    }

    // Register Admin Menu
    public static function dock26_cookies_register_consent_category_cpt()
    {
        $labels = array(
            'name'               => 'Consent Categories',
            'singular_name'      => 'Consent Category',
            'menu_name'          => 'Consent Categories',
            'add_new_item'       => 'Add New Consent Category',
            'edit_item'          => 'Edit Consent Category',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-shield',
            'supports'           => array('title'),
        );
        register_post_type('consent_category', $args);
    }

    public static function dock26_cookies_add_consent_category_meta()
    {
        add_meta_box(
            'dock26_cookies_consent_meta',
            'Category Details',
            ['Dock26_Cookies_Admin', 'dock26_cookies_render_consent_meta'],
            'consent_category',
            'normal',
            'default'
        );
    }

    public static function dock26_cookies_render_consent_meta($post)
    {
        $name        = get_post_meta($post->ID, '_consent_name', true);
        $description = get_post_meta($post->ID, '_consent_description', true);
        $key         = get_post_meta($post->ID, '_consent_key', true);

        echo '<p>';
        echo '<label><strong>Name:</strong></label><br>';
        echo '<input type="text" name="consent_name" value="' . esc_attr($name) . '" style="width:100%;" />';
        echo '</p>';

        echo '<p>';
        echo '<label><strong>Description:</strong></label><br>';
        echo '<textarea name="consent_description" rows="4" style="width:100%;">' . esc_textarea($description) . '</textarea>';
        echo '</p>';

        echo '<p>';
        echo '<label><strong>Unique Key:</strong></label><br>';
        echo '<input type="text" name="consent_key" value="' . esc_attr($key) . '" style="width:100%;" />';
        echo '</p>';
    }

    public static function dock26_cookies_save_consent_category_meta($post_id)
    {
        if (array_key_exists('consent_name', $_POST)) {
            update_post_meta($post_id, '_consent_name', sanitize_text_field($_POST['consent_name']));
        }
        if (array_key_exists('consent_description', $_POST)) {
            update_post_meta($post_id, '_consent_description', sanitize_textarea_field($_POST['consent_description']));
        }
        if (array_key_exists('consent_key', $_POST)) {
            update_post_meta($post_id, '_consent_key', sanitize_key($_POST['consent_key']));
        }
    }

    public static function dock26_cookies_register_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=consent_category',
            'General Settings',
            'General Settings',
            'manage_options',
            'consent_general_settings',
            [__CLASS__, 'dock26_cookies_render_general_settings']
        );
    }

    public static function dock26_cookies_render_general_settings()
    {
        echo '<div class="wrap">';
        echo '<h1>Dock26 Cookies Settings</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('dock26_cookies_options_group');
        do_settings_sections('dock26_cookies_options_group');
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public static function dock26_cookies_register_settings()
    {
        register_setting('dock26_cookies_options_group', 'dock26_cookies_options', [__CLASS__, 'dock26_cookies_sanitize_options']);

        add_settings_section(
            'dock26_cookies_general_section',
            'General Settings',
            null,
            'dock26_cookies_options_group'
        );

        add_settings_field(
            'dock26_cookies_enable_script_blocking',
            'Enable Script Blocking',
            [__CLASS__, 'dock26_cookies_render_enable_script_blocking_field'],
            'dock26_cookies_options_group',
            'dock26_cookies_general_section'
        );

        add_settings_field(
            'dock26_cookies_custom_message',
            'Custom Consent Message',
            [__CLASS__, 'dock26_cookies_render_custom_message_field'],
            'dock26_cookies_options_group',
            'dock26_cookies_general_section'
        );

        // save default options if not set
        if (!get_option('dock26_cookies_options')) {
            update_option('dock26_cookies_options', [
                'enable_script_blocking' => true,
                'custom_message' => 'My Message',
            ]);
        }
    }

    public static function dock26_cookies_render_enable_script_blocking_field()
    {
        $options = get_option('dock26_cookies_options');
        ?>
        <input type="checkbox" name="dock26_cookies_options[enable_script_blocking]" value="1" <?php checked($options['enable_script_blocking'] ?? true); ?> />
        <?php
    }

    public static function dock26_cookies_render_custom_message_field()
    {
        $options = get_option('dock26_cookies_options');
        ?>
        <textarea name="dock26_cookies_options[custom_message]" rows="5" style="width:100%;"><?php echo esc_textarea($options['custom_message'] ?? ''); ?></textarea>
        <?php
    }

    public static function dock26_cookies_sanitize_options($options)
    {
        $options['enable_script_blocking'] = isset($options['enable_script_blocking']) ? true : false;
        $options['custom_message'] = sanitize_textarea_field($options['custom_message']);
        return $options;
    }
}
