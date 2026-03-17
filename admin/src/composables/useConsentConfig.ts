import { createGlobalState } from "@vueuse/core";
import { ref, computed, inject, nextTick } from "vue";
import type { ConsentCategory } from "../../../types";
import type {
  GuiOptions,
  Category,
  CookieConsentConfig,
} from "vanilla-cookieconsent";
import { d26CookieConsentKey } from "@/plugins/CookieConsentVue";

type UseConsentConfigInit = {
  categories: ConsentCategory[];
};

export const useConsentConfig = createGlobalState(() => {
  const isLoading = ref(true);

  const consentCategories = ref<ConsentCategory[]>([]);
  const guiOptions = ref<GuiOptions>({
    consentModal: {
      layout: "bar",
      position: "top left",
    },
  });

  const cookieConsent = inject(d26CookieConsentKey);

  const config = computed<CookieConsentConfig | null>(() => {
    const categories = consentCategories.value;
    if (categories.length == 0) return null;
    return {
      categories: categories.reduce(
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
      ),
      language: {
        default: "en",
        translations: {
          en: {
            consentModal: {
              label: "Cookie Consent Label",
              title: "Cookie Consent Title",
              description: "Description",
              acceptAllBtn: "Alles Akzeptieren",
              acceptNecessaryBtn: "Nur notwendige",
              showPreferencesBtn: "Preferenzen anpassen",
              closeIconLabel: "Schließen",
              revisionMessage: "revision",
              footer: "footer",
            },
            preferencesModal: {
              title: "Preferences Title",
              acceptAllBtn: "Alles Akzeptieren",
              acceptNecessaryBtn: "Nur notwendige",
              savePreferencesBtn: "Speichern",
              closeIconLabel: "Schließen",
              serviceCounterLabel: "Service COunter Label",
              sections: categories.map((cat) => {
                return {
                  title: cat.name,
                  description: cat.description,
                  linkedCategory: cat.slug,
                };
              }),
            },
          },
        },
      },
      guiOptions: guiOptions.value,
    };
  });

  function init(init: UseConsentConfigInit) {
    consentCategories.value = init.categories;
    nextTick(() => {
      console.log("nextTick");
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
  return {
    isLoading,
    config,
    consentCategories,
    guiOptions,
    init,
    showConsentModal,
  };
});
