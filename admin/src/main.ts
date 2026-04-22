import "./style.css";
import { createApp } from "vue";
import App from "./App.vue";
import ui from "@nuxt/ui/vue-plugin";
import { CookieConsentPlugin } from "./plugins/CookieConsentVue";
import type { CookieConfig } from "../../types";

// declare the global window interface extension for TypeScript
declare global {
  interface Window {
    dock26CookiesAdmin?: {
      apiUrl: string;
      nonce: string;
      config: CookieConfig;
    };
  }
}

const app = createApp(App);
app.use(ui, {
  router: false,
  colorMode: false,
  theme: {
    prefix: "tw",
  },
});
console.log("Initial config from server:", window.dock26CookiesAdmin?.config);
app.use(CookieConsentPlugin, {});
app.provide("apiUrl", window.dock26CookiesAdmin?.apiUrl || "");
app.provide("nonce", window.dock26CookiesAdmin?.nonce || "");
app.provide("initialConfig", window.dock26CookiesAdmin?.config || null);
app.mount("#dock26-cookies-admin-app");
