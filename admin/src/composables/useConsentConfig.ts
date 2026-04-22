import { createGlobalState } from "@vueuse/core";
import { ref, inject, nextTick } from "vue";
import type { ConsentCategory, CookieConfig } from "../../../types";
import type {
  GuiOptions,
  CookieConsentConfig,
} from "vanilla-cookieconsent";
import { d26CookieConsentKey } from "@/plugins/CookieConsentVue";

export const useConsentConfig = createGlobalState(() => {
  const isLoading = ref(true);

  const consentCategories = ref<ConsentCategory[]>([]);
  const guiOptions = ref<GuiOptions>({
    consentModal: {
      layout: "cloud",
      position: "bottom center"
    },
    preferencesModal: {
      layout: "box",
      position: "left",
    },
  });

  const cookieConsent = inject(d26CookieConsentKey);

  const config = ref<CookieConsentConfig | null>(null);

  function init(init: CookieConfig) {
    console.log("init config", init);
    config.value = init
    nextTick(() => {
      if (config.value && cookieConsent) {
        cookieConsent.run(config.value);
      }
    });
  }

  function showConsentModal() {
    if (cookieConsent && config.value) {
      cookieConsent.hide();
      cookieConsent.reset();
      cookieConsent.run(config.value);
      cookieConsent.show(true);
    }
  }

  function showPreferencesModal() {
    if (cookieConsent && config.value) {
      cookieConsent.getUserPreferences();
      cookieConsent.reset();
      cookieConsent.run(config.value);
      cookieConsent.showPreferences();
    }
  }
  return {
    isLoading,
    config,
    consentCategories,
    guiOptions,
    init,
    showConsentModal,
    showPreferencesModal,
  };
});
