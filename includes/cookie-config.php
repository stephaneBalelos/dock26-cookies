<?php

namespace Dock26Cookies;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CookieConfig
{

    private $settings = [
        'cookie_name' => 'd26Cookies',
        'privacy_btn' => [
            'label' => 'Datenschutzerklärung',
            'url' => '#'
        ],
        'imprint_btn' => [
            'label' => 'Impressum',
            'url' => '#'
        ]
    ];

    private $consentModal = [
        'title' => 'Wir Benutzen Cookies hier',
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
        // Initi
    }

    public function getConfig() {
        $settings = $this->settings;
        $consentConfig = $this->consentModal;
        $consentConfig['footer'] = '
            <a href="'. $settings['privacy_btn']['url'] .'" target="_blank">'. $settings['privacy_btn']['url'] .'</a>
            <a href="'. $settings['imprint_btn']['url'] .'" target="_blank">'. $settings['imprint_btn']['url'] .'</a>'
        ;

        $preferencesConfig = $this->preferencesModal;
        $preferencesConfig['sections'] = $this->prefrencesSections;


        $categories = $this->cookieCategories;
        foreach($categories as $key => $category) {
            $categories[$key]['services'] = [];
            foreach($this->services as $serviceKey => $service) {
                if($service['category'] === $key) {
                    $categories[$key]['services'][$serviceKey] = $service;
                }
            }
        }

        return [
            'settings' => $this->settings,
            'categories' => $categories,
            'language' => [
                'default' => 'de',
                'translations' => [
                    'de' => [
                        'consentModal' => $this->consentModal,
                        'preferencesModal' => $preferencesConfig
                    ]
                ]
            ]
        ];
    }
}
