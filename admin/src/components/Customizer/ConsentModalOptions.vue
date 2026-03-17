<template>
    <div class="flex flex-col justify-start">
        <UFormField
        v-if="$consentConfig.guiOptions.value.consentModal?.layout"
            :label="'Layout'"
        >
          <USelectMenu :model-value="$consentConfig.guiOptions.value.consentModal.layout" @update:model-value="updateLayout" :items="layoutOptions" class="w-48" />
    </UFormField>
        <UFormField
        v-if="$consentConfig.guiOptions.value.consentModal?.position"
            :label="'Position'"
        >
          <USelectMenu :model-value="$consentConfig.guiOptions.value.consentModal.position" @update:model-value="updatePosition" :items="positionOptions" class="w-48" />
    </UFormField>
    </div>
</template>

<script setup lang="ts">
import { useConsentConfig } from '@/composables/useConsentConfig';
import type { ConsentModalLayout, ConsentModalPosition } from 'vanilla-cookieconsent';
import { computed } from 'vue';


const $consentConfig = useConsentConfig()

const layoutOptions: ConsentModalLayout[] = [
    'bar',
    'bar inline',
    'box',
    'box inline',
    'box wide',
    'cloud',
    'cloud inline'
]

const positionOptions = computed(() => {
    const options: ConsentModalPosition[] = [
        'bottom',
        'bottom center',
        'bottom left',
        'bottom right',
        'middle',
        'middle center',
        'middle left',
        'middle right',
        'top',
        'top center',
        'top left',
        'top right'
    ]

    return options
})

function updateLayout(value: ConsentModalLayout) {
    $consentConfig.guiOptions.value.consentModal
    if ($consentConfig.guiOptions.value.consentModal) {
        $consentConfig.guiOptions.value.consentModal.layout = value
    } else {
        $consentConfig.guiOptions.value.consentModal = {
            layout: value
        }
    }
    $consentConfig.showConsentModal()
}
function updatePosition(value: ConsentModalPosition) {
    $consentConfig.guiOptions.value.consentModal
    if ($consentConfig.guiOptions.value.consentModal) {
        $consentConfig.guiOptions.value.consentModal.position = value
    } else {
        $consentConfig.guiOptions.value.consentModal = {
            position: value
        }
    }
    $consentConfig.showConsentModal()
}




</script>

<style scoped>

</style>