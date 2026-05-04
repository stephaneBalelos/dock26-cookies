<template>
    <UPage :ui="{
        center: 'lg:col-span-7',
        right: 'lg:col-span-3'
    }">
        <UPageBody class="mt-0">
            <GuiOptionsForm v-if="$consentConfig.config.value?.guiOptions"
                :options="$consentConfig.config.value?.guiOptions" />
        </UPageBody>
    </UPage>
</template>

<script setup lang="ts">
import GuiOptionsForm from '@/components/Forms/GuiOptionsForm.vue';
import { useConsentConfig } from '@/composables/useConsentConfig';

const $consentConfig = useConsentConfig()
</script>

<style scoped></style>