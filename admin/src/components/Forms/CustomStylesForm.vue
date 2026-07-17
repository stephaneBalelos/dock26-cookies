<template>
    <UForm :state="form" @submit="onSubmit">
        <UCard :ui="{
            header: 'w-full m-0',
            body: 'w-full',
            footer: 'w-full'
        }">
            <template #header>
                <div class="flex justify-between w-full">
                    <div class="text-lg font-bold m-0">Benutzerdefinierte Stile</div>
                </div>
            </template>
            <template #default>
                <div class="flex flex-col">
                    <div v-for="group in groups" :key="group.label" class="flex-1 mb-8">
                        <div class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">
                            {{ group.label }}
                        </div>
                        <div class="space-y-4">
                            <UFormField v-for="field in group.fields" :key="field.key" :label="field.label"
                                :name="field.key">
                                <UInputNumber v-if="field.type === 'size'" :model-value="sizeValue(field.key)"
                                    @update:model-value="value => setSizeValue(field.key, value)" :min="0"
                                    class="w-full" />
                                <div v-else class="flex items-center gap-2">
                                    <UColorPicker v-model="form[field.key]" :format="field.format ?? 'hex'" />
                                    <UInput v-model="form[field.key]" class="w-full" />
                                </div>
                            </UFormField>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end w-full gap-4">
                    <UButton type="submit">
                        Speichern
                    </UButton>
                </div>
            </template>
        </UCard>
    </UForm>
</template>

<script setup lang="ts">
import type { FormSubmitEvent } from '@nuxt/ui';
import { reactive } from 'vue';
import type { CustomStyles } from '../../../../types';

type StyleField = {
    key: string
    label: string
    default: string
    // Colors render a picker + hex field, sizes a pixel number input. Defaults to 'color'.
    type?: 'color' | 'size'
    // Non-hex defaults (rgba) render the color picker with an alpha-capable format.
    // For those the text UInput stays the source of truth (e.g. `transparent` isn't
    // representable by the picker but is preserved by the input).
    format?: 'hex' | 'rgb'
}

type StyleGroup = {
    label: string
    fields: StyleField[]
}

// UI metadata for every `--cc-*` property. `default` is only a fallback: the live
// defaults come from the server (CookieConfig::CUSTOM_STYLES) via the `styles` prop.
const groups: StyleGroup[] = [
    {
        label: 'Allgemein',
        fields: [
            { key: '--cc-bg', label: 'Hintergrund', default: '#ffffff' },
            { key: '--cc-primary-color', label: 'Primärfarbe', default: '#2c2f31' },
            { key: '--cc-secondary-color', label: 'Sekundärfarbe', default: '#5e6266' },
        ],
    },
    {
        label: 'Primär-Button',
        fields: [
            { key: '--cc-btn-primary-bg', label: 'Hintergrund', default: '#30363c' },
            { key: '--cc-btn-primary-color', label: 'Textfarbe', default: '#ffffff' },
            { key: '--cc-btn-primary-border-color', label: 'Rahmenfarbe', default: '#30363c' },
            { key: '--cc-btn-primary-hover-bg', label: 'Hintergrund (Hover)', default: '#000000' },
            { key: '--cc-btn-primary-hover-color', label: 'Textfarbe (Hover)', default: '#ffffff' },
            { key: '--cc-btn-primary-hover-border-color', label: 'Rahmenfarbe (Hover)', default: '#000000' },
        ],
    },
    {
        label: 'Sekundär-Button',
        fields: [
            { key: '--cc-btn-secondary-bg', label: 'Hintergrund', default: '#eaeff2' },
            { key: '--cc-btn-secondary-color', label: 'Textfarbe', default: '#2c2f31' },
            { key: '--cc-btn-secondary-border-color', label: 'Rahmenfarbe', default: '#eaeff2' },
            { key: '--cc-btn-secondary-hover-bg', label: 'Hintergrund (Hover)', default: '#d4dae0' },
            { key: '--cc-btn-secondary-hover-color', label: 'Textfarbe (Hover)', default: '#000000' },
            { key: '--cc-btn-secondary-hover-border-color', label: 'Rahmenfarbe (Hover)', default: '#d4dae0' },
        ],
    },
    {
        label: 'Trennlinie',
        fields: [
            { key: '--cc-separator-border-color', label: 'Rahmenfarbe', default: '#f0f4f7' },
        ],
    },
    {
        label: 'Toggles',
        fields: [
            { key: '--cc-toggle-on-bg', label: 'Hintergrund (An)', default: '#30363c' },
            { key: '--cc-toggle-off-bg', label: 'Hintergrund (Aus)', default: '#667481' },
            { key: '--cc-toggle-on-knob-bg', label: 'Knopf (An)', default: '#ffffff' },
            { key: '--cc-toggle-off-knob-bg', label: 'Knopf (Aus)', default: '#ffffff' },
            { key: '--cc-toggle-enabled-icon-color', label: 'Icon (Aktiviert)', default: '#ffffff' },
            { key: '--cc-toggle-disabled-icon-color', label: 'Icon (Deaktiviert)', default: '#ffffff' },
            { key: '--cc-toggle-readonly-bg', label: 'Hintergrund (Schreibgeschützt)', default: '#d5dee2' },
            { key: '--cc-toggle-readonly-knob-bg', label: 'Knopf (Schreibgeschützt)', default: '#ffffff' },
            { key: '--cc-toggle-readonly-knob-icon-color', label: 'Knopf-Icon (Schreibgeschützt)', default: '#d5dee2' },
        ],
    },
    {
        label: 'Kategorie-Blöcke',
        fields: [
            { key: '--cc-section-category-border', label: 'Sektions-Rahmen', default: '#f0f4f7' },
            { key: '--cc-cookie-category-block-bg', label: 'Block-Hintergrund', default: '#f0f4f7' },
            { key: '--cc-cookie-category-block-border', label: 'Block-Rahmen', default: '#f0f4f7' },
            { key: '--cc-cookie-category-block-hover-bg', label: 'Block-Hintergrund (Hover)', default: '#e9eff4' },
            { key: '--cc-cookie-category-block-hover-border', label: 'Block-Rahmen (Hover)', default: '#e9eff4' },
            { key: '--cc-cookie-category-expanded-block-bg', label: 'Ausgeklappter Block', default: '#ffffff00' },
            { key: '--cc-cookie-category-expanded-block-hover-bg', label: 'Ausgeklappter Block (Hover)', default: '#dee4e9' },
        ],
    },
    {
        label: 'Overlay & Scrollbar',
        fields: [
            { key: '--cc-overlay-bg', label: 'Overlay-Hintergrund', default: 'rgba(0, 0, 0, 0.65)', format: 'rgb' },
            { key: '--cc-webkit-scrollbar-bg', label: 'Scrollbar-Hintergrund', default: '#f0f4f7' },
            { key: '--cc-webkit-scrollbar-hover-bg', label: 'Scrollbar-Hintergrund (Hover)', default: '#000000' },
        ],
    },
    {
        label: 'Footer',
        fields: [
            { key: '--cc-footer-bg', label: 'Hintergrund', default: '#eaeff2' },
            { key: '--cc-footer-color', label: 'Textfarbe', default: '#5e6266' },
            { key: '--cc-footer-border-color', label: 'Rahmenfarbe', default: '#e4eaed' },
        ],
    },
    {
        label: 'Abstände & Radien',
        fields: [
            { key: '--cc-modal-border-radius', label: 'Modal-Eckenradius', default: '8px', type: 'size' },
            { key: '--cc-btn-border-radius', label: 'Button-Eckenradius', default: '6px', type: 'size' },
            { key: '--cc-modal-margin', label: 'Modal-Außenabstand', default: '16px', type: 'size' },
        ],
    },
]

type Props = {
    styles: CustomStyles
}

const props = defineProps<Props>()

const $emits = defineEmits<{
    (event: 'saved', styles: CustomStyles): void
}>()

// State keyed by the CSS variable name so the submitted object is a directly
// usable style map. The color picker and the text input share the same value,
// which keeps them in sync without a watcher. The hardcoded defaults are a
// fallback so a key missing from the server can't yield an undefined v-model.
const form = reactive<Record<string, string>>({
    ...Object.fromEntries(
        groups.flatMap(group => group.fields.map(field => [field.key, field.default]))
    ),
    ...props.styles,
})

// Sizes are stored as `8px` strings like every other value, so the number input
// converts on the way in and out.
function sizeValue(key: string): number {
    return parseFloat(form[key] ?? '0')
}

function setSizeValue(key: string, value: number | undefined) {
    form[key] = `${value ?? 0}px`
}

async function onSubmit(_event: FormSubmitEvent<Record<string, string>>) {
    $emits('saved', { ...form })
}
</script>

<style scoped></style>
