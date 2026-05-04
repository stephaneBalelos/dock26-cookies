<template>
    <UForm v-if="form" @submit="onSubmit">
        <UCard :ui="{
            header: 'w-full m-0',
            body: 'w-full',
            footer: 'w-full'
        }">
            <template #header>
                <div class="flex justify-between w-full">
                    <div class="text-lg font-bold m-0">GUI Options</div>

                    <div class="flex">
                        <UButton color="neutral" variant="outline" class="mr-2" icon="i-heroicons-eye"
                            @click="() => $consentConfig.showConsentModal()">
                            Einwilligungs Modal anzeigen
                        </UButton>
                        <UButton color="neutral" variant="outline" class="mr-2" icon="i-heroicons-eye"
                            @click="() => $consentConfig.showPreferencesModal()">
                            Präferenzen Modal anzeigen
                        </UButton>
                    </div>
                </div>
            </template>
            <template #default>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex-1 space-y-4 mb-4">
                        <p class="text-lg font-bold">Consent Modal</p>
                        <UFormField label="Layout" name="consentModal.layout">
                            <USelect v-model="form.consentModal.layout" :items="consentLayoutOptions" class="w-full" />
                        </UFormField>
                        <UFormField label="Position" name="consentModal.position">
                            <USelect v-model="form.consentModal.position" :items="consentPositionOptions"
                                class="w-full" />
                        </UFormField>
                    </div>
                    <div class="flex-1 space-y-4 mb-4">
                        <p class="text-lg font-bold">Preferenz Modal</p>
                        <UFormField label="Layout" name="preferencesModal.layout">
                            <USelect v-model="form.preferencesModal.layout" :items="prefLayoutOptions" class="w-full" />
                        </UFormField>
                        <UFormField label="Position" name="preferencesModal.position">
                            <USelect v-model="form.preferencesModal.position" :items="prefPositionsOptions"
                                class="w-full" :disabled="form.preferencesModal.layout == 'box'" />
                        </UFormField>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="flex justify-end w-full gap-4">
                    <UButton type="submit">
                        Speichern
                    </UButton>
                </div>
            </template>
        </UCard>
    </UForm>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { CookieConfig } from '../../../../types';
import z from 'zod';
import { useConsentConfig } from '@/composables/useConsentConfig';
import type { ConsentModalLayout, ConsentModalPosition, GuiOptions, PreferencesModalLayout, PreferencesModalPosition } from 'vanilla-cookieconsent';

type Props = {
    options: CookieConfig['guiOptions']
}

const props = defineProps<Props>()

const $consentConfig = useConsentConfig()

const $emits = defineEmits<{
    (event: 'saved', config: GuiOptions): void
}>()


const consentLayoutOptions: ConsentModalLayout[] = [
    'bar',
    'box',
    'cloud',
]

const consentPositionOptions = computed(() => {

    if (form.value?.consentModal.layout == 'bar') {
        return ['top', 'bottom'] as ConsentModalPosition[]
    }

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

const prefLayoutOptions: PreferencesModalLayout[] = [
    'box',
    'bar',
]

const prefPositionsOptions = computed(() => {
    return ['right', 'left'] as PreferencesModalPosition[]
})

const schema = computed(() => {
    return z.object({
        consentModal: z.object({
            layout: z.enum([...consentLayoutOptions]),
            position: z.enum([...consentPositionOptions.value]),
        }),
        preferencesModal: z.object({
            layout: z.enum([...prefLayoutOptions]),
            position: z.enum([...prefPositionsOptions.value]),
        })
    })
})


const form = computed({
    get: () => {
        return $consentConfig.config.value?.guiOptions
    }, set: (newValue) => {
        if ($consentConfig.config.value) {
            if ($consentConfig.config.value.guiOptions) {
                $consentConfig.config.value.guiOptions.consentModal.layout = newValue?.consentModal.layout ?? 'box'
                $consentConfig.config.value.guiOptions.consentModal.position = newValue?.consentModal.position ?? 'bottom center'

                $consentConfig.config.value.guiOptions.preferencesModal.layout = newValue?.preferencesModal.layout ?? 'box'
                $consentConfig.config.value.guiOptions.preferencesModal.position = newValue?.preferencesModal.position ?? 'left'

            }
        }
    }
})


function onSubmit() {
    const guiConfig = $consentConfig.config.value?.guiOptions
    console.log(guiConfig)

    try {
        const parsed = schema.value.parse(guiConfig)
        console.log(parsed)
        $emits('saved', {
            consentModal: {
                layout: parsed.consentModal.layout,
                position: parsed.consentModal.position
            },
            preferencesModal: {
                layout: parsed.preferencesModal.layout,
                position: parsed.preferencesModal.position
            }
        })
    } catch (error) {
        console.log(error)
    }
}
</script>

<style scoped></style>