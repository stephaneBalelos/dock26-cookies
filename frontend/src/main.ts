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
import type { CookieConfig } from "../../types";

/**
 * All config. options available here:
 * https://cookieconsent.orestbida.com/reference/configuration-reference.html
 */

declare global {
  interface Window {
    iframemanager: () => IframeManagerInstance;
    // Printed by Main::enqueue_assets() right before this bundle.
    dock26Cookies: {
      config: CookieConfig;
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
    // Deliberately no thumbnailUrl: the preview image would be fetched from
    // Google's servers before the visitor has consented. A first-party
    // preview can be set per embed (shortcode attribute "thumbnail").

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
  // Any iframe of a provider without a definition above. The server puts
  // the full iframe URL into data-id (Embeds::gate_iframes()).
  iframe: {
    embedUrl: "{data-id}",

    languages: {
      de: {
        loadBtn: "Inhalt laden",
        // Empty: no "always load" button for unidentified providers.
        loadAllBtn: "",
        notice:
          "Dieser Inhalt wird von einem Drittanbieter gehostet. Beim Laden des Inhalts werden Daten an den Anbieter übertragen.",
      },
    },
  },
};

// A generic placeholder loads whatever URL it carries, so anything that is
// not a web address (javascript:, data:) loses its placeholder role.
function dropUnsafeGenericPlaceholders() {
  for (const div of document.querySelectorAll<HTMLElement>(
    'div[data-service="iframe"]',
  )) {
    let protocol = "";
    try {
      protocol = new URL(div.dataset.id ?? "").protocol;
    } catch {
      // Not a URL at all.
    }
    if (protocol !== "https:" && protocol !== "http:") {
      div.removeAttribute("data-service");
    }
  }
}

window.CookieConsent = CookieConsent;
const im = window.iframemanager();

async function init() {
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

  dropUnsafeGenericPlaceholders();

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

    // Fires on the first consent and on every later page load: bring all
    // placeholders in line with what the visitor has accepted.
    onConsent: ({ cookie }) => {
      syncIframes(cookie, [...serviceCategories.keys()]);
    },

    onChange: ({ cookie, changedServices }) => {
      syncIframes(cookie, Object.values(changedServices).flat());
    },
  });

  function syncIframes(cookie: CookieConsent.CookieValue, services: string[]) {
    const accepted = Object.values(cookie.services).flat();
    for (const service of services) {
      if (accepted.includes(service)) {
        im.acceptService(service);
      } else {
        im.rejectService(service);
      }
    }
  }

  // Any element with one of these ids reopens the banner or the preferences.
  // Delegated, so it also works for elements added after page load.
  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Element)) {
      return;
    }

    if (event.target.closest("#dock26-cookies-consent")) {
      CookieConsent.show(true);
    } else if (event.target.closest("#dock26-cookies-preferences")) {
      CookieConsent.showPreferences();
    }
  });
}

// The bundle is normally printed in the footer, but an optimization plugin may
// defer it past DOMContentLoaded — the banner must still come up then.
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}
