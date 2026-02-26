import * as CookieConsent from "vanilla-cookieconsent";
import "vanilla-cookieconsent/dist/cookieconsent.css";

/**
 * All config. options available here:
 * https://cookieconsent.orestbida.com/reference/configuration-reference.html
 */

type Dock26CookieConsentCategories = {
  id: string;
  name: string;
  description: string;
  enabled: boolean;
  readOnly: boolean;
};

type Dock26CookieConsentSettings = {
  [key: string]: any;
};

declare global {
  interface Window {
    dock26Cookies: {
      settings: Dock26CookieConsentSettings;
      categories: Dock26CookieConsentCategories[];
    };
    CookieConsent: typeof CookieConsent;
  }
}

window.CookieConsent = CookieConsent;

document.addEventListener("DOMContentLoaded", async () => {
  console.log(
    "Initializing Cookie Consent with categories:",
    window.dock26Cookies,
  );
  if (window.dock26Cookies.categories.length === 0) {
    console.warn("No consent categories found.");
  }

  const categories: CookieConsent.CookieConsentConfig["categories"] = {};
  const sections: CookieConsent.Section[] = [];
  const settings = window.dock26Cookies.settings;

  window.dock26Cookies.categories.forEach((category) => {
    const categoryId = category.id.toString();
    categories[categoryId] = {
      enabled: category.enabled,
      readOnly: category.readOnly,
    };
    sections.push({
      title: category.name,
      description: category.description,
      linkedCategory:
        category.enabled && category.readOnly ? undefined : categoryId,
    });
  });

  await CookieConsent.run({
    // root: "body",
    // autoShow: true,
    disablePageInteraction: true,
    // hideFromBots: true,
    // mode: 'opt-in',
    // revision: 0,

    cookie: {
      name: "cc_cookie",
      // domain: location.hostname,
      // path: '/',
      // sameSite: "Lax",
      // expiresAfterDays: 182,
    },

    // https://cookieconsent.orestbida.com/reference/configuration-reference.html#guioptions
    guiOptions: {
      consentModal: {
        layout: "cloud inline",
        position: "bottom center",
        equalWeightButtons: true,
        flipButtons: false,
      },
      preferencesModal: {
        layout: "box",
        equalWeightButtons: true,
        flipButtons: false,
      },
    },

    onFirstConsent: ({ cookie }) => {
      console.log("First consent given:", cookie);
    },

    onConsent: ({ cookie }) => {
      // handleConsent(cookie);
      console.log("Consent updated:", cookie);
    },

    onChange: ({ changedCategories, changedServices }) => {
      handleConsentChange(changedCategories, changedServices);
    },

    onModalReady: ({ modalName }) => {
      console.log("ready:", modalName);
    },

    onModalShow: ({ modalName }) => {
      console.log("visible:", modalName);
    },

    onModalHide: ({ modalName }) => {
      console.log("hidden:", modalName);
    },

    categories: categories,

    language: {
      default: "de",
      translations: {
        de: {
          consentModal: {
            title: settings.consent_modal_title || "Cookie-Zustimmung",
            description:
              settings.consent_modal_description || "Wir verwenden Cookies",
            acceptAllBtn:
              settings.consent_modal_accept_all_btn || "Alle akzeptieren",
            acceptNecessaryBtn:
              settings.consent_modal_accept_necessary_btn ||
              "Nur notwendige akzeptieren",
            showPreferencesBtn:
              settings.consent_modal_show_preferences_btn ||
              "Individuelle Präferenzen verwalten",
            // closeIconLabel: 'Reject all and close modal',
            footer: `
                        <a href="${settings.imprint_link}" target="_blank">Impressum</a>
                        <a href="${settings.privacy_policy_link}" target="_blank">Datenschutzerklärung</a>
                    `,
          },
          preferencesModal: {
            title: settings.preferences_modal_title || "Cookie-Einstellungen",
            acceptAllBtn:
              settings.preferences_modal_accept_all_btn || "Alle akzeptieren",
            acceptNecessaryBtn:
              settings.preferences_modal_accept_necessary_btn ||
              "Nur notwendige akzeptieren",
            savePreferencesBtn:
              settings.preferences_modal_save_preferences_btn ||
              "Aktuelle Auswahl akzeptieren",
            closeIconLabel:
              settings.preferences_modal_close_icon_label || "Modal schließen",
            serviceCounterLabel:
              settings.preferences_modal_service_counter_label ||
              "Dienst|Dienste",
            sections: [...sections],
          },
        },
      },
    },
  });

  function handleConsentChange(
    changedCategories: string[],
    _changedServices: {
      [key: string]: string[];
    },
  ) {
    const cookieValue = window.CookieConsent.getCookie();
    // Check for each changed categories if the Consent ist given or revoked
    // If a consent has been removed, reload the page
    let shouldReload = false;
    changedCategories.forEach((category) => {
      if (!cookieValue.categories.includes(category)) {
        shouldReload = true;
      }
    });

    if (shouldReload) {
      window.location.reload();
    }
  }

  // Init Triggers Buttons
  const initTriggers = () => {
    const button = document.getElementById("dock-26-cookies-trigger-cc");
    if (!button) {
      return;
    }
    button.addEventListener("click", () => {
      CookieConsent.showPreferences();
    });
  };

  initTriggers();
});
