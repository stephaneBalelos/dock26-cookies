<template>
    <UPage :ui="{
        center: 'lg:col-span-7',
        right: 'lg:col-span-3'
    }">
        <UPageBody class="mt-0">
            <div class="grid grid-cols-1 gap-4">
                <ConsentGlobalSettingsForm v-if="$consentConfig.config.value?.settings"
                    :settings="$consentConfig.config.value.settings" @saved="onUpdateGlobalSettings" />

                <GuiOptionsForm v-if="$consentConfig.config.value?.guiOptions"
                    :options="$consentConfig.config.value.guiOptions" @saved="onUpdateGuiOptions" />

                <CustomStylesForm v-if="$consentConfig.customStyles.value"
                    :styles="$consentConfig.customStyles.value" @saved="onUpdateCustomStyles" />
            </div>
        </UPageBody>
    </UPage>
</template>

<script setup lang="ts">
import ConsentGlobalSettingsForm from '@/components/Forms/ConsentGlobalSettingsForm.vue';
import GuiOptionsForm from '@/components/Forms/GuiOptionsForm.vue';
import { useConsentConfig } from '@/composables/useConsentConfig';
import { useSaveSection } from '@/composables/useSaveSection';
import type { CustomStyles, GlobalSettingsConfig } from '../../../types';
import type { GuiOptions } from 'vanilla-cookieconsent';

const $consentConfig = useConsentConfig()

const { save } = useSaveSection()

function onUpdateGlobalSettings(data: GlobalSettingsConfig) {
    return save('global', data, 'Allgemeine Einstellungen gespeichert')
}

function onUpdateGuiOptions(data: GuiOptions) {
    return save('gui', data, 'GUI-Optionen gespeichert')
}

function onUpdateCustomStyles(data: CustomStyles) {
    return save('custom-styles', data, 'Benutzerdefinierte Stile gespeichert')
}
</script>

<style scoped></style>