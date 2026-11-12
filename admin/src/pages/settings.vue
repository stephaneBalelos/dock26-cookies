<template>
    <UPage :ui="{
        center: 'lg:col-span-7',
        right: 'lg:col-span-3'
    }">
        <UPageBody class="mt-0">
            <div class="grid grid-cols-1 gap-4">
                <ConsentGlobalSettingsForm v-if="$consentConfig.config.value?.settings"
                    :settings="$consentConfig.config.value.settings" @saved="onUpdateGlobalSettings" />
            </div>
        </UPageBody>
    </UPage>
</template>

<script setup lang="ts">
import ConsentGlobalSettingsForm from '@/components/Forms/ConsentGlobalSettingsForm.vue';
import { useConsentConfig } from '@/composables/useConsentConfig';
import { useSaveSection } from '@/composables/useSaveSection';
import type { GlobalSettingsConfig } from '../../../types';

const $consentConfig = useConsentConfig()

const { save } = useSaveSection()

function onUpdateGlobalSettings(data: GlobalSettingsConfig) {
    return save('global', data, 'Allgemeine Einstellungen gespeichert')
}
</script>

<style scoped></style>
