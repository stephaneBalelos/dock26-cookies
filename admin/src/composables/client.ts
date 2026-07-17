// Injected by wp_localize_script before this bundle runs (includes/admin.php).
const adminData = window.dock26CookiesAdmin;

export function useClient() {
  const apiUrl = adminData?.apiUrl ?? "";

  const requestHeader = {
    "Content-Type": "application/json",
    "X-WP-Nonce": adminData?.nonce ?? "",
  };

  async function jsonQuery(
    url: string,
    method: string,
    data?: Record<string, any>,
  ) {
    const res = await fetch(url, {
      method: method,
      headers: requestHeader,
      body: data ? JSON.stringify(data) : null,
    });
    const body = await res.json().catch(() => null);

    if (!res.ok) {
      // Validation errors (422) arrive as a plain message string,
      // WP_Error responses as { message } objects.
      const message =
        typeof body === "string" && body
          ? body
          : (body?.message ?? `Anfrage fehlgeschlagen (HTTP ${res.status})`);
      throw new Error(message);
    }

    return body;
  }

  async function getConfig() {
    return jsonQuery(`${apiUrl}/config`, "GET");
  }

  async function updateGlobalSettings(config: Record<string, any>) {
    return jsonQuery(`${apiUrl}/update-settings`, "PUT", { config });
  }

  async function updateConsentModalConfig(config: Record<string, any>) {
    return jsonQuery(`${apiUrl}/update-consent-modal-config`, "PUT", {
      config,
    });
  }

  async function updatePreferencesModalConfig(config: Record<string, any>) {
    return jsonQuery(`${apiUrl}/update-preference-modal-config`, "PUT", {
      config,
    });
  }

  async function updateGuiOptions(config: Record<string, any>) {
    return jsonQuery(`${apiUrl}/update-gui-config`, "PUT", { config });
  }

  async function updateCustomStyles(config: Record<string, any>) {
    return jsonQuery(`${apiUrl}/update-custom-styles`, "PUT", { config });
  }

  return {
    getConfig,
    updateGlobalSettings,
    updateConsentModalConfig,
    updatePreferencesModalConfig,
    updateGuiOptions,
    updateCustomStyles,
  };
}
