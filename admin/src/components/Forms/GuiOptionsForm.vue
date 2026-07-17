<template>
    <UForm :state="form" :schema="schema" @submit="onSubmit">
        <UCard :ui="{
            header: 'w-full m-0',
            body: 'w-full',
            footer: 'w-full'
        }">
            <template #header>
                <div class="flex justify-between w-full">
                    <div class="text-lg font-bold m-0">GUI-Optionen</div>

                    <div class="flex">
                        <UButton color="neutral" variant="outline" class="mr-2" icon="i-heroicons-eye"
                            @click="() => $consentConfig.showConsentModal()">
                            Einwilligungs-Modal anzeigen
                        </UButton>
                        <UButton color="neutral" variant="outline" class="mr-2" icon="i-heroicons-eye"
                            @click="() => $consentConfig.showPreferencesModal()">
                            Präferenzen-Modal anzeigen
                        </UButton>
                    </div>
                </div>
            </template>
            <template #default>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex-1 space-y-4 mb-4">
                        <p class="text-lg font-bold">Einwilligungs-Modal</p>
                        <UFormField label="Layout" name="consentModal.layout">
                            <USelect v-model="form.consentModal.layout" :items="consentLayoutOptions" class="w-full" />
                        </UFormField>
                        <UFormField label="Position" name="consentModal.position">
                            <USelect v-model="form.consentModal.position" :items="consentPositionOptions"
                                class="w-full" />
                        </UFormField>
                    </div>
                    <div class="flex-1 space-y-4 mb-4">
                        <p class="text-lg font-bold">Präferenzen-Modal</p>
                        <UFormField label="Layout" name="preferencesModal.layout">
                            <USelect v-model="form.preferencesModal.layout" :items="prefLayoutOptions" class="w-full" />
                        </UFormField>
                        <UFormField label="Position" name="preferencesModal.position">
                            <USelect v-model="form.preferencesModal.position" :items="prefPositionOptions"
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
import { computed, reactive, watch } from 'vue';
import type { FormSubmitEvent } from '@nuxt/ui';
import type { CookieConfig } from '../../../../types';
import z from 'zod';
import { useConsentConfig } from '@/composables/useConsentConfig';
import type { ConsentModalLayout, ConsentModalPosition, GuiOptions, PreferencesModalLayout, PreferencesModalPosition } from 'vanilla-cookieconsent';

type Props = {
    options: NonNullable<CookieConfig['guiOptions']>
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

const prefLayoutOptions: PreferencesModalLayout[] = [
    'box',
    'bar',
]

const prefPositionOptions: PreferencesModalPosition[] = [
    'left',
    'right',
]

const form = reactive({
    consentModal: {
        layout: props.options.consentModal.layout,
        position: props.options.consentModal.position,
    },
    preferencesModal: {
        layout: props.options.preferencesModal.layout,
        position: props.options.preferencesModal.position,
    },
})

const consentPositionOptions = computed<ConsentModalPosition[]>(() => {
    if (form.consentModal.layout == 'bar') {
        return ['top', 'bottom']
    }

    return [
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
})

// The 'bar' layout only supports top/bottom; clamp the position when the
// layout switch makes the current one invalid.
watch(() => form.consentModal.layout, () => {
    if (!consentPositionOptions.value.includes(form.consentModal.position)) {
        form.consentModal.position = 'bottom'
    }
})

// Mirror edits into the shared config so the preview buttons in the header
// show the unsaved state.
watch(form, () => {
    const guiOptions = $consentConfig.config.value?.guiOptions
    if (!guiOptions) {
        return
    }
    guiOptions.consentModal.layout = form.consentModal.layout
    guiOptions.consentModal.position = form.consentModal.position
    guiOptions.preferencesModal.layout = form.preferencesModal.layout
    guiOptions.preferencesModal.position = form.preferencesModal.position
})

const schema = computed(() => {
    return z.object({
        consentModal: z.object({
            layout: z.enum(consentLayoutOptions as [ConsentModalLayout, ...ConsentModalLayout[]]),
            position: z.enum(consentPositionOptions.value as [ConsentModalPosition, ...ConsentModalPosition[]]),
        }),
        preferencesModal: z.object({
            layout: z.enum(prefLayoutOptions as [PreferencesModalLayout, ...PreferencesModalLayout[]]),
            position: z.enum(prefPositionOptions as [PreferencesModalPosition, ...PreferencesModalPosition[]]),
        })
    })
})

type Form = {
    consentModal: { layout: ConsentModalLayout; position: ConsentModalPosition }
    preferencesModal: { layout: PreferencesModalLayout; position: PreferencesModalPosition }
}

async function onSubmit($event: FormSubmitEvent<Form>) {
    $emits('saved', $event.data)
}
</script>

<style scoped></style>
