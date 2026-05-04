<template>
    <UHeader>
        <template #title>
            <div class="flex items-center gap-4">
                <p class="text-lg font-bold">Logo</p>

                <div v-if="$consentConfig.isLoading.value">
                    <small>
                        Loading
                    </small>
                </div>
            </div>
        </template>
        <template #right>
            <div>
                <a href="#/">Home</a> |
                <a href="#/settings">About</a> |
                <a href="#/non-existent-path">Broken Link</a>
            </div>
        </template>
    </UHeader>
</template>

<script setup lang="ts">
import { useConsentConfig } from '@/composables/useConsentConfig';


const $consentConfig = useConsentConfig()
</script>

<style scoped></style>