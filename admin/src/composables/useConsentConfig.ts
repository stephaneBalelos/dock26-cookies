import { createGlobalState } from "@vueuse/core";
import { ref, nextTick } from "vue";
import * as cookieConsent from "vanilla-cookieconsent";
import type { CookieConfig } from "../../../types";
import { useClient } from "./client";

export const useConsentConfig = createGlobalState(() => {
  const client = useClient();
  const isLoading = ref(false);

  const config = ref<CookieConfig | null>(null);

  function init(initial: CookieConfig) {
    config.value = initial;
    nextTick(() => {
      if (config.value) {
        cookieConsent.run({
          ...config.value,
        });
      }
    });
  }

  async function reloadConfig() {
    try {
      isLoading.value = true;
      closeAllModal();
      const c = await client.getConfig();
      init(c);
    } catch (error) {
      console.error("Fehler beim Neuladen der Konfiguration", error);
    } finally {
      isLoading.value = false;
    }
  }

  function showConsentModal() {
    if (config.value) {
      cookieConsent.hide();
      cookieConsent.reset();
      cookieConsent.run(config.value);
      cookieConsent.show(true);
    }
  }

  function showPreferencesModal() {
    if (config.value) {
      cookieConsent.reset();
      cookieConsent.run(config.value);
      cookieConsent.showPreferences();
    }
  }

  function closeAllModal() {
    cookieConsent.hide();
    cookieConsent.hidePreferences();
  }

  return {
    isLoading,
    config,
    init,
    reloadConfig,
    showConsentModal,
    showPreferencesModal,
  };
});
