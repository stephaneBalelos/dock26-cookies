import { createGlobalState } from "@vueuse/core";
import { ref, inject, nextTick } from "vue";
import type { CookieConfig } from "../../../types";
import { d26CookieConsentKey } from "@/plugins/CookieConsentVue";

export const useConsentConfig = createGlobalState(() => {
  const isLoading = ref(true);

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
    init,
    showConsentModal,
    showPreferencesModal,
    consentModalLayoutOptions
  };
});
