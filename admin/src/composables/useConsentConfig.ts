import { createGlobalState } from "@vueuse/core";
import { ref, nextTick } from "vue";
import * as cookieConsent from "vanilla-cookieconsent";
import type { AdminData, CookieConfig, CustomStyles } from "../../../types";
import { customStylesCss } from "../styleFields";

// The preview stores its consent under its own name, so trying out the modals
// here doesn't overwrite the consent the admin gave as a visitor of the site.
export const PREVIEW_COOKIE_NAME = "dock26_cookies_preview";

const CUSTOM_STYLES_ELEMENT_ID = "dock26-cookies-custom-styles";

/** Gives the quick previews in the admin page the saved custom styles. */
function applyCustomStyles(styles: CustomStyles) {
  let element = document.getElementById(CUSTOM_STYLES_ELEMENT_ID);
  if (!element) {
    element = document.createElement("style");
    element.id = CUSTOM_STYLES_ELEMENT_ID;
    document.head.append(element);
  }
  element.textContent = customStylesCss(styles);
}

/** Only the keys cookieconsent understands; `settings` is plugin-specific. */
function previewOptions(config: CookieConfig): cookieConsent.CookieConsentConfig {
  return {
    cookie: { name: PREVIEW_COOKIE_NAME },
    // Modals open only through the preview buttons, never on page load.
    autoShow: false,
    categories: config.categories,
    guiOptions: config.guiOptions,
    language: config.language,
  };
}

/** The saved configuration plus the live preview of the modals. */
export const useConsentConfig = createGlobalState(() => {
  const isLoading = ref(false);

  const config = ref<CookieConfig | null>(null);
  const customStyles = ref<CustomStyles | null>(null);

  function init(data: AdminData) {
    apply(data);
    nextTick(() => {
      if (config.value) {
        cookieConsent.run(previewOptions(config.value));
      }
    });
  }

  /** Takes over the state the server returned after a save. */
  function apply(data: AdminData) {
    cookieConsent.hide();
    cookieConsent.hidePreferences();
    config.value = data.config;
    customStyles.value = data.customStyles;
    applyCustomStyles(data.customStyles);
  }

  function showConsentModal() {
    if (config.value) {
      cookieConsent.hide();
      cookieConsent.reset();
      cookieConsent.run(previewOptions(config.value));
      cookieConsent.show(true);
    }
  }

  function showPreferencesModal() {
    if (config.value) {
      cookieConsent.reset();
      cookieConsent.run(previewOptions(config.value));
      cookieConsent.showPreferences();
    }
  }

  return {
    isLoading,
    config,
    customStyles,
    init,
    apply,
    showConsentModal,
    showPreferencesModal,
  };
});
