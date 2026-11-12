import type { SettingsSection } from "../../../types";
import { useClient } from "./client";
import { useConsentConfig } from "./useConsentConfig";

/**
 * The save flow shared by all settings forms: send the section, report the
 * outcome as a toast and take over the configuration the server answers with.
 */
export function useSaveSection() {
  const client = useClient();
  const consentConfig = useConsentConfig();
  const toast = useToast();

  async function save(
    section: SettingsSection,
    config: object,
    successTitle: string,
  ) {
    consentConfig.isLoading.value = true;
    try {
      consentConfig.apply(await client.saveSection(section, config));
      toast.add({ title: successTitle, color: "success" });
    } catch (error) {
      toast.add({
        title: "Speichern fehlgeschlagen",
        description: error instanceof Error ? error.message : String(error),
        icon: "i-heroicons-exclamation-triangle",
        color: "error",
      });
    } finally {
      consentConfig.isLoading.value = false;
    }
  }

  return { save };
}
