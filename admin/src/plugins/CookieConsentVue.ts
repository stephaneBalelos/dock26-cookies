import "vanilla-cookieconsent/dist/cookieconsent.css";
import * as CookieConsent from "vanilla-cookieconsent";
import type { InjectionKey, Plugin } from "vue";

export const d26CookieConsentKey = Symbol() as InjectionKey<typeof CookieConsent>


export const CookieConsentPlugin =  {
    install: (app, _pluginConfig) => {
        app.config.globalProperties.$CookieConsent = CookieConsent;
        app.provide(d26CookieConsentKey, CookieConsent)

    }
} satisfies Plugin

declare module 'vue' {
  interface ComponentCustomProperties {
    $CookieConsent: typeof CookieConsent;
  }
}

export {}  
