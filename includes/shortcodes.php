<?php
namespace Dock26Cookies;
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Shortcode
{

    public static function init() {
        self::register_shortcode();
    }

    public static function register_shortcode()
    {
        add_shortcode('dock26_iframe_content', [\Dock26Cookies\Shortcode::class, 'render']);
    }

    public static function render($atts)
    {
        $atts = shortcode_atts(['id' => 0, 'service' => 'youtube', 'thumbnail' => ''], $atts, 'dock26_iframe_content');


        return '<div
            data-service="' . esc_attr($atts['service']) . '"
            data-id="' . esc_attr($atts['id']) . '"
            data-thumbnail="' . esc_url($atts['thumbnail']) . '"
            data-autoscale
            data-iframe-loading="lazy"
            data-iframe-frameborder="0">
        </div>';

    }


}
