<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit;
}

use WP_REST_Controller;
use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;

class APIController extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = '/dock26-cookies/v1';
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, '/update-consent-modal-config', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_consent_modal_config'],
                'permission_callback' => [$this, 'permissions_check']
            ]
        ]);

        register_rest_route($this->namespace, '/update-preference-modal-config', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_preference_modal_config'],
                'permission_callback' => [$this, 'permissions_check']
            ]
        ]);

        register_rest_route($this->namespace, '/update-gui-config', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_gui_config'],
                'permission_callback' => [$this, 'permissions_check']
            ]
        ]);
    }

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

    public function update_consent_modal_config(WP_REST_Request $request): WP_REST_Response
    {
        return $this->handle_config_update($request, [CookieConfig::class, 'saveConsentModalConfig']);
    }

    public function update_preference_modal_config(WP_REST_Request $request): WP_REST_Response
    {
        return $this->handle_config_update($request, [CookieConfig::class, 'savePreferenceModalConfig']);
    }

    public function update_gui_config(WP_REST_Request $request): WP_REST_Response
    {
        return $this->handle_config_update($request, [CookieConfig::class, 'saveGUISettings']);
    }

    private function handle_config_update(WP_REST_Request $request, callable $save_fn): WP_REST_Response
    {
        $parameters = $request->get_json_params();

        if (!isset($parameters['config']) || !\is_array($parameters['config'])) {
            return new WP_REST_Response(null, 422);
        }

        try {
            $result = $save_fn($parameters['config']);
            return new WP_REST_Response($result, 200);
        } catch (\InvalidArgumentException $e) {
            return new WP_REST_Response($e->getMessage(), 422);
        } catch (\Exception $e) {
            return new WP_REST_Response($e->getMessage(), 500);
        }
    }
}
