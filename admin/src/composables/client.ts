import type { AdminData, SettingsSection } from "../../../types";

/** REST client for includes/api.php. */
export function useClient() {
  // Printed by Admin::enqueue_admin_assets() right before this bundle.
  const { apiUrl = "", nonce = "" } = window.dock26CookiesAdmin ?? {};

  /**
   * Saves one settings section and resolves with the resulting admin data.
   * Rejects with an Error carrying the server's (German) message.
   */
  async function saveSection(
    section: SettingsSection,
    config: object,
  ): Promise<AdminData> {
    const res = await fetch(`${apiUrl}/settings/${section}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": nonce,
      },
      body: JSON.stringify({ config }),
    });
    const body = await res.json().catch(() => null);

    if (!res.ok) {
      // Errors arrive as WP_Error objects: { code, message, data }.
      throw new Error(
        body?.message ?? `Anfrage fehlgeschlagen (HTTP ${res.status})`,
      );
    }

    return body;
  }

  return { saveSection };
}
