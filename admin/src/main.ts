import "./style.css";
import "vanilla-cookieconsent/dist/cookieconsent.css";
import { createApp } from "vue";
import App from "./App.vue";
import ui from "@nuxt/ui/vue-plugin";
import type { AdminData } from "../../types";

declare global {
  interface Window {
    // Printed by Admin::enqueue_admin_assets() right before this bundle.
    dock26CookiesAdmin?: AdminData & {
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
});
app.mount("#dock26-cookies-admin-app");
