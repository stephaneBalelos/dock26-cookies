export type CookieConfig = {
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

export type ConsentModalConfig = {
  locale: "de" | "en";
  title?: string;
  description?: string;
  acceptAllBtn?: string;
  acceptNecessaryBtn?: string;
  showPreferencesBtn?: string;
};

export type PreferencesModalConfig = {
  locale: "de" | "en";
  title?: string;
  acceptAllBtn?: string;
  acceptNecessaryBtn?: string;
  savePreferencesBtn?: string;
  closeIconLabel?: string;
};
