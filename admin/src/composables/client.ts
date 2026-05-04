import { computed, inject } from "vue";


export function useClient() {
  const apiUrl = inject("apiUrl", "");
  const nonce = inject("nonce", "");

  const requestHeader = computed(() => {
    return {
      "Content-Type": "application/json",
      "X-WP-Nonce": nonce,
    };
  });

  async function jsonQuery(
    url: string,
    method: string,
    data?: Record<string, any>,
  ) {
    const res = await fetch(url, {
      method: method,
      headers: requestHeader.value,
      body: data ? JSON.stringify(data) : null,
    });
    return await res.json();
  }

  async function getConfig() {
    return jsonQuery(`${apiUrl}/config`, "GET")
  }

  async function updateConsentModalConfig(config: Record<string, any>) {
    return jsonQuery(`${apiUrl}/update-consent-modal-config`, "PUT", { config });
  }

  async function updatePreferencesModalConfig(config: Record<string, any>) {
    return jsonQuery(`${apiUrl}/update-preference-modal-config`, "PUT", { config });
  }

  async function updateGuiOptions(config: Record<string, any>) {
    return jsonQuery(`${apiUrl}/update-gui-config`, "PUT", { config })
  }

  return {
    getConfig,
    updateConsentModalConfig,
    updatePreferencesModalConfig,
    updateGuiOptions,
  };
}
