export type CookieConfig = {
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

export type ConsentCategory = {
  id: number;
  slug: string;
  name: string;
  description: string;
};
