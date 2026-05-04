<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit;
}

class CookieConfig
{
    private const OPTION_PREFIX = 'dock26_cookies_';

    private $settings = [
        'cookie_name' => 'd26Cookies',
        'default_language' => 'de',
        'privacy_btn' => [
            'label' => 'Datenschutzerklärung',
            'url' => '#'
        ],
        'imprint_btn' => [
            'label' => 'Impressum',
            'url' => '#'
        ]
    ];

    private $guiOptions = [
        'consentModal' => [
            'layout' => 'box',
            'position' => 'middle center'
        ],
        'preferencesModal' => [
            'layout' => 'bar',
            'position' => 'left'
        ]
    ];

    private $consentModalConfig = [
        'title' => 'Wir Benutzen Cookies',
        'description' => "Auf dieser Website nutzen wir Cookies und vergleichbare Funktionen zur Verarbeitung von Endgeräteinformationen und personenbezogenen Daten (wie z.B. IP-Adressen oder Browserinformationen). Die Verarbeitung dient der Einbindung von Inhalten, externen Diensten und Elementen Dritter, der statistischen Analyse/Messung, personalisierten Werbung sowie der Einbindung sozialer Medien.",
        'acceptAllBtn' => "Alles akzeptieren",
        'acceptNecessaryBtn' => "Nur notwendige",
        'showPreferencesBtn' => "Präferenzen verwalten"
    ];

    private $preferencesModal = [
        'title' => 'Cookies Präferenzen',
        'acceptAllBtn' => "Alles akzeptieren",
        'acceptNecessaryBtn' => "Nur notwendige",
        'savePreferencesBtn' => "Mein Auswahl speichern",
        'closeIconLabel' => "schließen"
    ];

    private $cookieCategories = [
        'necessary' => [
            'enabled' => true,
            'readOnly' => true,
        ],
        'external_media' => [
            'enabled' => false,
            'readOnly' => false
        ],
        'analytics' => [
            'enabled' => false,
            'readOnly' => false
        ],
        'marketing' => [
            'enabled' => false,
            'readOnly' => false
        ],
        'security' => [
            'enabled' => false,
            'readOnly' => false
        ]
    ];

    private $preferencesSections = [
        [
            'title' => 'Strictly necessary cookies',
            'description' => 'Diese Cookies sind für das ordnungsgemäße Funktionieren meiner Website unerlässlich. Ohne diese Cookies würde die Website nicht ordnungsgemäß funktionieren.',
            'linkedCategory' => 'necessary'
        ],
        [
            'title' => 'Externe Medien',
            'description' => 'Diese Cookies ermöglichen die Einbindung von Inhalten und Diensten Dritter, wie z.B. Videos oder Social Media Feeds. Wenn Sie diese Cookies nicht akzeptieren, werden Inhalte von Drittanbietern nicht angezeigt.',
            'linkedCategory' => 'external_media'
        ],
        [
            'title' => 'Performance and Analytics cookies',
            'description' => 'Dank dieser Cookies kann sich die Website an die Einstellungen erinnern, die Sie in der Vergangenheit vorgenommen haben',
            'linkedCategory' => 'analytics'
        ],
        [
            'title' => 'Marketing & Werbungen',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
            'linkedCategory' => 'marketing'
        ],
        [
            'title' => 'Security cookies',
            'description' => 'Diese Cookies tragen zur Sicherheit der Website bei, indem sie bösartige Aktivitäten erkennen und verhindern. Sie helfen dabei, die Website vor Angriffen zu schützen und die Integrität der Benutzerdaten zu gewährleisten.',
            'linkedCategory' => 'security'
        ]
    ];

    public $services = [
        'youtube' => [
            'category' => 'external_media',
            'label' => 'YouTube',
        ],
    ];

    public function __construct()
    {
        $this->initConsentModalConfig();
        $this->initPreferenceModalConfig();
        $this->initGUISettings();
    }

    public function getConfig(): array
    {
        $settings = $this->settings;

        $consentModalConfig = $this->consentModalConfig;
        $consentModalConfig['footer'] = \sprintf(
            '<a href="%s" target="_blank">%s</a> <a href="%s" target="_blank">%s</a>',
            $settings['privacy_btn']['url'],
            $settings['privacy_btn']['label'],
            $settings['imprint_btn']['url'],
            $settings['imprint_btn']['label']
        );

        $preferencesConfig = $this->preferencesModal;
        $preferencesConfig['sections'] = $this->preferencesSections;

        $categories = $this->cookieCategories;
        foreach ($categories as $key => $category) {
            $categories[$key]['services'] = [];
            foreach ($this->services as $serviceKey => $service) {
                if ($service['category'] === $key) {
                    $categories[$key]['services'][$serviceKey] = $service;
                }
            }
        }

        return [
            'settings' => $settings,
            'guiOptions' => $this->guiOptions,
            'categories' => $categories,
            'language' => [
                'default' => $settings['default_language'],
                'translations' => [
                    $settings['default_language'] => [
                        'consentModal' => $consentModalConfig,
                        'preferencesModal' => $preferencesConfig
                    ]
                ]
            ]
        ];
    }

    private static function validateRequiredString(array $config, string $key, string $message): void
    {
        if (empty($config[$key])) {
            throw new \InvalidArgumentException($message);
        }
    }

    private function initConsentModalConfig(): void
    {
        $saved = $this->get_option('consent_modal_config');
        if ($saved) {
            $this->consentModalConfig = array_merge($this->consentModalConfig, json_decode($saved, true));
        }
    }

    public static function saveConsentModalConfig(array $config): bool
    {
        self::validateRequiredString($config, 'title', 'Der Titel des Consent Modals ist erforderlich.');
        self::validateRequiredString($config, 'description', 'Die Beschreibung des Consent Modals ist erforderlich.');
        self::validateRequiredString($config, 'acceptAllBtn', 'Die Bezeichnung des "Alles Akzeptieren" Buttons ist erforderlich.');
        self::validateRequiredString($config, 'acceptNecessaryBtn', 'Die Bezeichnung des "Nur notwendige Akzeptieren" Buttons ist erforderlich.');
        self::validateRequiredString($config, 'showPreferencesBtn', 'Die Bezeichnung des "Präferenzen verwalten" Buttons ist erforderlich.');

        update_option(self::OPTION_PREFIX . 'consent_modal_config', json_encode([
            'title' => sanitize_text_field($config['title']),
            'description' => sanitize_textarea_field($config['description']),
            'acceptAllBtn' => sanitize_text_field($config['acceptAllBtn']),
            'acceptNecessaryBtn' => sanitize_text_field($config['acceptNecessaryBtn']),
            'showPreferencesBtn' => sanitize_text_field($config['showPreferencesBtn']),
        ]));

        return true;
    }

    private function initPreferenceModalConfig(): void
    {
        $saved = $this->get_option('preference_modal_config');
        if ($saved) {
            $this->preferencesModal = array_merge($this->preferencesModal, json_decode($saved, true));
        }
    }

    public static function savePreferenceModalConfig(array $config): bool
    {
        self::validateRequiredString($config, 'title', 'Der Titel des Präferenzen Modals ist erforderlich.');
        self::validateRequiredString($config, 'acceptAllBtn', 'Die Bezeichnung des "Alles Akzeptieren" Buttons ist erforderlich.');
        self::validateRequiredString($config, 'acceptNecessaryBtn', 'Die Bezeichnung des "Nur notwendige Akzeptieren" Buttons ist erforderlich.');
        self::validateRequiredString($config, 'savePreferencesBtn', 'Die Bezeichnung des "Präferenzen speichern" Buttons ist erforderlich.');
        self::validateRequiredString($config, 'closeIconLabel', 'Die Bezeichnung des "Schließen" Icons ist erforderlich.');

        update_option(self::OPTION_PREFIX . 'preference_modal_config', json_encode([
            'title' => sanitize_text_field($config['title']),
            'acceptAllBtn' => sanitize_text_field($config['acceptAllBtn']),
            'acceptNecessaryBtn' => sanitize_text_field($config['acceptNecessaryBtn']),
            'savePreferencesBtn' => sanitize_text_field($config['savePreferencesBtn']),
            'closeIconLabel' => sanitize_text_field($config['closeIconLabel']),
        ]));

        return true;
    }

    private function initGUISettings(): void
    {
        $saved = $this->get_option('gui_settings');
        if ($saved) {
            $this->guiOptions = array_replace_recursive($this->guiOptions, json_decode($saved, true));
        }
    }

    public static function saveGUISettings(array $settings): bool
    {
        if (isset($settings['consentModal'])) {
            if (isset($settings['consentModal']['layout']) && !\in_array($settings['consentModal']['layout'], ['box', 'bar', 'cloud'])) {
                throw new \InvalidArgumentException('Das Layout Basis-Design des Consent Modals ist ungültig. Erlaubte Werte sind: box, bar, cloud.');
            }
            if (isset($settings['consentModal']['position']) && !\in_array($settings['consentModal']['position'], ['top left', 'top center', 'top right', 'middle left', 'middle center', 'middle right', 'bottom left', 'bottom center', 'bottom right', 'top', 'bottom'])) {
                throw new \InvalidArgumentException('Die Position des Consent Modals ist ungültig. Erlaubte Werte sind: top left, top center, top right, middle left, middle center, middle right, bottom left, bottom center, bottom right.');
            }
        }

        if (isset($settings['preferencesModal'])) {
            if (isset($settings['preferencesModal']['layout']) && !\in_array($settings['preferencesModal']['layout'], ['box', 'bar'])) {
                throw new \InvalidArgumentException('Das Layout Basis-Design des Präferenzen Modals ist ungültig. Erlaubte Werte sind: box, bar.');
            }
            if (isset($settings['preferencesModal']['position']) && !\in_array($settings['preferencesModal']['position'], ['left', 'right'])) {
                throw new \InvalidArgumentException('Die Position des Präferenzen Modals ist ungültig. Erlaubte Werte sind: left, right.');
            }
        }

        update_option(self::OPTION_PREFIX . 'gui_settings', json_encode([
            'consentModal' => [
                'layout' => $settings['consentModal']['layout'] ?? null,
                'position' => $settings['consentModal']['position'] ?? null,
            ],
            'preferencesModal' => [
                'layout' => $settings['preferencesModal']['layout'] ?? null,
                'position' => $settings['preferencesModal']['position'] ?? null,
            ],
        ]));

        return true;
    }

    private function get_option(string $option_name, $default = null)
    {
        return get_option(self::OPTION_PREFIX . $option_name, $default);
    }
}
