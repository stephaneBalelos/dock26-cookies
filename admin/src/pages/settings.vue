<template>
    <UPage :ui="{
        center: 'lg:col-span-7',
        right: 'lg:col-span-3'
    }">
        <UPageBody class="mt-0">
            <GuiOptionsForm v-if="$consentConfig.config.value?.guiOptions"
                :options="$consentConfig.config.value?.guiOptions" @saved="onUpdateGuiOptions" />
        </UPageBody>
    </UPage>
</template>

<script setup lang="ts">
import GuiOptionsForm from '@/components/Forms/GuiOptionsForm.vue';
import { useClient } from '@/composables/client';
import { useConsentConfig } from '@/composables/useConsentConfig';
import type { GuiOptions } from 'vanilla-cookieconsent';

const $consentConfig = useConsentConfig()

const $client = useClient()

async function onUpdateGuiOptions(data: GuiOptions) {
    try {
        const updatedConfig = await $client.updateGuiOptions(data);
        console.log('Updated consent modal config', updatedConfig);
    } catch (error) {
        console.error('Error updating consent modal config', error);
    }
}
</script>

<style scoped></style>