export type CookieConfig = {
  settings: {
    cookie_name: string;
    default_language: string;
    privacy_btn: { label: string; url: string };
    imprint_btn: { label: string; url: string };
  };
  guiOptions?: {
    consentModal: {
      layout: "box" | "bar" | "cloud";
      position: 'top left' | 'top center' | 'top right' | 'middle left' | 'middle center' | 'middle right' | 'bottom left' | 'bottom center' | 'bottom right';
    };
    preferencesModal: {
      layout: "box" | "bar";
      position: 'left' | 'right';
    };
  };
  categories: {
    [category: string]: {
      enabled?: boolean;
      readOnly?: boolean;
      autoClear?: CookieConsent.AutoClear;
      services?: {
        [service: string]: {
          label: string;
        };
      };
    };
  };
  language: {
    default: string;
    translations: {
      [locale: string]: CookieConsent.Translation;
    };
  };
};

/**
 * CSS custom properties of the consent modal, keyed by variable name (e.g. "--cc-bg").
 * Kept separate from CookieConfig: that object is spread into cookieConsent.run(),
 * which must not receive these.
 */
export type CustomStyles = Record<string, string>;

/**
 * The admin-editable subset of CookieConfig["settings"]. The button labels and
 * default_language are omitted deliberately: they are never persisted and keep
 * their PHP defaults.
 */
export type GlobalSettingsConfig = {
  cookie_name: string;
  privacy_btn: { url: string };
  imprint_btn: { url: string };
};

export type ConsentModalConfig = {
  title?: string;
  description?: string;
  acceptAllBtn?: string;
  acceptNecessaryBtn?: string;
  showPreferencesBtn?: string;
};

export type PreferencesModalConfig = {
  title?: string;
  acceptAllBtn?: string;
  acceptNecessaryBtn?: string;
  savePreferencesBtn?: string;
  closeIconLabel?: string;
};
