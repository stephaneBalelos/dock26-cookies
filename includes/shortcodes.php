<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Dock26_Cookies_Shortcode
{

    public static function render($atts)
    {
        // Enqueue necessary scripts and styles
        $atts = shortcode_atts(['id' => 0], $atts);

        return '<div id="dock26-cookies-container" data-id="' . esc_attr($atts['id']) . '"></div>';

    }
}
