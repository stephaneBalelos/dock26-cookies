<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
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
            'layout' => 'bar',
            'position' => 'bottom'
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
        "external_media" => [
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

    private $prefrencesSections = [
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


    public function __construct($preferences, $categories)
    {
        // Init
        $this->initConsentModalConfig();

        // Init Gui Settings
        $this->initGUISettings();
    }

    public function getConfig()
    {
        $settings = $this->settings;

        $consentConfig['footer'] = '
            <a href="' . $settings['privacy_btn']['url'] . '" target="_blank">' . $settings['privacy_btn']['url'] . '</a>
            <a href="' . $settings['imprint_btn']['url'] . '" target="_blank">' . $settings['imprint_btn']['url'] . '</a>';

        $preferencesConfig = $this->preferencesModal;
        $preferencesConfig['sections'] = $this->prefrencesSections;


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
                'default' => $this->settings['default_language'],
                'translations' => [
                    $this->settings['default_language'] => [
                        'consentModal' => $this->consentModalConfig,
                        'preferencesModal' => $preferencesConfig
                    ]
                ]
            ]
        ];
    }

    private function initConsentModalConfig()
    {
        $savedConfig = $this->get_option('consent_modal_config');
        if ($savedConfig) {
            $this->consentModalConfig = [...$this->consentModalConfig, ...json_decode($savedConfig, true)];
        }
    }

    public static function saveConsenModalConfig(array $config)
    {
        // Check and Validate Config
        // Title
        if (!isset($config['title']) || empty($config['title']) || !\strlen($config['title']) > 0) {
            throw new \InvalidArgumentException('Der Titel des Consent Modals ist erforderlich.');
        }

        // Description
        if (!isset($config['description']) || empty($config['description']) || !\strlen($config['description']) > 0) {
            throw new \InvalidArgumentException('Die Beschreibung des Consent Modals ist erforderlich.');
        }

        // Accept All Button
        if (!isset($config['acceptAllBtn']) || empty($config['acceptAllBtn']) || !\strlen($config['acceptAllBtn']) > 0) {
            throw new \InvalidArgumentException('Die Bezeichnung des "Alles Akzeptieren" Buttons ist erforderlich.');
        }

        // Reject All Button
        if (!isset($config['acceptNecessaryBtn']) || empty($config['acceptNecessaryBtn']) || !\strlen($config['acceptNecessaryBtn']) > 0) {
            throw new \InvalidArgumentException('Die Bezeichnung des "Nur notwendige Akzeptieren" Buttons ist erforderlich.');
        }

        // Show Preferences Button
        if (!isset($config['showPreferencesBtn']) || empty($config['showPreferencesBtn']) || !\strlen($config['showPreferencesBtn']) > 0) {
            throw new \InvalidArgumentException('Die Bezeichnung des "Präferenzen verwalten" Buttons ist erforderlich.');
        }

        // Layout
        if (isset($config['layout'])) {
            if (!isset($config['layout']['base']) || !\in_array($config['layout']['base'], ['box', 'bar', 'cloud'])) {
                throw new \InvalidArgumentException('Das Layout Basis-Design ist ungültig. Erlaubte Werte sind: box, bar, cloud.');
            }

            if (!isset($config['layout']['variant']) || !\in_array($config['layout']['variant'], ['wide', 'inline'])) {
                throw new \InvalidArgumentException('Das Layout Variant ist ungültig. Erlaubte Werte sind: wide, inline.');
            }
        }

        // Position
        if (isset($config['position'])) {
            if (!isset($config['position']['x']) || !\in_array($config['position']['x'], ['left', 'center', 'right'])) {
                throw new \InvalidArgumentException('Die Position X ist ungültig. Erlaubte Werte sind: left, center, right.');
            }
            if (!isset($config['position']['y']) || !\in_array($config['position']['y'], ['top', 'middle', 'bottom'])) {
                throw new \InvalidArgumentException('Die Position Y ist ungültig. Erlaubte Werte sind: top, middle, bottom.');
            }
        }

        $config = [
            'title' => sanitize_text_field($config['title']),
            'description' => sanitize_textarea_field($config['description']),
            'acceptAllBtn' => sanitize_text_field($config['acceptAllBtn']),
            'acceptNecessaryBtn' => sanitize_text_field($config['acceptNecessaryBtn']),
            'showPreferencesBtn' => sanitize_text_field($config['showPreferencesBtn']),
            'layout' => $config['layout'] ?? null,
            'position' => $config['position'] ?? null
        ];

        // Save config
        update_option(self::OPTION_PREFIX . 'consent_modal_config', json_encode($config));

        return true;
    }

    private function initGUISettings()
    {
        $savedGuiSettings = $this->get_option('gui_settings');
        if ($savedGuiSettings) {
            $this->guiOptions = [...$this->guiOptions, ...json_decode($savedGuiSettings, true)];
        }
    }

    public static function saveGUISettings(array $settings)
    {
        // Validate settings here if needed
        if (isset($settings['consentModal'])) {
            if (isset($settings['consentModal']['layout']) && (!\in_array($settings['consentModal']['layout'], ['box', 'bar', 'cloud']))) {
                throw new \InvalidArgumentException('Das Layout Basis-Design des Consent Modals ist ungültig. Erlaubte Werte sind: box, bar, cloud.');
            }
            if (isset($settings['consentModal']['position']) && (!\in_array($settings['consentModal']['position'], ['top left', 'top center', 'top right', 'middle left', 'middle center', 'middle right', 'bottom left', 'bottom center', 'bottom right', 'top', 'bottom']))) {
                throw new \InvalidArgumentException('Die Position des Consent Modals ist ungültig. Erlaubte Werte sind: top left, top center, top right, middle left, middle center, middle right, bottom left, bottom center, bottom right.');
            }
        }

        if (isset($settings['preferencesModal'])) {
            if (isset($settings['preferencesModal']['layout']) && (!\in_array($settings['preferencesModal']['layout'], ['box', 'bar']))) {
                throw new \InvalidArgumentException('Das Layout Basis-Design des Präferenzen Modals ist ungültig. Erlaubte Werte sind: box, bar.');
            }
            if (isset($settings['preferencesModal']['position']) && (!\in_array($settings['preferencesModal']['position'], ['left', 'right']))) {
                throw new \InvalidArgumentException('Die Position des Präferenzen Modals ist ungültig. Erlaubte Werte sind: left, right.');
            }
        }

        $guiSettings = [
            'consentModal' => [
                'layout' => $settings['consentModal']['layout'] ?? null,
                'position' => $settings['consentModal']['position'] ?? null
            ],
            'preferencesModal' => [
                'layout' => $settings['preferencesModal']['layout'] ?? null,
                'position' => $settings['preferencesModal']['position'] ?? null
            ]
        ];

        // Save settings
        update_option(self::OPTION_PREFIX . 'gui_settings', json_encode($guiSettings));

        return true;
    }

    private function get_option($option_name, $default = null)
    {
        return get_option($this::OPTION_PREFIX . $option_name, $default);
    }
}
