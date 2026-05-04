import { createGlobalState } from "@vueuse/core";
import { ref, inject, nextTick } from "vue";
import type { CookieConfig } from "../../../types";
import { d26CookieConsentKey } from "@/plugins/CookieConsentVue";
import { useClient } from "./client";

export const useConsentConfig = createGlobalState(() => {

  const client = useClient()
  const isLoading = ref(false);

  const consentModalLayoutOptions = {
    layout: {
      base: [
        { label: 'Box', value: 'box' },
        { label: 'Bar', value: 'bar' },
        { label: 'Cloud', value: 'cloud' },
      ],
      variant: [
        { label: 'Wide', value: 'wide' },
        { label: 'Inline', value: 'inline' },
      ],
    },
    position: {
      x: [
        { label: 'Left', value: 'left' },
        { label: 'Center', value: 'center' },
        { label: 'Right', value: 'right' },
      ],
      y: [
        { label: 'Top', value: 'top' },
        { label: 'Middle', value: 'middle' },
        { label: 'Bottom', value: 'bottom' },
      ],
    }
  };


  const cookieConsent = inject(d26CookieConsentKey);

  const config = ref<CookieConfig | null>(null);

  function init(init: CookieConfig) {
    config.value = init
    nextTick(() => {
      if (config.value && cookieConsent) {
        cookieConsent.run({
          ...config.value
        });
      }
    });
  }

  async function reloadConfig() {
    try {
      isLoading.value = true
      closeAllModal()
      const c = await client.getConfig()
      init(c)
    } catch (error) {
      console.log(error)
    } finally {
      isLoading.value = false
    }
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

  function closeAllModal() {
    if(cookieConsent && config.value) {
      cookieConsent.hide()
      cookieConsent.hidePreferences()
    }
  }
  return {
    isLoading,
    config,
    init,
    reloadConfig,
    showConsentModal,
    showPreferencesModal,
    consentModalLayoutOptions
  };
});
