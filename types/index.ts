// Shared between the admin app and the frontend bundle. This folder has no
// node_modules of its own, so the vanilla-cookieconsent types are referenced
// through the package's global `CookieConsent` namespace instead of an import.

/** The configuration built by CookieConfig::getConfig() (includes/cookie-config.php). */
export type CookieConfig = {
  settings: {
    cookie_name: string;
    default_language: string;
    privacy_btn: { label: string; url: string };
    imprint_btn: { label: string; url: string };
  };
  guiOptions: {
    consentModal: {
      layout: CookieConsent.ConsentModalLayout;
      position: CookieConsent.ConsentModalPosition;
    };
    preferencesModal: {
      layout: CookieConsent.PreferencesModalLayout;
      position: CookieConsent.PreferencesModalPosition;
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
 * On the frontend they are printed server-side as an inline stylesheet.
 */
export type CustomStyles = Record<string, string>;

/** CookieConfig::toAdminData(): the admin app's state, also returned by every save. */
export type AdminData = {
  config: CookieConfig;
  customStyles: CustomStyles;
};

/** URL segment of POST /settings/<section> (APIController::SECTIONS). */
export type SettingsSection =
  | "global"
  | "consent-modal"
  | "preferences-modal"
  | "gui"
  | "custom-styles";

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
