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
import { useConsentConfig } from '@/composables/useConsentConfig';
import { useSaveSection } from '@/composables/useSaveSection';
import type { ConsentModalConfig, PreferencesModalConfig } from '../../../types';
import PreferencesModalForm from '@/components/Forms/PreferencesModalForm.vue';

const $consentConfig = useConsentConfig()

const { save } = useSaveSection()

function onUpdateConsentModalConfig(config: ConsentModalConfig) {
    return save('consent-modal', config, 'Einwilligungs-Modal gespeichert')
}

function onUpdatePreferencesModalConfig(config: PreferencesModalConfig) {
    return save('preferences-modal', config, 'Präferenzen-Modal gespeichert')
}
</script>

<style scoped></style>