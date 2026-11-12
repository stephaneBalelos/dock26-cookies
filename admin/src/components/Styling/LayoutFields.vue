<template>
    <div class="space-y-4">
        <div class="space-y-2">
            <p class="text-xs font-medium text-highlighted">Einwilligungs-Modal</p>
            <UFormField label="Layout" orientation="horizontal" size="sm" :ui="fieldUi">
                <USelect :model-value="modelValue.consentModal.layout" :items="consentLayoutOptions" size="sm"
                    class="w-44" @update:model-value="setConsentLayout" />
            </UFormField>
            <UFormField label="Position" orientation="horizontal" size="sm" :ui="fieldUi">
                <USelect :model-value="modelValue.consentModal.position" :items="consentPositionOptions" size="sm"
                    class="w-44"
                    @update:model-value="(value: ConsentModalPosition) => update('consentModal', { position: value })" />
            </UFormField>
        </div>

        <div class="space-y-2">
            <p class="text-xs font-medium text-highlighted">Präferenzen-Modal</p>
            <UFormField label="Layout" orientation="horizontal" size="sm" :ui="fieldUi">
                <USelect :model-value="modelValue.preferencesModal.layout" :items="prefLayoutOptions" size="sm"
                    class="w-44"
                    @update:model-value="(value: PreferencesModalLayout) => update('preferencesModal', { layout: value })" />
            </UFormField>
            <UFormField label="Position" orientation="horizontal" size="sm" :ui="fieldUi">
                <USelect :model-value="modelValue.preferencesModal.position" :items="prefPositionOptions" size="sm"
                    class="w-44" :disabled="modelValue.preferencesModal.layout === 'box'"
                    @update:model-value="(value: PreferencesModalPosition) => update('preferencesModal', { position: value })" />
            </UFormField>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { ConsentModalLayout, ConsentModalPosition, PreferencesModalLayout, PreferencesModalPosition } from 'vanilla-cookieconsent';
import type { CookieConfig } from '../../../../types';

type GuiOptions = CookieConfig['guiOptions']

const props = defineProps<{
    modelValue: GuiOptions
}>()

const $emits = defineEmits<{
    (event: 'update:modelValue', options: GuiOptions): void
}>()

const fieldUi = { root: 'items-center', label: 'font-normal' }

const consentLayoutOptions: ConsentModalLayout[] = ['bar', 'box', 'cloud']

const prefLayoutOptions: PreferencesModalLayout[] = ['box', 'bar']

const prefPositionOptions: PreferencesModalPosition[] = ['left', 'right']

const ALL_CONSENT_POSITIONS: ConsentModalPosition[] = [
    'bottom',
    'bottom center',
    'bottom left',
    'bottom right',
    'middle',
    'middle center',
    'middle left',
    'middle right',
    'top',
    'top center',
    'top left',
    'top right',
]

function positionsFor(layout: ConsentModalLayout): ConsentModalPosition[] {
    return layout === 'bar' ? ['top', 'bottom'] : ALL_CONSENT_POSITIONS
}

const consentPositionOptions = computed(() => positionsFor(props.modelValue.consentModal.layout))

function update<K extends keyof GuiOptions>(modal: K, changes: Partial<GuiOptions[K]>) {
    $emits('update:modelValue', {
        ...props.modelValue,
        [modal]: { ...props.modelValue[modal], ...changes },
    })
}

// The 'bar' layout only supports top/bottom; clamp the position when the
// layout switch makes the current one invalid.
function setConsentLayout(layout: ConsentModalLayout) {
    const position = props.modelValue.consentModal.position
    update('consentModal', {
        layout,
        position: positionsFor(layout).includes(position) ? position : 'bottom',
    })
}
</script>
