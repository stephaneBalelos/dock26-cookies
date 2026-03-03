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
    data?: Record<string, string | number>,
  ) {
    const res = await fetch(url, {
      method: method,
      headers: requestHeader.value,
      body: data ? JSON.stringify(data) : null,
    });

    return await res.json();
  }

  async function getConsentCategories() {
    return jsonQuery(`${apiUrl}/categories`, "GET");
  }

  async function createConsentCategory(name: string) {
    return jsonQuery(`${apiUrl}/categories`, "POST", {
      category_name: name,
    });
  }

  async function getConsentCategory(id: string) {
    return jsonQuery(`${apiUrl}/categories/${id}`, "GET");
  }

  async function updateConsentCategory(id: string, name: string) {
    return jsonQuery(`${apiUrl}/categories/${id}`, "PUT", {
      category_name: name,
    });
  }

  async function deleteConsentCategory(id: string) {
    return jsonQuery(`${apiUrl}/categories/${id}`, "DELETE");
  }

  return {
    getConsentCategories,
    createConsentCategory,
    getConsentCategory,
    updateConsentCategory,
    deleteConsentCategory,
  };
}
