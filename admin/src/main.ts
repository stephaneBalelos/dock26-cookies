import "./style.css";
import "vanilla-cookieconsent/dist/cookieconsent.css";
import { createApp } from "vue";
import App from "./App.vue";
import ui from "@nuxt/ui/vue-plugin";
import type { CookieConfig, CustomStyles } from "../../types";

// declare the global window interface extension for TypeScript
declare global {
  interface Window {
    dock26CookiesAdmin?: {
      apiUrl: string;
      nonce: string;
      config: CookieConfig;
      customStyles: CustomStyles;
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
app.provide("apiUrl", window.dock26CookiesAdmin?.apiUrl || "");
app.provide("nonce", window.dock26CookiesAdmin?.nonce || "");
app.provide("initialConfig", window.dock26CookiesAdmin?.config || null);
app.provide(
  "initialCustomStyles",
  window.dock26CookiesAdmin?.customStyles || null,
);
app.mount("#dock26-cookies-admin-app");
