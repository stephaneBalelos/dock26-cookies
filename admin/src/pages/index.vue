<template>
    <UPage :ui="{
        center: 'lg:col-span-7',
        right: 'lg:col-span-3'
    }">
        <UPageBody class="mt-0">
            <div v-if="$consentConfig.config.value" class="flex flex-col gap-6">
                <ConsentModalForm v-if="$consentConfig.config.value.language.translations.de"
                    :consentConfig="$consentConfig.config.value.language.translations.de.consentModal"
                    @saved="onUpdateConsentModalConfig" />

                <PreferencesModalForm v-if="$consentConfig.config.value.language.translations.de"
                    :preferencesConfig="$consentConfig.config.value.language.translations.de.preferencesModal"
                    @saved="onUpdatePreferencesModalConfig" />
            </div>
        </UPageBody>
    </UPage>
</template>

<script setup lang="ts">
import { useClient } from '@/composables/client';
import { useConsentConfig } from '@/composables/useConsentConfig';
import type { ConsentModalConfig, PreferencesModalConfig } from '../../../types';
import PreferencesModalForm from '@/components/Forms/PreferencesModalForm.vue';

const $consentConfig = useConsentConfig()

const $client = useClient()

const toast = useToast()

function notifyError(error: unknown) {
    toast.add({
        title: 'Speichern fehlgeschlagen',
        description: error instanceof Error ? error.message : String(error),
        icon: 'i-heroicons-exclamation-triangle',
        color: 'error'
    })
}

async function onUpdateConsentModalConfig(config: ConsentModalConfig) {
    try {
        await $client.updateConsentModalConfig(config);
        toast.add({ title: 'Einwilligungs-Modal gespeichert', color: 'success' })
        await $consentConfig.reloadConfig()
    } catch (error) {
        notifyError(error)
    }
}
async function onUpdatePreferencesModalConfig(config: PreferencesModalConfig) {
    try {
        await $client.updatePreferencesModalConfig(config);
        toast.add({ title: 'Präferenzen-Modal gespeichert', color: 'success' })
        await $consentConfig.reloadConfig()
    } catch (error) {
        notifyError(error)
    }
}
</script>

<style scoped></style>