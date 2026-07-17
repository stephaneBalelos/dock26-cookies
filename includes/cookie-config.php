<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit;
}

class CookieConfig
{
    private const OPTION_PREFIX = 'dock26_cookies_';

    /**
     * CSS custom properties of the cookie consent modal, keyed by variable name.
     * Values that the upstream stylesheet defines as `var(--other)` references are
     * resolved here to the concrete value they point at.
     */
    private const CUSTOM_STYLES = [
        '--cc-bg' => '#ffffff',
        '--cc-primary-color' => '#2c2f31',
        '--cc-secondary-color' => '#5e6266',

        '--cc-btn-primary-bg' => '#30363c',
        '--cc-btn-primary-color' => '#ffffff',
        '--cc-btn-primary-border-color' => '#30363c',
        '--cc-btn-primary-hover-bg' => '#000000',
        '--cc-btn-primary-hover-color' => '#ffffff',
        '--cc-btn-primary-hover-border-color' => '#000000',

        '--cc-btn-secondary-bg' => '#eaeff2',
        '--cc-btn-secondary-color' => '#2c2f31',
        '--cc-btn-secondary-border-color' => '#eaeff2',
        '--cc-btn-secondary-hover-bg' => '#d4dae0',
        '--cc-btn-secondary-hover-color' => '#000000',
        '--cc-btn-secondary-hover-border-color' => '#d4dae0',

        '--cc-separator-border-color' => '#f0f4f7',

        '--cc-toggle-on-bg' => '#30363c',
        '--cc-toggle-off-bg' => '#667481',
        '--cc-toggle-on-knob-bg' => '#ffffff',
        '--cc-toggle-off-knob-bg' => '#ffffff',
        '--cc-toggle-enabled-icon-color' => '#ffffff',
        '--cc-toggle-disabled-icon-color' => '#ffffff',
        '--cc-toggle-readonly-bg' => '#d5dee2',
        '--cc-toggle-readonly-knob-bg' => '#ffffff',
        '--cc-toggle-readonly-knob-icon-color' => '#d5dee2',

        '--cc-section-category-border' => '#f0f4f7',
        '--cc-cookie-category-block-bg' => '#f0f4f7',
        '--cc-cookie-category-block-border' => '#f0f4f7',
        '--cc-cookie-category-block-hover-bg' => '#e9eff4',
        '--cc-cookie-category-block-hover-border' => '#e9eff4',
        '--cc-cookie-category-expanded-block-bg' => '#ffffff00',
        '--cc-cookie-category-expanded-block-hover-bg' => '#dee4e9',

        '--cc-overlay-bg' => 'rgba(0, 0, 0, 0.65)',
        '--cc-webkit-scrollbar-bg' => '#f0f4f7',
        '--cc-webkit-scrollbar-hover-bg' => '#000000',

        '--cc-footer-bg' => '#eaeff2',
        '--cc-footer-color' => '#5e6266',
        '--cc-footer-border-color' => '#e4eaed',

        '--cc-modal-border-radius' => '8px',
        '--cc-btn-border-radius' => '6px',
        '--cc-modal-margin' => '16px',
    ];

    /**
     * Custom style properties holding a pixel length instead of a color.
     */
    private const SIZE_KEYS = [
        '--cc-modal-border-radius',
        '--cc-btn-border-radius',
        '--cc-modal-margin',
    ];

    private const SIZE_PATTERN = '/^\d+(\.\d+)?px$/';

    private const COOKIE_NAME_PATTERN = '/^[A-Za-z0-9_-]{1,64}$/';

    private const COLOR_PATTERN = '/^(#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})|rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*(0|1|0?\.\d+)\s*)?\)|hsla?\(\s*\d+(\.\d+)?(deg)?\s*,\s*\d+(\.\d+)?%\s*,\s*\d+(\.\d+)?%\s*(,\s*(0|1|0?\.\d+)\s*)?\)|transparent)$/';

    private $customStyles = self::CUSTOM_STYLES;

    // cookie_name feeds cookie.name of the frontend consent script. It is
    // admin-editable, but renaming it invalidates the consent every existing
    // visitor has already stored — the admin form warns about this before save.
    private $settings = [
        'cookie_name' => 'cc_cookie',
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
        'title' => 'Wir benutzen Cookies',
        'description' => "Auf dieser Website nutzen wir Cookies und vergleichbare Funktionen zur Verarbeitung von Endgeräteinformationen und personenbezogenen Daten (wie z.B. IP-Adressen oder Browserinformationen). Die Verarbeitung dient der Einbindung von Inhalten, externen Diensten und Elementen Dritter, der statistischen Analyse/Messung, personalisierten Werbung sowie der Einbindung sozialer Medien.",
        'acceptAllBtn' => "Alles akzeptieren",
        'acceptNecessaryBtn' => "Nur notwendige",
        'showPreferencesBtn' => "Präferenzen verwalten"
    ];

    private $preferencesModal = [
        'title' => 'Cookies Präferenzen',
        'acceptAllBtn' => "Alles akzeptieren",
        'acceptNecessaryBtn' => "Nur notwendige",
        'savePreferencesBtn' => "Meine Auswahl speichern",
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
            'title' => 'Unbedingt erforderliche Cookies',
            'description' => 'Diese Cookies sind für das ordnungsgemäße Funktionieren der Website unerlässlich. Ohne diese Cookies würde die Website nicht ordnungsgemäß funktionieren.',
            'linkedCategory' => 'necessary'
        ],
        [
            'title' => 'Externe Medien',
            'description' => 'Diese Cookies ermöglichen die Einbindung von Inhalten und Diensten Dritter, wie z.B. Videos oder Social Media Feeds. Wenn Sie diese Cookies nicht akzeptieren, werden Inhalte von Drittanbietern nicht angezeigt.',
            'linkedCategory' => 'external_media'
        ],
        [
            'title' => 'Performance- und Analyse-Cookies',
            'description' => 'Dank dieser Cookies kann sich die Website an die Einstellungen erinnern, die Sie in der Vergangenheit vorgenommen haben.',
            'linkedCategory' => 'analytics'
        ],
        [
            'title' => 'Marketing & Werbung',
            'description' => 'Diese Cookies werden verwendet, um Ihnen personalisierte Werbung anzuzeigen, die auf Ihren Interessen basiert. Sie helfen außerdem dabei, die Wirksamkeit von Werbekampagnen zu messen.',
            'linkedCategory' => 'marketing'
        ],
        [
            'title' => 'Sicherheits-Cookies',
            'description' => 'Diese Cookies tragen zur Sicherheit der Website bei, indem sie bösartige Aktivitäten erkennen und verhindern. Sie helfen dabei, die Website vor Angriffen zu schützen und die Integrität der Benutzerdaten zu gewährleisten.',
            'linkedCategory' => 'security'
        ]
    ];

    public $services = [
        'youtube' => [
            'category' => 'external_media',
            'label' => 'YouTube',
        ],
        'googlemaps' => [
            'category' => 'external_media',
            'label' => 'Google Maps',
        ],
    ];

    public function __construct()
    {
        $this->initSettings();
        $this->initConsentModalConfig();
        $this->initPreferenceModalConfig();
        $this->initGUISettings();
        $this->initCustomStyles();
    }

    public function getCustomStyles(): array
    {
        return $this->customStyles;
    }

    public function getConfig(): array
    {
        $settings = $this->settings;

        $consentModalConfig = $this->consentModalConfig;
        $consentModalConfig['footer'] = \sprintf(
            '<a href="%s" target="_blank">%s</a> <a href="%s" target="_blank">%s</a>',
            esc_url($settings['privacy_btn']['url']),
            esc_html($settings['privacy_btn']['label']),
            esc_url($settings['imprint_btn']['url']),
            esc_html($settings['imprint_btn']['label'])
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
        if (!isset($config[$key]) || !\is_string($config[$key]) || \trim($config[$key]) === '') {
            throw new \InvalidArgumentException($message);
        }
    }

    private function initSettings(): void
    {
        $saved = self::readJsonOption('settings');
        if ($saved === null) {
            return;
        }

        // Only the admin-editable keys are read back. The button labels and
        // default_language are never persisted, so they always fall through to
        // the defaults above.
        if (isset($saved['cookie_name'])) {
            $this->settings['cookie_name'] = $saved['cookie_name'];
        }

        foreach (['privacy_btn', 'imprint_btn'] as $button) {
            if (isset($saved[$button]['url'])) {
                $this->settings[$button]['url'] = $saved[$button]['url'];
            }
        }
    }

    public static function saveGlobalSettings(array $settings): bool
    {
        self::validateRequiredString($settings, 'cookie_name', 'Der Cookie-Name ist erforderlich.');

        $cookieName = \trim($settings['cookie_name']);
        if (!\preg_match(self::COOKIE_NAME_PATTERN, $cookieName)) {
            throw new \InvalidArgumentException('Der Cookie-Name darf nur Buchstaben, Ziffern, Bindestriche und Unterstriche enthalten.');
        }

        update_option(self::OPTION_PREFIX . 'settings', json_encode([
            'cookie_name' => $cookieName,
            'privacy_btn' => [
                'url' => self::sanitizeUrlSetting($settings, 'privacy_btn', 'Datenschutzerklärung'),
            ],
            'imprint_btn' => [
                'url' => self::sanitizeUrlSetting($settings, 'imprint_btn', 'Impressum'),
            ],
        ]));

        return true;
    }

    /**
     * Validates and sanitizes the `url` of one of the footer button settings.
     * esc_url_raw() empties out unsafe schemes such as javascript:, which is
     * what makes an empty result a rejection rather than a fallback.
     */
    private static function sanitizeUrlSetting(array $settings, string $key, string $label): string
    {
        if (!isset($settings[$key]) || !\is_array($settings[$key])) {
            throw new \InvalidArgumentException(\sprintf('Die Einstellungen für "%s" fehlen.', $label));
        }

        self::validateRequiredString($settings[$key], 'url', \sprintf('Die URL der %s ist erforderlich.', $label));

        $url = esc_url_raw(\trim($settings[$key]['url']));
        if ($url === '') {
            throw new \InvalidArgumentException(\sprintf('Die URL der %s ist ungültig.', $label));
        }

        return $url;
    }

    private function initConsentModalConfig(): void
    {
        $saved = self::readJsonOption('consent_modal_config');
        if ($saved !== null) {
            $this->consentModalConfig = array_merge($this->consentModalConfig, $saved);
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
        $saved = self::readJsonOption('preference_modal_config');
        if ($saved !== null) {
            $this->preferencesModal = array_merge($this->preferencesModal, $saved);
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
        $saved = self::readJsonOption('gui_settings');
        if ($saved === null) {
            return;
        }

        // Copy only present, non-null values so partial or legacy saves
        // can't wipe out the defaults.
        foreach (['consentModal', 'preferencesModal'] as $modal) {
            foreach (['layout', 'position'] as $key) {
                if (isset($saved[$modal][$key])) {
                    $this->guiOptions[$modal][$key] = $saved[$modal][$key];
                }
            }
        }
    }

    public static function saveGUISettings(array $settings): bool
    {
        if (isset($settings['consentModal'])) {
            if (isset($settings['consentModal']['layout']) && !\in_array($settings['consentModal']['layout'], ['box', 'bar', 'cloud'])) {
                throw new \InvalidArgumentException('Das Layout Basis-Design des Consent Modals ist ungültig. Erlaubte Werte sind: box, bar, cloud.');
            }
            if (isset($settings['consentModal']['position']) && !\in_array($settings['consentModal']['position'], ['top left', 'top center', 'top right', 'middle left', 'middle center', 'middle right', 'bottom left', 'bottom center', 'bottom right', 'top', 'middle', 'bottom'])) {
                throw new \InvalidArgumentException('Die Position des Consent Modals ist ungültig. Erlaubte Werte sind: top, middle, bottom, top left, top center, top right, middle left, middle center, middle right, bottom left, bottom center, bottom right.');
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

        // Merge over the stored settings so a partial payload (e.g. only the
        // consent modal) doesn't clear the other modal's values.
        $clean = self::readJsonOption('gui_settings') ?? [];
        foreach (['consentModal', 'preferencesModal'] as $modal) {
            foreach (['layout', 'position'] as $key) {
                if (isset($settings[$modal][$key])) {
                    $clean[$modal][$key] = $settings[$modal][$key];
                }
            }
        }

        update_option(self::OPTION_PREFIX . 'gui_settings', json_encode($clean));

        return true;
    }

    private function initCustomStyles(): void
    {
        $saved = self::readJsonOption('custom_styles');
        if ($saved !== null) {
            $this->customStyles = array_merge($this->customStyles, $saved);
        }
    }

    public static function saveCustomStyles(array $styles): bool
    {
        $clean = [];

        foreach ($styles as $key => $value) {
            if (!\array_key_exists($key, self::CUSTOM_STYLES)) {
                throw new \InvalidArgumentException(\sprintf('Die CSS-Eigenschaft "%s" ist unbekannt.', $key));
            }

            $isSize = \in_array($key, self::SIZE_KEYS, true);
            $pattern = $isSize ? self::SIZE_PATTERN : self::COLOR_PATTERN;

            if (!\is_string($value) || !\preg_match($pattern, $value)) {
                throw new \InvalidArgumentException(\sprintf(
                    $isSize
                        ? 'Der Wert für "%s" ist ungültig. Erwartet wird eine Pixel-Angabe, z.B. 8px.'
                        : 'Der Wert für "%s" ist ungültig. Erwartet wird eine Farbe, z.B. #ffffff oder rgba(0, 0, 0, 0.65).',
                    $key
                ));
            }

            $clean[$key] = $value;
        }

        update_option(self::OPTION_PREFIX . 'custom_styles', json_encode($clean));

        return true;
    }

    /**
     * Removes every option this class persists. Called on plugin uninstall.
     */
    public static function deleteAllOptions(): void
    {
        foreach (['settings', 'consent_modal_config', 'preference_modal_config', 'gui_settings', 'custom_styles'] as $option_name) {
            delete_option(self::OPTION_PREFIX . $option_name);
        }
    }

    /**
     * Reads a JSON-encoded plugin option; returns null if it is missing or
     * cannot be decoded to an array (e.g. a corrupted option value).
     */
    private static function readJsonOption(string $option_name): ?array
    {
        $saved = get_option(self::OPTION_PREFIX . $option_name);
        if (!\is_string($saved) || $saved === '') {
            return null;
        }

        $decoded = json_decode($saved, true);

        return \is_array($decoded) ? $decoded : null;
    }
}
