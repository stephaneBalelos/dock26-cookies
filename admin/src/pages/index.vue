<template>
    <UPage :ui="{
        center: 'lg:col-span-7',
        right: 'lg:col-span-3'
    }">
        <UPageBody class="mt-0">
            <div class="grid grid-cols-1 gap-4">
                <div v-if="$consentConfig.config.value" class="flex flex-col gap-6">
                    <ConsentModalForm v-if="$consentConfig.config.value.language.translations.de" :consentConfig="{
                        locale: 'de',
                        ...$consentConfig.config.value.language.translations.de.consentModal
                    }" @saved="onUpdateConsentModalConfig" />

                    <PreferencesModalForm v-if="$consentConfig.config.value.language.translations.de" :preferencesConfig="{
                        locale: 'de',
                        ...$consentConfig.config.value.language.translations.de.preferencesModal
                    }" @saved="onUpdatePreferencesModalConfig" />
                </div>
                <div>
                    <UPageCard title="Einwilligung Popup"
                        description="Nuxt UI integrates with latest Tailwind CSS, bringing significant improvements."
                        class="h-full">
                        <div class="flex flex-col">
                            <div class="flex-1 mb-4">
                                dsa
                            </div>
                            <UButton :icon="'i-heroicons-eye'" color="neutral" variant="outline"
                                @click="() => $consentConfig.showPreferencesModal()">
                                Präferenzen Modal anzeigen
                            </UButton>
                        </div>
                    </UPageCard>
                </div>
            </div>
        </UPageBody>
        <template #right>
            <h2>Cookie Kategorien</h2>
        </template>
    </UPage>
</template>

<script setup lang="ts">
import { useClient } from '@/composables/client';
import { useConsentConfig } from '@/composables/useConsentConfig';
import type { ConsentModalConfig } from '../../../types';
import PreferencesModalForm from '@/components/Forms/PreferencesModalForm.vue';

const $consentConfig = useConsentConfig()

const $client = useClient()

async function onUpdateConsentModalConfig(config: ConsentModalConfig) {
    try {
        const updatedConfig = await $client.updateConsentModalConfig(config);
        console.log('Updated consent modal config', updatedConfig);
    } catch (error) {
        console.error('Error updating consent modal config', error);
    }
}
async function onUpdatePreferencesModalConfig(config: ConsentModalConfig) {
    try {
        const updatedConfig = await $client.updatePreferencesModalConfig(config);
        console.log('Updated consent modal config', updatedConfig);
    } catch (error) {
        console.error('Error updating consent modal config', error);
    }
}
</script>

<style scoped></style>