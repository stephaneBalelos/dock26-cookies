import * as CookieConsent from "vanilla-cookieconsent";
import "vanilla-cookieconsent/dist/cookieconsent.css";
// Side-effect import: the iframemanager dist file is an IIFE that registers
// window.iframemanager — without it the bundle has no runtime and
// window.iframemanager() below throws.
import "@orestbida/iframemanager";
import "@orestbida/iframemanager/dist/iframemanager.css";
import {
  type IframeManagerInstance,
  type IframeManagerRunOptions,
} from "@orestbida/iframemanager";
import type { CookieConfig, CustomStyles } from "../../types";

/**
 * All config. options available here:
 * https://cookieconsent.orestbida.com/reference/configuration-reference.html
 */

declare global {
  interface Window {
    iframemanager: () => IframeManagerInstance;
    dock26Cookies: {
      config: CookieConfig;
      customStyles?: CustomStyles;
    };
    CookieConsent: typeof CookieConsent;
  }
}

type ServiceDefinition = IframeManagerRunOptions["services"][string];

// Embed/notice details for every service this plugin can consent-gate.
// Which of them are active — and which consent category they belong to —
// comes from the server config (CookieConfig::$services).
const SERVICE_DEFINITIONS: Record<string, ServiceDefinition> = {
  youtube: {
    embedUrl: "https://www.youtube-nocookie.com/embed/{data-id}",
    thumbnailUrl: "https://i3.ytimg.com/vi/{data-id}/hqdefault.jpg",

    iframe: {
      allow:
        "accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen;",
    },

    languages: {
      de: {
        loadBtn: "Video laden",
        loadAllBtn: "Akzeptieren und laden",
        notice:
          'Dieser Inhalt wird von einem Drittanbieter gehostet. Wenn Sie den externen Inhalt anzeigen, akzeptieren Sie die <a rel="noreferrer noopener" href="https://www.youtube.com/t/terms" target="_blank">Nutzungsbedingungen</a> von youtube.com.',
      },
    },
  },
  googlemaps: {
    embedUrl: "https://www.google.com/maps/embed?pb={data-id}",

    iframe: {
      allow: "picture-in-picture; fullscreen;",
    },

    languages: {
      de: {
        loadBtn: "Karte laden",
        loadAllBtn: "Nicht mehr fragen",
        notice:
          'Dieser Inhalt wird von einem Drittanbieter gehostet. Wenn Sie den externen Inhalt anzeigen, akzeptieren Sie die <a rel="noreferrer noopener" href="https://cloud.google.com/maps-platform/terms" target="_blank">Nutzungsbedingungen</a> von Google Maps.',
      },
    },
  },
};

window.CookieConsent = CookieConsent;
const im = window.iframemanager();

document.addEventListener("DOMContentLoaded", async () => {
  const config = window.dock26Cookies.config;
  const defaultLanguage = config.language.default;

  // Service id -> consent category, taken from the server config.
  const serviceCategories = new Map<string, string>();
  for (const [category, categoryConfig] of Object.entries(config.categories)) {
    for (const service of Object.keys(categoryConfig.services ?? {})) {
      serviceCategories.set(service, category);
    }
  }

  const services = Object.fromEntries(
    Object.entries(SERVICE_DEFINITIONS).filter(([id]) =>
      serviceCategories.has(id),
    ),
  );

  im.run({
    currLang: defaultLanguage,
    onChange: ({ changedServices, eventSource }) => {
      if (eventSource.type !== "click") {
        return;
      }

      // Accepting via an iframe placeholder must not drop already-accepted
      // services of the same category, so merge instead of replacing.
      const changedByCategory = new Map<string, string[]>();
      for (const service of changedServices) {
        const category = serviceCategories.get(service);
        if (!category) {
          continue;
        }
        const changed = changedByCategory.get(category);
        if (changed) {
          changed.push(service);
        } else {
          changedByCategory.set(category, [service]);
        }
      }

      for (const [category, changed] of changedByCategory) {
        const accepted =
          CookieConsent.getUserPreferences().acceptedServices[category] || [];
        CookieConsent.acceptService([...accepted, ...changed], category);
      }
    },

    services,
  });

  await CookieConsent.run({
    disablePageInteraction: true,

    cookie: {
      name: config.settings.cookie_name,
    },

    categories: {
      ...config.categories,
    },

    // https://cookieconsent.orestbida.com/reference/configuration-reference.html#guioptions
    guiOptions: config.guiOptions,

    language: {
      default: defaultLanguage,
      translations: config.language.translations,
    },

    onChange: ({ cookie, changedServices }) => {
      updateIframeAcceptance(cookie, changedServices);
    },
  });

  function updateIframeAcceptance(
    cookie: CookieConsent.CookieValue,
    changedServicesCategories: { [key: string]: string[] },
  ) {
    const changedServices = Object.values(changedServicesCategories).flat();
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

  // Scoped to #cc-main rather than :root/body: the stylesheet declares some
  // variables (e.g. --cc-modal-margin) on #cc-main itself, which would win over
  // an inherited value. Appending late also beats the vendor rule on document order.
  const initCustomStyles = () => {
    const customStyles = window.dock26Cookies?.customStyles;
    if (!customStyles) {
      return;
    }

    const declarations = Object.entries(customStyles)
      .map(([property, value]) => `${property}:${value}`)
      .join(";");

    const style = document.createElement("style");
    style.id = "dock26-cookies-custom-styles";
    style.textContent = `#cc-main{${declarations}}`;
    document.head.appendChild(style);
  };

  initCustomStyles();

  initTriggers();
});
