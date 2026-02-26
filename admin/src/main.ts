import "./style.css";
import { createApp } from "vue";
import App from "./App.vue";
import ui from '@nuxt/ui/vue-plugin'


// declare the global window interface extension for TypeScript
declare global {
  interface Window {
    dock26CookiesAdmin?: {
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
app.mount("#app");
