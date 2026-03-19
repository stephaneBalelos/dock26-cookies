import * as CookieConsent from "vanilla-cookieconsent";
import "vanilla-cookieconsent/dist/cookieconsent.css";
import "@orestbida/iframemanager/dist/iframemanager.css";
import { type IframeManagerInstance } from "@orestbida/iframemanager";
import type { ConsentCategory } from "../../types";
import type { Category } from "vanilla-cookieconsent";

/**
 * All config. options available here:
 * https://cookieconsent.orestbida.com/reference/configuration-reference.html
 */

type Dock26CookieConsentSettings = {
  [key: string]: any;
};

declare global {
  interface Window {
    iframemanager: () => IframeManagerInstance;
    dock26Cookies: {
      config: {
        settings: Dock26CookieConsentSettings;
        categories: ConsentCategory[];
      };
    };
    CookieConsent: typeof CookieConsent;
  }
}

window.CookieConsent = CookieConsent;
const im = window.iframemanager();

console.log(im);

document.addEventListener("DOMContentLoaded", async () => {
  console.log(
    "Initializing Cookie Consent with categories:",
    window.dock26Cookies,
  );
  if (window.dock26Cookies.config.categories.length === 0) {
    console.warn("No consent categories found.");
  }

  im.run({
    currLang: "de",

    services: {
      youtube: {
        embedUrl: "https://www.youtube-nocookie.com/embed/{data-id}",
        thumbnailUrl: "https://i3.ytimg.com/vi/{data-id}/hqdefault.jpg",

        iframe: {
          allow:
            "accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;",
        },

        languages: {
          de: {
            loadBtn: "Accept",
            notice:
              'This content is hosted by a third party. By showing the external content you accept the <a rel="noreferrer noopener" href="https://www.youtube.com/t/terms" target="_blank">terms and conditions</a> of youtube.com.',
            loadAllBtn: "Accept and Load",
          },
        },
      },
    },
  });

  const categories = window.dock26Cookies.config.categories.reduce(
    (acc, category) => {
      acc[category.slug] = {
        autoClear: {
          cookies: [],
          reloadPage: true,
        },
      };
      return acc;
    },
    {} as Record<string, Category>,
  );
  categories["external_medias"] = {
    services: {
      youtube: {
        label: "Youtube Embed",
        onAccept: () => im.acceptService("youtube"),
        onReject: () => im.rejectService("youtube"),
      },
      vimeo: {
        label: "Vimeo Embed",
        onAccept: () => im.acceptService("vimeo"),
        onReject: () => im.rejectService("vimeo"),
      },
    },
  };
  const sections: CookieConsent.Section[] =
    window.dock26Cookies.config.categories.map((cat) => {
      return {
        title: cat.name,
        description: cat.description,
        linkedCategory: cat.slug,
      };
    });

  sections.push({
    title: "External Medias",
    description: "External",
    linkedCategory: "external_medias",
  });

  const settings = window.dock26Cookies.config.settings;

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

    categories: categories,

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
      console.log(changedCategories, changedServices);
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

  // Init Triggers Buttons
  const initTriggers = () => {
    const buttonConsent = document.getElementById("dock26-cookies-consent");
    const buttonPreferences = document.getElementById(
      "dock26-cookies-preferences",
    );
    if (buttonConsent) {
      buttonConsent.addEventListener("click", () => {
        CookieConsent.show(true);
      });
    }

    if (buttonPreferences) {
      buttonPreferences.addEventListener("click", () => {
        CookieConsent.showPreferences();
      });
    }
  };

  initTriggers();
});
