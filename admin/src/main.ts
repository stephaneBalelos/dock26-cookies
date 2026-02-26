import "./style.scss";
import { createApp } from "vue";
import App from "./App.vue";

// declare the global window interface extension for TypeScript
declare global {
  interface Window {
    dock26CookiesAdmin?: {
      nonce: string;
    };
  }
}

const app = createApp(App);
app.provide("nonce", window.dock26CookiesAdmin?.nonce || "");
app.mount("#app");
