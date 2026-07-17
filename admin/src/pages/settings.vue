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
                    :options="$consentConfig.config.value?.guiOptions" @saved="onUpdateGuiOptions" />

                <CustomStylesForm v-if="initialCustomStyles" :styles="initialCustomStyles"
                    @saved="onUpdateCustomStyles" />
            </div>
        </UPageBody>
    </UPage>
</template>

<script setup lang="ts">
import { inject } from 'vue';
import ConsentGlobalSettingsForm from '@/components/Forms/ConsentGlobalSettingsForm.vue';
import GuiOptionsForm from '@/components/Forms/GuiOptionsForm.vue';
import { useClient } from '@/composables/client';
import { useConsentConfig } from '@/composables/useConsentConfig';
import type { CustomStyles, GlobalSettingsConfig } from '../../../types';
import type { GuiOptions } from 'vanilla-cookieconsent';

const $consentConfig = useConsentConfig()

const $client = useClient()

const toast = useToast()

const initialCustomStyles = inject('initialCustomStyles', null) as CustomStyles | null

function notifyError(error: unknown) {
    toast.add({
        title: 'Speichern fehlgeschlagen',
        description: error instanceof Error ? error.message : String(error),
        icon: 'i-heroicons-exclamation-triangle',
        color: 'error'
    })
}

async function onUpdateGlobalSettings(data: GlobalSettingsConfig) {
    try {
        await $client.updateGlobalSettings(data);
        toast.add({ title: 'Allgemeine Einstellungen gespeichert', color: 'success' })
        await $consentConfig.reloadConfig()
    } catch (error) {
        notifyError(error)
    }
}

async function onUpdateGuiOptions(data: GuiOptions) {
    try {
        await $client.updateGuiOptions(data);
        toast.add({ title: 'GUI-Optionen gespeichert', color: 'success' })
        await $consentConfig.reloadConfig()
    } catch (error) {
        notifyError(error)
    }
}

async function onUpdateCustomStyles(data: CustomStyles) {
    try {
        await $client.updateCustomStyles(data);
        toast.add({ title: 'Benutzerdefinierte Stile gespeichert', color: 'success' })
    } catch (error) {
        notifyError(error)
    }
}
</script>

<style scoped></style>