import { computed, inject } from "vue";
import type { ConsentCategory } from "../../../types"


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

  async function getConsentCategories(): Promise<ConsentCategory[]> {
    return jsonQuery(`${apiUrl}/categories`, "GET")
  }

  async function createConsentCategory(name: string) {
    return jsonQuery(`${apiUrl}/categories`, "POST", {
      category_name: name,
    });
  }

  async function updateConsentCategoriesOrder(order: number[]) {
    return jsonQuery(`${apiUrl}/categories`, "put", {
      categories_sort: order
    })
  }

  async function getConsentCategory(id: string): Promise<ConsentCategory> {
    return jsonQuery(`${apiUrl}/categories/${id}`, "GET");
  }

  async function updateConsentCategory(id: string, data: {name: string, description?: string}) {
    console.log(data)
    return jsonQuery(`${apiUrl}/categories/${id}`, "PUT", {
      category_name: data.name,
      category_description: data.description,
    });
  }

  async function deleteConsentCategory(id: string) {
    return jsonQuery(`${apiUrl}/categories/${id}`, "DELETE");
  }

  return {
    getConsentCategories,
    updateConsentCategoriesOrder,
    createConsentCategory,
    getConsentCategory,
    updateConsentCategory,
    deleteConsentCategory,
  };
}
