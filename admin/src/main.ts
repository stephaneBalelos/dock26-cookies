import "./style.css";
import { createApp } from "vue";
import App from "./App.vue";
import ui from '@nuxt/ui/vue-plugin'
import { CookieConsentPlugin } from "./plugins/CookieConsentVue";


// declare the global window interface extension for TypeScript
declare global {
  interface Window {
    dock26CookiesAdmin?: {
      apiUrl: string;
      nonce: string;
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
})
app.use(CookieConsentPlugin, {})
app.provide("apiUrl", window.dock26CookiesAdmin?.apiUrl || "");
app.provide("nonce", window.dock26CookiesAdmin?.nonce || "");
app.mount("#dock26-cookies-admin-app");
