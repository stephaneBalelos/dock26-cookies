<?php

namespace Dock26Cookies;

if (!\defined('ABSPATH')) {
    exit;
}

/**
 * Defaults, persistence and validation of everything the admin can configure.
 *
 * An instance holds the effective configuration (defaults overlaid with the
 * saved options). The static save*() methods validate one settings section
 * and persist it; they throw \InvalidArgumentException with a message meant
 * for the admin when the input is invalid.
 *
 * Saved values are validated again when they are read back, so a corrupted
 * or tampered option row falls back to the default instead of reaching the
 * page.
 */
final class CookieConfig
{
    private const OPTION_PREFIX = 'dock26_cookies_';

    /** Every option this class persists (without prefix). Mirrored in uninstall.php. */
    public const OPTION_NAMES = [
        'settings',
        'consent_modal_config',
        'preference_modal_config',
        'gui_settings',
        'custom_styles',
    ];

    private const DEFAULT_COOKIE_NAME = 'cc_cookie';

    private const COOKIE_NAME_PATTERN = '/^[A-Za-z0-9_-]{1,64}$/';

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
     * CSS custom properties of the embed placeholders (iframemanager), each
     * taking the value of the cookie consent property it is mapped to. The
     * placeholders have no settings of their own.
     */
    private const IFRAME_STYLES = [
        '--im-bg' => '--cc-bg',
        '--im-color' => '--cc-primary-color',
        // The modals color their links the same way.
        '--im-link-color' => '--cc-btn-primary-bg',
        '--im-link-hover-color' => '--cc-primary-color',

        '--im-btn-bg' => '--cc-btn-primary-bg',
        '--im-btn-color' => '--cc-btn-primary-color',
        '--im-btn-hover-bg' => '--cc-btn-primary-hover-bg',

        '--im-btn-s-bg' => '--cc-btn-secondary-bg',
        '--im-btn-s-color' => '--cc-btn-secondary-color',
        '--im-btn-s-hover-bg' => '--cc-btn-secondary-hover-bg',
        '--im-btn-s-active-bg' => '--cc-btn-secondary-hover-bg',
    ];

    /** Custom style properties holding a pixel length instead of a color. */
    private const SIZE_KEYS = [
        '--cc-modal-border-radius',
        '--cc-btn-border-radius',
        '--cc-modal-margin',
    ];

    private const SIZE_PATTERN = '/^\d+(\.\d+)?px$/';

    private const COLOR_PATTERN = '/^(#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})|rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*(0|1|0?\.\d+)\s*)?\)|hsla?\(\s*\d+(\.\d+)?(deg)?\s*,\s*\d+(\.\d+)?%\s*,\s*\d+(\.\d+)?%\s*(,\s*(0|1|0?\.\d+)\s*)?\)|transparent)$/';

    /**
     * Editable texts of the consent modal: key => label used in validation
     * messages, maximum length and whether line breaks are kept. The limits
     * match the admin form (ConsentModalForm.vue).
     */
    private const CONSENT_MODAL_FIELDS = [
        'title' => ['label' => 'Der Titel des Consent Modals', 'max' => 64],
        'description' => ['label' => 'Die Beschreibung des Consent Modals', 'max' => 500, 'multiline' => true],
        'acceptAllBtn' => ['label' => 'Die Bezeichnung des "Alles akzeptieren" Buttons', 'max' => 24],
        'acceptNecessaryBtn' => ['label' => 'Die Bezeichnung des "Nur notwendige" Buttons', 'max' => 24],
        'showPreferencesBtn' => ['label' => 'Die Bezeichnung des "Präferenzen verwalten" Buttons', 'max' => 24],
    ];

    /** Editable texts of the preferences modal, see CONSENT_MODAL_FIELDS. */
    private const PREFERENCES_MODAL_FIELDS = [
        'title' => ['label' => 'Der Titel des Präferenzen Modals', 'max' => 64],
        'acceptAllBtn' => ['label' => 'Die Bezeichnung des "Alles akzeptieren" Buttons', 'max' => 24],
        'acceptNecessaryBtn' => ['label' => 'Die Bezeichnung des "Nur notwendige" Buttons', 'max' => 24],
        'savePreferencesBtn' => ['label' => 'Die Bezeichnung des "Präferenzen speichern" Buttons', 'max' => 24],
        'closeIconLabel' => ['label' => 'Die Bezeichnung des "Schließen" Icons', 'max' => 24],
    ];

    /** Allowed values per modal and GUI option. */
    private const GUI_CHOICES = [
        'consentModal' => [
            'layout' => ['box', 'bar', 'cloud'],
            'position' => [
                'top', 'middle', 'bottom',
                'top left', 'top center', 'top right',
                'middle left', 'middle center', 'middle right',
                'bottom left', 'bottom center', 'bottom right',
            ],
        ],
        'preferencesModal' => [
            'layout' => ['box', 'bar'],
            'position' => ['left', 'right'],
        ],
    ];

    private const GUI_LABELS = [
        'consentModal' => 'des Consent Modals',
        'preferencesModal' => 'des Präferenzen Modals',
        'layout' => 'Das Layout',
        'position' => 'Die Position',
    ];

    /**
     * Third-party services this plugin can consent-gate. `id_pattern` is what
     * a `data-id` for that service may look like; the frontend bundle holds
     * the matching embed definitions (SERVICE_DEFINITIONS in main.ts).
     */
    private const SERVICES = [
        'youtube' => [
            'category' => 'external_media',
            'label' => 'YouTube',
            'id_pattern' => '/^[A-Za-z0-9_-]{6,20}$/',
        ],
        'googlemaps' => [
            'category' => 'external_media',
            'label' => 'Google Maps',
            // The `pb` parameter of a Google Maps embed URL.
            'id_pattern' => '/^[A-Za-z0-9!.%+_:~*,()-]{1,2048}$/',
        ],
        // Any iframe of a provider not listed above (Embeds::GENERIC_SERVICE).
        // Its id is the full iframe URL.
        'iframe' => [
            'category' => 'external_media',
            'label' => 'Sonstige eingebettete Inhalte',
            'id_pattern' => '~^https?://[^\s"\'<>$]{1,2048}$~i',
        ],
    ];

    private const CATEGORIES = [
        'necessary' => ['enabled' => true, 'readOnly' => true],
        'external_media' => ['enabled' => false, 'readOnly' => false],
        'analytics' => ['enabled' => false, 'readOnly' => false],
        'marketing' => ['enabled' => false, 'readOnly' => false],
        'security' => ['enabled' => false, 'readOnly' => false],
    ];

    private const PREFERENCES_SECTIONS = [
        [
            'title' => 'Unbedingt erforderliche Cookies',
            'description' => 'Diese Cookies sind für das ordnungsgemäße Funktionieren der Website unerlässlich. Ohne diese Cookies würde die Website nicht ordnungsgemäß funktionieren.',
            'linkedCategory' => 'necessary',
        ],
        [
            'title' => 'Externe Medien',
            'description' => 'Diese Cookies ermöglichen die Einbindung von Inhalten und Diensten Dritter, wie z.B. Videos oder Social Media Feeds. Wenn Sie diese Cookies nicht akzeptieren, werden Inhalte von Drittanbietern nicht angezeigt.',
            'linkedCategory' => 'external_media',
        ],
        [
            'title' => 'Performance- und Analyse-Cookies',
            'description' => 'Diese Cookies helfen uns zu verstehen, wie Besucher die Website nutzen, indem Informationen gesammelt und statistisch ausgewertet werden. So können wir die Website laufend verbessern.',
            'linkedCategory' => 'analytics',
        ],
        [
            'title' => 'Marketing & Werbung',
            'description' => 'Diese Cookies werden verwendet, um Ihnen personalisierte Werbung anzuzeigen, die auf Ihren Interessen basiert. Sie helfen außerdem dabei, die Wirksamkeit von Werbekampagnen zu messen.',
            'linkedCategory' => 'marketing',
        ],
        [
            'title' => 'Sicherheits-Cookies',
            'description' => 'Diese Cookies tragen zur Sicherheit der Website bei, indem sie bösartige Aktivitäten erkennen und verhindern. Sie helfen dabei, die Website vor Angriffen zu schützen und die Integrität der Benutzerdaten zu gewährleisten.',
            'linkedCategory' => 'security',
        ],
    ];

    /**
     * cookie_name feeds cookie.name of the frontend consent script. Renaming
     * it invalidates the consent every existing visitor has already stored —
     * the admin form warns about this before save. The button labels and
     * default_language are not editable and never persisted.
     *
     * @var array{cookie_name: string, default_language: string, privacy_btn: array{label: string, url: string}, imprint_btn: array{label: string, url: string}}
     */
    private array $settings = [
        'cookie_name' => self::DEFAULT_COOKIE_NAME,
        'default_language' => 'de',
        'privacy_btn' => ['label' => 'Datenschutzerklärung', 'url' => '#'],
        'imprint_btn' => ['label' => 'Impressum', 'url' => '#'],
    ];

    /** @var array<string, array<string, string>> */
    private array $guiOptions = [
        'consentModal' => ['layout' => 'box', 'position' => 'middle center'],
        'preferencesModal' => ['layout' => 'bar', 'position' => 'left'],
    ];

    /** @var array<string, string> */
    private array $consentModal = [
        'title' => 'Wir benutzen Cookies',
        'description' => 'Auf dieser Website nutzen wir Cookies und vergleichbare Funktionen zur Verarbeitung von Endgeräteinformationen und personenbezogenen Daten (wie z.B. IP-Adressen oder Browserinformationen). Die Verarbeitung dient der Einbindung von Inhalten, externen Diensten und Elementen Dritter, der statistischen Analyse/Messung, personalisierten Werbung sowie der Einbindung sozialer Medien.',
        'acceptAllBtn' => 'Alles akzeptieren',
        'acceptNecessaryBtn' => 'Nur notwendige',
        'showPreferencesBtn' => 'Präferenzen verwalten',
    ];

    /** @var array<string, string> */
    private array $preferencesModal = [
        'title' => 'Cookies Präferenzen',
        'acceptAllBtn' => 'Alles akzeptieren',
        'acceptNecessaryBtn' => 'Nur notwendige',
        'savePreferencesBtn' => 'Meine Auswahl speichern',
        'closeIconLabel' => 'schließen',
    ];

    /** @var array<string, string> */
    private array $customStyles = self::CUSTOM_STYLES;

    public function __construct()
    {
        $this->loadSettings();
        $this->loadModalTexts('consent_modal_config', self::CONSENT_MODAL_FIELDS, $this->consentModal);
        $this->loadModalTexts('preference_modal_config', self::PREFERENCES_MODAL_FIELDS, $this->preferencesModal);
        $this->loadGuiOptions();
        $this->loadCustomStyles();
    }

    // ---------------------------------------------------------------------
    // Output
    // ---------------------------------------------------------------------

    /**
     * The configuration consumed by the frontend bundle and the admin app
     * (type CookieConfig in types/index.ts).
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $language = $this->settings['default_language'];

        $consentModal = $this->consentModal;
        $footer = $this->renderFooterLinks();
        if ($footer !== '') {
            $consentModal['footer'] = $footer;
        }

        $preferencesModal = $this->preferencesModal;
        $preferencesModal['sections'] = self::PREFERENCES_SECTIONS;

        return [
            'settings' => $this->settings,
            'guiOptions' => $this->guiOptions,
            'categories' => self::categoriesWithServices(),
            'language' => [
                'default' => $language,
                'translations' => [
                    $language => [
                        'consentModal' => $consentModal,
                        'preferencesModal' => $preferencesModal,
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, string> */
    public function getCustomStyles(): array
    {
        return $this->customStyles;
    }

    /**
     * The custom styles as CSS: one rule for the cookie modals and the rules
     * that give the embed placeholders the same look.
     *
     * The modal rule is scoped to #cc-main rather than :root: the vendor
     * stylesheet declares some variables (e.g. --cc-modal-margin) on #cc-main
     * itself, which would win over an inherited value.
     */
    public function getCustomStylesCss(): string
    {
        $declarations = [];
        foreach ($this->customStyles as $property => $value) {
            $declarations[] = $property . ':' . $value;
        }

        return '#cc-main{' . \implode(';', $declarations) . '}' . $this->getIframeStylesCss();
    }

    /**
     * The placeholder variables carry the concrete values: the --cc-*
     * properties only exist inside #cc-main, so a placeholder elsewhere on
     * the page could not resolve a var() reference to them.
     */
    private function getIframeStylesCss(): string
    {
        $styles = $this->customStyles;

        $declarations = [];
        foreach (self::IFRAME_STYLES as $property => $source) {
            $declarations[] = $property . ':' . $styles[$source];
        }

        // No counterpart among the --cc-* properties. The overlay lies on top
        // of the optional thumbnail, so it stays translucent.
        $declarations[] = '--im-overlay-bg:color-mix(in srgb,' . $styles['--cc-bg'] . ' 80%,transparent)';
        $declarations[] = '--im-btn-active-box-shadow:0 0 0 .25em color-mix(in srgb,' . $styles['--cc-btn-primary-bg'] . ' 24%,transparent)';

        // The vendor stylesheet has no variable for the button radius.
        $buttons = 'div[data-service] .cll .c-l-b,div[data-service] .cll .c-la-b';

        return 'div[data-service]{' . \implode(';', $declarations) . '}'
            . $buttons . '{border-radius:' . $styles['--cc-btn-border-radius'] . '}';
    }

    /**
     * Everything the admin app needs to render its forms and the preview.
     *
     * @return array{config: array<string, mixed>, customStyles: array<string, string>}
     */
    public function toAdminData(): array
    {
        return [
            'config' => $this->getConfig(),
            'customStyles' => $this->getCustomStyles(),
        ];
    }

    public static function isKnownService(string $service): bool
    {
        return isset(self::SERVICES[$service]);
    }

    /** Whether $id is a plausible embed id for $service. */
    public static function isValidServiceId(string $service, string $id): bool
    {
        return self::isKnownService($service)
            && \preg_match(self::SERVICES[$service]['id_pattern'], $id) === 1;
    }

    /**
     * Links to the privacy policy and imprint. A link whose URL was never
     * configured (still the "#" placeholder) is left out.
     */
    private function renderFooterLinks(): string
    {
        $links = [];
        foreach (['privacy_btn', 'imprint_btn'] as $button) {
            $url = $this->settings[$button]['url'];
            if ($url === '' || $url === '#') {
                continue;
            }

            $links[] = \sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                esc_url($url),
                esc_html($this->settings[$button]['label'])
            );
        }

        return \implode(' ', $links);
    }

    /** @return array<string, array<string, mixed>> */
    private static function categoriesWithServices(): array
    {
        $categories = self::CATEGORIES;
        foreach (self::SERVICES as $serviceKey => $service) {
            $categories[$service['category']]['services'][$serviceKey] = ['label' => $service['label']];
        }

        return $categories;
    }

    // ---------------------------------------------------------------------
    // Global settings
    // ---------------------------------------------------------------------

    private function loadSettings(): void
    {
        $saved = self::readOption('settings');
        if ($saved === null) {
            return;
        }

        $cookieName = $saved['cookie_name'] ?? null;
        if (\is_string($cookieName) && \preg_match(self::COOKIE_NAME_PATTERN, $cookieName)) {
            $this->settings['cookie_name'] = $cookieName;
        }

        foreach (['privacy_btn', 'imprint_btn'] as $button) {
            $url = $saved[$button]['url'] ?? null;
            if (\is_string($url) && $url !== '') {
                $this->settings[$button]['url'] = $url;
            }
        }
    }

    /** @param array<string, mixed> $settings */
    public static function saveGlobalSettings(array $settings): void
    {
        $cookieName = \is_string($settings['cookie_name'] ?? null) ? \trim($settings['cookie_name']) : '';
        if ($cookieName === '') {
            throw new \InvalidArgumentException('Der Cookie-Name ist erforderlich.');
        }
        if (!\preg_match(self::COOKIE_NAME_PATTERN, $cookieName)) {
            throw new \InvalidArgumentException('Der Cookie-Name darf nur Buchstaben, Ziffern, Bindestriche und Unterstriche enthalten (maximal 64 Zeichen).');
        }

        self::writeOption('settings', [
            'cookie_name' => $cookieName,
            'privacy_btn' => [
                'url' => self::sanitizeUrlSetting($settings, 'privacy_btn', 'Datenschutzerklärung'),
            ],
            'imprint_btn' => [
                'url' => self::sanitizeUrlSetting($settings, 'imprint_btn', 'Impressum'),
            ],
        ]);
    }

    /**
     * Validates and sanitizes the `url` of one of the footer button settings.
     * esc_url_raw() empties out unsafe schemes such as javascript:, which is
     * what makes an empty result a rejection rather than a fallback.
     *
     * @param array<string, mixed> $settings
     */
    private static function sanitizeUrlSetting(array $settings, string $key, string $label): string
    {
        $url = $settings[$key]['url'] ?? null;
        if (!\is_string($url) || \trim($url) === '') {
            throw new \InvalidArgumentException(\sprintf('Die URL der %s ist erforderlich.', $label));
        }

        $url = esc_url_raw(\trim($url), ['http', 'https']);
        if ($url === '') {
            throw new \InvalidArgumentException(\sprintf('Die URL der %s ist ungültig.', $label));
        }

        return $url;
    }

    // ---------------------------------------------------------------------
    // Modal texts
    // ---------------------------------------------------------------------

    /**
     * Overlays the saved texts of one modal onto its defaults.
     *
     * @param array<string, array{label: string, max: int, multiline?: bool}> $fields
     * @param array<string, string> $texts
     */
    private function loadModalTexts(string $optionName, array $fields, array &$texts): void
    {
        $saved = self::readOption($optionName);
        if ($saved === null) {
            return;
        }

        foreach ($fields as $key => $field) {
            if (!isset($saved[$key]) || !\is_string($saved[$key])) {
                continue;
            }

            // The consent script renders these texts as HTML, so they are
            // stripped of markup again on the way out.
            $value = self::sanitizeText($saved[$key], $field);
            if ($value !== '') {
                $texts[$key] = $value;
            }
        }
    }

    /** @param array<string, mixed> $config */
    public static function saveConsentModalConfig(array $config): void
    {
        self::writeOption('consent_modal_config', self::validateTexts($config, self::CONSENT_MODAL_FIELDS));
    }

    /** @param array<string, mixed> $config */
    public static function savePreferenceModalConfig(array $config): void
    {
        self::writeOption('preference_modal_config', self::validateTexts($config, self::PREFERENCES_MODAL_FIELDS));
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, array{label: string, max: int, multiline?: bool}> $fields
     * @return array<string, string>
     */
    private static function validateTexts(array $input, array $fields): array
    {
        $clean = [];
        foreach ($fields as $key => $field) {
            $value = \is_string($input[$key] ?? null) ? self::sanitizeText($input[$key], $field) : '';

            if ($value === '') {
                throw new \InvalidArgumentException(\sprintf('%s ist erforderlich.', $field['label']));
            }
            if (\mb_strlen($value) > $field['max']) {
                throw new \InvalidArgumentException(\sprintf('%s darf maximal %d Zeichen lang sein.', $field['label'], $field['max']));
            }

            $clean[$key] = $value;
        }

        return $clean;
    }

    /** @param array{label: string, max: int, multiline?: bool} $field */
    private static function sanitizeText(string $value, array $field): string
    {
        return empty($field['multiline']) ? sanitize_text_field($value) : sanitize_textarea_field($value);
    }

    // ---------------------------------------------------------------------
    // GUI options
    // ---------------------------------------------------------------------

    private function loadGuiOptions(): void
    {
        $saved = self::readOption('gui_settings');
        if ($saved === null) {
            return;
        }

        foreach (self::GUI_CHOICES as $modal => $options) {
            foreach ($options as $option => $choices) {
                $value = $saved[$modal][$option] ?? null;
                if (\in_array($value, $choices, true)) {
                    $this->guiOptions[$modal][$option] = $value;
                }
            }
        }
    }

    /**
     * Accepts a partial payload: options that are not part of it keep their
     * stored value.
     *
     * @param array<string, mixed> $settings
     */
    public static function saveGUISettings(array $settings): void
    {
        $clean = self::readOption('gui_settings') ?? [];

        foreach (self::GUI_CHOICES as $modal => $options) {
            foreach ($options as $option => $choices) {
                if (!isset($settings[$modal][$option])) {
                    continue;
                }

                $value = $settings[$modal][$option];
                if (!\in_array($value, $choices, true)) {
                    throw new \InvalidArgumentException(\sprintf(
                        '%s %s ist ungültig. Erlaubte Werte sind: %s.',
                        self::GUI_LABELS[$option],
                        self::GUI_LABELS[$modal],
                        \implode(', ', $choices)
                    ));
                }

                $clean[$modal][$option] = $value;
            }
        }

        self::writeOption('gui_settings', $clean);
    }

    // ---------------------------------------------------------------------
    // Custom styles
    // ---------------------------------------------------------------------

    private function loadCustomStyles(): void
    {
        $saved = self::readOption('custom_styles');
        if ($saved === null) {
            return;
        }

        foreach (\array_keys(self::CUSTOM_STYLES) as $property) {
            $value = $saved[$property] ?? null;
            if (\is_string($value) && self::isValidStyleValue($property, $value)) {
                $this->customStyles[$property] = $value;
            }
        }
    }

    /**
     * Stores exactly the given properties; the ones left out fall back to
     * their defaults.
     *
     * @param array<array-key, mixed> $styles
     */
    public static function saveCustomStyles(array $styles): void
    {
        $unknown = \array_key_first(\array_diff_key($styles, self::CUSTOM_STYLES));
        if ($unknown !== null) {
            throw new \InvalidArgumentException(\sprintf('Die CSS-Eigenschaft "%s" ist unbekannt.', sanitize_text_field((string) $unknown)));
        }

        $clean = [];
        foreach (\array_keys(self::CUSTOM_STYLES) as $property) {
            if (!\array_key_exists($property, $styles)) {
                continue;
            }

            $value = $styles[$property];
            if (!\is_string($value) || !self::isValidStyleValue($property, $value)) {
                throw new \InvalidArgumentException(\sprintf(
                    \in_array($property, self::SIZE_KEYS, true)
                        ? 'Der Wert für "%s" ist ungültig. Erwartet wird eine Pixel-Angabe, z.B. 8px.'
                        : 'Der Wert für "%s" ist ungültig. Erwartet wird eine Farbe, z.B. #ffffff oder rgba(0, 0, 0, 0.65).',
                    $property
                ));
            }

            $clean[$property] = $value;
        }

        self::writeOption('custom_styles', $clean);
    }

    private static function isValidStyleValue(string $property, string $value): bool
    {
        $pattern = \in_array($property, self::SIZE_KEYS, true) ? self::SIZE_PATTERN : self::COLOR_PATTERN;

        return \preg_match($pattern, $value) === 1;
    }

    // ---------------------------------------------------------------------
    // Storage
    // ---------------------------------------------------------------------

    /**
     * Reads a plugin option. Versions before 0.4.0 stored JSON strings; those
     * are still understood until migrateLegacyOptions() has converted them.
     *
     * @return array<array-key, mixed>|null Null if the option is missing or unusable.
     */
    private static function readOption(string $name): ?array
    {
        $saved = get_option(self::OPTION_PREFIX . $name);
        if (\is_string($saved) && $saved !== '') {
            $saved = \json_decode($saved, true);
        }

        return \is_array($saved) ? $saved : null;
    }

    /** @param array<array-key, mixed> $value */
    private static function writeOption(string $name, array $value): void
    {
        update_option(self::OPTION_PREFIX . $name, $value);
    }

    /** Converts options stored as JSON strings (before 0.4.0) to arrays. */
    public static function migrateLegacyOptions(): void
    {
        foreach (self::OPTION_NAMES as $name) {
            if (!\is_string(get_option(self::OPTION_PREFIX . $name))) {
                continue;
            }

            $decoded = self::readOption($name);
            if ($decoded !== null) {
                self::writeOption($name, $decoded);
            }
        }
    }
}
