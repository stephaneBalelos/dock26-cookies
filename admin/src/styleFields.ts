import type { CustomStyles } from "../../types";

export type StyleField = {
  key: string;
  label: string;
  default: string;
  // Colors render a color dropdown, sizes a pixel number input. Defaults to 'color'.
  type?: "color" | "size";
  // Notation the color dropdown writes back. Fields whose default is rgba()
  // keep that notation; everything else is written as hex.
  format?: "hex" | "rgb";
};

export type StyleGroup = {
  label: string;
  fields: StyleField[];
};

// UI metadata for every `--cc-*` property. `default` mirrors
// CookieConfig::CUSTOM_STYLES (includes/cookie-config.php): it is the value the
// "Standard" reset restores and the fallback for a key missing from the server.
export const STYLE_GROUPS: StyleGroup[] = [
  {
    label: "Allgemein",
    fields: [
      { key: "--cc-bg", label: "Hintergrund", default: "#ffffff" },
      { key: "--cc-primary-color", label: "Primärfarbe", default: "#2c2f31" },
      { key: "--cc-secondary-color", label: "Sekundärfarbe", default: "#5e6266" },
    ],
  },
  {
    label: "Primär-Button",
    fields: [
      { key: "--cc-btn-primary-bg", label: "Hintergrund", default: "#30363c" },
      { key: "--cc-btn-primary-color", label: "Textfarbe", default: "#ffffff" },
      { key: "--cc-btn-primary-border-color", label: "Rahmenfarbe", default: "#30363c" },
      { key: "--cc-btn-primary-hover-bg", label: "Hintergrund (Hover)", default: "#000000" },
      { key: "--cc-btn-primary-hover-color", label: "Textfarbe (Hover)", default: "#ffffff" },
      { key: "--cc-btn-primary-hover-border-color", label: "Rahmenfarbe (Hover)", default: "#000000" },
    ],
  },
  {
    label: "Sekundär-Button",
    fields: [
      { key: "--cc-btn-secondary-bg", label: "Hintergrund", default: "#eaeff2" },
      { key: "--cc-btn-secondary-color", label: "Textfarbe", default: "#2c2f31" },
      { key: "--cc-btn-secondary-border-color", label: "Rahmenfarbe", default: "#eaeff2" },
      { key: "--cc-btn-secondary-hover-bg", label: "Hintergrund (Hover)", default: "#d4dae0" },
      { key: "--cc-btn-secondary-hover-color", label: "Textfarbe (Hover)", default: "#000000" },
      { key: "--cc-btn-secondary-hover-border-color", label: "Rahmenfarbe (Hover)", default: "#d4dae0" },
    ],
  },
  {
    label: "Trennlinie",
    fields: [
      { key: "--cc-separator-border-color", label: "Rahmenfarbe", default: "#f0f4f7" },
    ],
  },
  {
    label: "Toggles",
    fields: [
      { key: "--cc-toggle-on-bg", label: "Hintergrund (An)", default: "#30363c" },
      { key: "--cc-toggle-off-bg", label: "Hintergrund (Aus)", default: "#667481" },
      { key: "--cc-toggle-on-knob-bg", label: "Knopf (An)", default: "#ffffff" },
      { key: "--cc-toggle-off-knob-bg", label: "Knopf (Aus)", default: "#ffffff" },
      { key: "--cc-toggle-enabled-icon-color", label: "Icon (Aktiviert)", default: "#ffffff" },
      { key: "--cc-toggle-disabled-icon-color", label: "Icon (Deaktiviert)", default: "#ffffff" },
      { key: "--cc-toggle-readonly-bg", label: "Hintergrund (Schreibgeschützt)", default: "#d5dee2" },
      { key: "--cc-toggle-readonly-knob-bg", label: "Knopf (Schreibgeschützt)", default: "#ffffff" },
      { key: "--cc-toggle-readonly-knob-icon-color", label: "Knopf-Icon (Schreibgeschützt)", default: "#d5dee2" },
    ],
  },
  {
    label: "Kategorie-Blöcke",
    fields: [
      { key: "--cc-section-category-border", label: "Sektions-Rahmen", default: "#f0f4f7" },
      { key: "--cc-cookie-category-block-bg", label: "Block-Hintergrund", default: "#f0f4f7" },
      { key: "--cc-cookie-category-block-border", label: "Block-Rahmen", default: "#f0f4f7" },
      { key: "--cc-cookie-category-block-hover-bg", label: "Block-Hintergrund (Hover)", default: "#e9eff4" },
      { key: "--cc-cookie-category-block-hover-border", label: "Block-Rahmen (Hover)", default: "#e9eff4" },
      { key: "--cc-cookie-category-expanded-block-bg", label: "Ausgeklappter Block", default: "#ffffff00" },
      { key: "--cc-cookie-category-expanded-block-hover-bg", label: "Ausgeklappter Block (Hover)", default: "#dee4e9" },
    ],
  },
  {
    label: "Overlay & Scrollbar",
    fields: [
      { key: "--cc-overlay-bg", label: "Overlay-Hintergrund", default: "rgba(0, 0, 0, 0.65)", format: "rgb" },
      { key: "--cc-webkit-scrollbar-bg", label: "Scrollbar-Hintergrund", default: "#f0f4f7" },
      { key: "--cc-webkit-scrollbar-hover-bg", label: "Scrollbar-Hintergrund (Hover)", default: "#000000" },
    ],
  },
  {
    label: "Footer",
    fields: [
      { key: "--cc-footer-bg", label: "Hintergrund", default: "#eaeff2" },
      { key: "--cc-footer-color", label: "Textfarbe", default: "#5e6266" },
      { key: "--cc-footer-border-color", label: "Rahmenfarbe", default: "#e4eaed" },
    ],
  },
  {
    label: "Abstände & Radien",
    fields: [
      { key: "--cc-modal-border-radius", label: "Modal-Eckenradius", default: "8px", type: "size" },
      { key: "--cc-btn-border-radius", label: "Button-Eckenradius", default: "6px", type: "size" },
      { key: "--cc-modal-margin", label: "Modal-Außenabstand", default: "16px", type: "size" },
    ],
  },
];

export const STYLE_DEFAULTS: CustomStyles = Object.fromEntries(
  STYLE_GROUPS.flatMap((group) => group.fields.map((field) => [field.key, field.default])),
);

/** Keep in sync with CookieConfig::COLOR_PATTERN (includes/cookie-config.php). */
export const COLOR_PATTERN =
  /^(#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})|rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*(0|1|0?\.\d+)\s*)?\)|hsla?\(\s*\d+(\.\d+)?(deg)?\s*,\s*\d+(\.\d+)?%\s*,\s*\d+(\.\d+)?%\s*(,\s*(0|1|0?\.\d+)\s*)?\)|transparent)$/;

/** The saved styles completed with the defaults, in STYLE_GROUPS order. */
export function withStyleDefaults(styles: CustomStyles | null | undefined): CustomStyles {
  return { ...STYLE_DEFAULTS, ...styles };
}

/**
 * The styles as a CSS rule, in the same form CookieConfig::getCustomStylesCss()
 * prints on the frontend (scoped to #cc-main, see there for why).
 */
export function customStylesCss(styles: CustomStyles): string {
  const declarations = Object.entries(styles).map(([property, value]) => `${property}:${value}`);
  return `#cc-main{${declarations.join(";")}}`;
}

/** A plain deep copy; reactive proxies can't go through structuredClone(). */
export function clone<T>(value: T): T {
  return JSON.parse(JSON.stringify(value)) as T;
}
