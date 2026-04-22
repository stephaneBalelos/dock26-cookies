<template>
    <UPage :ui="{
        center: 'lg:col-span-7',
        right: 'lg:col-span-3'
    }">
        <UPageBody class="mt-0">
            <div class="grid grid-cols-1 gap-4">
                <div class="">
                    <UPageCard v-if="$consentConfig.config.value" title="Einwilligungs Modal" description="Hier kannst du die Inhalte des Einwilligungs Modals anpassen.">
                        <div class="flex flex-col">
                            <div class="flex-1 mb-4">
                                <ConsentModalForm :consentConfig="{ locale: 'de'}" @saved="(data) => console.log(data)" />
                            </div>
                            <UButton :icon="'i-heroicons-eye'" color="neutral" variant="outline"
                                @click="() => $consentConfig.showConsentModal()">
                                Einwiligung Modal anzeigen
                            </UButton>
                        </div>
                    </UPageCard>
                </div>
                <div>
                    <UPageCard title="Einwilligung Popup"
                        description="Nuxt UI integrates with latest Tailwind CSS, bringing significant improvements."
                        class="h-full">
                        <div class="flex flex-col">
                            <div class="flex-1 mb-4">
                                dsa
                            </div>
                            <UButton :icon="'i-heroicons-eye'" color="neutral" variant="outline" @click="() => $consentConfig.showPreferencesModal()">
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
import { useConsentConfig } from '@/composables/useConsentConfig';

const $consentConfig = useConsentConfig()
</script>

<style scoped></style>