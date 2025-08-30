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

                // Add Column For Custom Fields
        add_filter('manage_consent_category_posts_columns', function ($columns) {
            $columns['id'] = 'ID';
            $columns['consent_name'] = 'Name';
            $columns['consent_enabled'] = 'Enabled';
            $columns['consent_readonly'] = 'Read Only';
            return $columns;
        });

        add_action('manage_consent_category_posts_custom_column', function ($column, $post_id) {
            switch ($column) {
                case 'id':
                    echo esc_html($post_id);
                    break;
                case 'consent_name':
                    echo esc_html(get_post_meta($post_id, '_consent_name', true));
                    break;
                case 'consent_enabled':
                    echo esc_html(get_post_meta($post_id, '_consent_enabled', true));
                    break;
                case 'consent_readonly':
                    echo esc_html(get_post_meta($post_id, '_consent_readonly', true));
                    break;
            }
        }, 10, 2);
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
        $isReadOnly  = get_post_meta($post->ID, '_consent_readonly', true);
        $isEnabled   = get_post_meta($post->ID, '_consent_enabled', true);

        echo '<p>';
        echo '<label><strong>Name:</strong></label><br>';
        echo '<input type="text" name="consent_name" value="' . esc_attr($name) . '" style="width:100%;" />';
        echo '</p>';

        echo '<p>';
        echo '<label><strong>Description:</strong></label><br>';
        echo '<textarea name="consent_description" rows="4" style="width:100%;">' . esc_textarea($description) . '</textarea>';
        echo '</p>';

        echo '<p>';
        echo '<label><strong>Enabled:</strong></label><br>';
        echo '<input type="checkbox" name="consent_enabled" value="1" ' . checked($isEnabled, true, false) . ' />';
        echo '</p>';

        echo '<p>';
        echo '<label><strong>Read Only:</strong></label><br>';
        echo '<input type="checkbox" name="consent_readonly" value="1" ' . checked($isReadOnly, true, false) . ' />';
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
        if (array_key_exists('consent_enabled', $_POST)) {
            update_post_meta($post_id, '_consent_enabled', $_POST['consent_enabled'] === '1' ? '1' : '0');
        } else {
            update_post_meta($post_id, '_consent_enabled', '0');
        }
        if (array_key_exists('consent_readonly', $_POST)) {
            update_post_meta($post_id, '_consent_readonly', $_POST['consent_readonly'] === '1' ? '1' : '0');
        } else {
            update_post_meta($post_id, '_consent_readonly', '0');
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

        add_settings_section(
            'dock26_cookies_consent_modal_section',
            'Consent Modal Settings',
            null,
            'dock26_cookies_options_group'
        );

        add_settings_section(
            'dock26_cookies_preferences_modal_section',
            'Preferences Modal Settings',
            null,
            'dock26_cookies_options_group'
        );

        add_settings_field(
            'dock26_cookies_imprint_link',
            'Impressum Link',
            [__CLASS__, 'dock26_cookies_render_imprint_link_field'],
            'dock26_cookies_options_group',
            'dock26_cookies_general_section'
        );

        add_settings_field(
            'dock26_cookies_privacy_policy_link',
            'Datenschutzerklärung Link',
            [__CLASS__, 'dock26_cookies_render_privacy_policy_link_field'],
            'dock26_cookies_options_group',
            'dock26_cookies_general_section'
        );

        add_settings_field(
            'dock26_cookies_consent_modal_title',
            'Consent Modal Title',
            [__CLASS__, 'dock26_cookies_render_consent_modal_title_field'],
            'dock26_cookies_options_group',
            'dock26_cookies_consent_modal_section'
        );

        add_settings_field(
            'dock26_cookies_consent_modal_description',
            'Consent Modal Description',
            [__CLASS__, 'dock26_cookies_render_consent_modal_description_field'],
            'dock26_cookies_options_group',
            'dock26_cookies_consent_modal_section'
        );

        add_settings_field(
            'dock26_cookies_preferences_modal_title',
            'Preferences Modal Title',
            [__CLASS__, 'dock26_cookies_render_preferences_modal_title_field'],
            'dock26_cookies_options_group',
            'dock26_cookies_preferences_modal_section'
        );

        // save default options if not set
        if (!get_option('dock26_cookies_options')) {
            update_option('dock26_cookies_options', [
                'imprint_link' => '',
                'privacy_policy_link' => '',
                'consent_modal_title' => 'Cookie-Zustimmung',
                'consent_modal_description' => 'Wir verwenden Cookies, um Ihnen die bestmögliche Erfahrung auf unserer Website zu bieten.',
                'preferences_modal_title' => 'Cookies-Einstellungen',
            ]);
        }
    }

    public static function dock26_cookies_render_imprint_link_field()
    {
        $options = get_option('dock26_cookies_options');
    ?>
        <input type="text" name="dock26_cookies_options[imprint_link]" value="<?php echo esc_url($options['imprint_link'] ?? ''); ?>" style="width:100%;" />
    <?php
    }

    public static function dock26_cookies_render_privacy_policy_link_field()
    {
        $options = get_option('dock26_cookies_options');
    ?>
        <input type="text" name="dock26_cookies_options[privacy_policy_link]" value="<?php echo esc_url($options['privacy_policy_link'] ?? ''); ?>" style="width:100%;" />
    <?php
    }

    public static function dock26_cookies_render_consent_modal_title_field()
    {
        $options = get_option('dock26_cookies_options');
    ?>
        <input type="text" name="dock26_cookies_options[consent_modal_title]" value="<?php echo esc_attr($options['consent_modal_title'] ?? ''); ?>" style="width:100%;" />
    <?php
    }

    public static function dock26_cookies_render_consent_modal_description_field()
    {
        $options = get_option('dock26_cookies_options');
    ?>
        <textarea name="dock26_cookies_options[consent_modal_description]" rows="5" style="width:100%;"><?php echo esc_textarea($options['consent_modal_description'] ?? ''); ?></textarea>
<?php
    }

    public static function dock26_cookies_render_preferences_modal_title_field()
    {
        $options = get_option('dock26_cookies_options');
    ?>
        <input type="text" name="dock26_cookies_options[preferences_modal_title]" value="<?php echo esc_attr($options['preferences_modal_title'] ?? ''); ?>" style="width:100%;" />
    <?php
    }

    public static function dock26_cookies_sanitize_options($options)
    {
        $options['imprint_link'] = esc_url_raw($options['imprint_link'] ?? '');
        $options['privacy_policy_link'] = esc_url_raw($options['privacy_policy_link'] ?? '');
        $options['consent_modal_title'] = sanitize_text_field($options['consent_modal_title'] ?? '');
        $options['consent_modal_description'] = sanitize_textarea_field($options['consent_modal_description'] ?? '');
        $options['preferences_modal_title'] = sanitize_text_field($options['preferences_modal_title'] ?? '');
        return $options;
    }
}
