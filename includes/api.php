<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use WP_REST_Controller;
use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Tax_Query;

class APIController extends WP_REST_Controller
{
    // Here initialize our namespace and resource name.
    public function __construct()
    {
        $this->namespace = '/dock26-cookies/v1';
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, '/categories', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_consent_service_categories'],
                'permission_callback' => [$this, 'permissions_check']
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_consent_service_categories'],
                'permission_callback' => [$this, 'permissions_check']
            ]
        ]);
        register_rest_route($this->namespace, '/categories/(?P<id>[\d]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_consent_service_category'],
                'permission_callback' => [$this, 'permissions_check'],
                'args' => [
                    'id' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ]
                ]
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_consent_service_category'],
                'permission_callback' => [$this, 'permissions_check'],
                'args' => [
                    'id' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ]
                ]
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_consent_service_category'],
                'permission_callback' => [$this, 'permissions_check'],
                'args' => [
                    'id' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        }
                    ]
                ]
            ],

        ]);
    }

    // Check permissions
    public function permissions_check()
    {
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return true;
        }

        if (is_user_logged_in()) {
            return new WP_Error('rest_forbidden', 'You do not have permissions to access this resource.', ['status' => 403]);
        }

        return new WP_Error('rest_forbidden', 'You cannot access this resource.', ['status' => 401]);
    }



    public function get_consent_services()
    {
        $services = get_posts([
            'post_type' => 'd26cookies_consent_service',
            'numberposts' => -1
        ]);
        return rest_ensure_response($services);
    }

    public static function get_consent_service_categories()
    {
        $categories = get_terms([
            'taxonomy' => 'd26cookies_consent_service_cat',
            'hide_empty' => false,
        ]);

        return $categories;
    }
    public static function create_consent_service_categories(WP_REST_Request $request)
    {
          $parameters = $request->get_json_params();

          if(isset($parameters['category_name'])) {
            $result = wp_insert_term($parameters['category_name'], 'd26cookies_consent_service_cat', []);
            return new WP_REST_Response($result, 200);
          }

        return new WP_REST_Response(null, 422);
    }

    public static function get_consent_service_category(WP_REST_Request $request)
    {
        $parameters = $request->get_url_params();

        $result = get_term($parameters['id'], 'd26cookies_consent_service_cat', ARRAY_A);

        if($result) {
            return new WP_REST_Response($result, 200);
        }
        return new WP_REST_Response(null, 422);
    }

    public static function update_consent_service_category(WP_REST_Request $request)
    {
        $parameters = $request->get_url_params();
        $data = $request->get_json_params();

        $result = wp_update_term($parameters['id'], 'd26cookies_consent_service_cat', [
            'name' => $data['category_name']
        ]);

        if($result) {
            return new WP_REST_Response($result, 200);
        }
        return new WP_REST_Response(null, 422);
    }

    
    public static function delete_consent_service_category(WP_REST_Request $request)
    {
        $parameters = $request->get_url_params();

        $result = wp_delete_term($parameters['id'], 'd26cookies_consent_service_cat');

        if ($result == true) {
            return new WP_REST_Response($result, 200);
        }

        return new WP_REST_Response($result, 422);
    }

    
}
