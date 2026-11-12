<?php

namespace Dock26Cookies;

if (!\defined('ABSPATH')) {
    exit;
}

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * REST API of the admin app.
 *
 *   POST /dock26-cookies/v1/settings/<section>  save one settings section
 *
 * A successful save answers with CookieConfig::toAdminData(), so the app
 * refreshes its state without a second request. The initial state is printed
 * into the admin page (Admin::enqueue_admin_assets()).
 */
final class APIController
{
    public const REST_NAMESPACE = 'dock26-cookies/v1';

    /** Settings section (URL segment) => method of CookieConfig that saves it. */
    private const SECTIONS = [
        'global' => 'saveGlobalSettings',
        'consent-modal' => 'saveConsentModalConfig',
        'preferences-modal' => 'savePreferenceModalConfig',
        'gui' => 'saveGUISettings',
        'custom-styles' => 'saveCustomStyles',
    ];

    public static function register_routes(): void
    {
        // Only the known sections are routable; anything else is a 404.
        $sections = \implode('|', \array_keys(self::SECTIONS));

        register_rest_route(self::REST_NAMESPACE, '/settings/(?P<section>' . $sections . ')', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [self::class, 'update_section'],
            'permission_callback' => [self::class, 'permissions_check'],
            'args' => [
                'config' => [
                    'description' => 'Die Werte des Einstellungsbereichs.',
                    'type' => 'object',
                    'required' => true,
                ],
            ],
        ]);
    }

    public static function permissions_check(): bool|WP_Error
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            'Sie haben keine Berechtigung, die Cookie-Einstellungen zu verwalten.',
            ['status' => rest_authorization_required_code()]
        );
    }

    /** @param WP_REST_Request<array<string, mixed>> $request */
    public static function update_section(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $save = [CookieConfig::class, self::SECTIONS[$request['section']]];

        try {
            $save((array) $request['config']);
        } catch (\InvalidArgumentException $e) {
            return new WP_Error('dock26_cookies_invalid_settings', $e->getMessage(), ['status' => 422]);
        }

        return new WP_REST_Response((new CookieConfig())->toAdminData());
    }
}
