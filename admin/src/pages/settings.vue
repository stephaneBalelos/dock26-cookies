<template>
    <UPage :ui="{
        center: 'lg:col-span-7',
        right: 'lg:col-span-3'
    }">
        <UPageBody class="mt-0">
            <div class="grid grid-cols-1 gap-4">
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
import GuiOptionsForm from '@/components/Forms/GuiOptionsForm.vue';
import { useClient } from '@/composables/client';
import { useConsentConfig } from '@/composables/useConsentConfig';
import type { CustomStyles } from '../../../types';
import type { GuiOptions } from 'vanilla-cookieconsent';

const $consentConfig = useConsentConfig()

const $client = useClient()

const initialCustomStyles = inject('initialCustomStyles', null) as CustomStyles | null

async function onUpdateGuiOptions(data: GuiOptions) {
    try {
        const updatedConfig = await $client.updateGuiOptions(data);
        console.log('Updated consent modal config', updatedConfig);
    } catch (error) {
        console.error('Error updating consent modal config', error);
    }
}

async function onUpdateCustomStyles(data: CustomStyles) {
    try {
        const updatedStyles = await $client.updateCustomStyles(data);
        console.log('Updated custom styles', updatedStyles);
    } catch (error) {
        console.error('Error updating custom styles', error);
    }
}
</script>

<style scoped></style>