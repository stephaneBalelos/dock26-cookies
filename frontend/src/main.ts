import * as CookieConsent from "vanilla-cookieconsent";
import "vanilla-cookieconsent/dist/cookieconsent.css";
import "@orestbida/iframemanager/dist/iframemanager.css";
import { type IframeManagerInstance } from "@orestbida/iframemanager";
import type { CookieConfig } from "../../types";

/**
 * All config. options available here:
 * https://cookieconsent.orestbida.com/reference/configuration-reference.html
 */

declare global {
  interface Window {
    iframemanager: () => IframeManagerInstance;
    dock26Cookies: {
      config: CookieConfig;
    }
    CookieConsent: typeof CookieConsent;
  }
}

window.CookieConsent = CookieConsent;
const im = window.iframemanager();

document.addEventListener("DOMContentLoaded", async () => {
  console.log(
    "Initializing Cookie Consent with categories:",
    window.dock26Cookies,
  );

  im.run({
    currLang: "de",
    onChange: ({ changedServices, eventSource }) => {
      if (eventSource.type === "click") {
        console.log("Services changed via click:", changedServices);
        const servicesToAccept = [
          ...CookieConsent.getUserPreferences().acceptedServices["external_media"] || [],
          ...changedServices,
        ];

        CookieConsent.acceptService(servicesToAccept, "external_media");
      }
    },

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

  const translations = window.dock26Cookies.config.language.translations;
  const guiOptions = window.dock26Cookies.config.guiOptions

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

    categories: {
      ...window.dock26Cookies.config.categories,
    },

    // https://cookieconsent.orestbida.com/reference/configuration-reference.html#guioptions
    guiOptions: guiOptions,

    language: {
      default: "de",
      translations: translations,
    },

    onFirstConsent: ({ cookie }) => {
      console.log("First consent given:", cookie);
    },

    onConsent: ({ cookie }) => {
      console.log("Consent updated:", cookie);
    },

    onChange: ({ cookie, changedServices, changedCategories }) => {
      console.log("Consent changed:", cookie, changedServices, changedCategories);
      updateIframeAcceptance(cookie, changedServices);
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
  });

  function updateIframeAcceptance(
    cookie: CookieConsent.CookieValue,
    changedServicesCategories: { [key: string]: string[] },
  ) {
    console.log(Object.keys(changedServicesCategories));
    const changedServices: string[] = [];
    Object.keys(changedServicesCategories).forEach((category) => {
      const services = changedServicesCategories[category];
      console.log(`Category "${category}" changed. Services:`, services);
      changedServices.push(...services);
    });
    // Destructure the cookie object to get accepted services
    const acceptedServices = Object.values(cookie.services).flat();
    for (const service of changedServices) {
      if (acceptedServices.includes(service)) {
        im.acceptService(service);
      } else {
        im.rejectService(service);
      }
    }
  }

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
