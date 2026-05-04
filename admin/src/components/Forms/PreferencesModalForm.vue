<template>
    <UForm :schema="schema" :state="form" @submit="onSubmit">
        <UCard :ui="{
            header: 'w-full m-0',
            body: 'w-full',
            footer: 'w-full'
        }">
            <template #header>
                <div class="flex justify-between w-full">
                    <div class="text-lg font-bold m-0">Preferenzen Modal - {{
                        props.preferencesConfig.locale.toUpperCase() }}</div>

                    <div class="flex">
                        <UButton color="neutral" variant="outline" class="mr-2" icon="i-heroicons-eye"
                            @click="() => $consentConfig.showPreferencesModal()">
                            Preferenzen Modal anzeigen
                        </UButton>
                    </div>
                </div>
            </template>
            <template #default>
                <div class="flex flex-col">
                    <div class="flex-1 space-y-4 mb-4">
                        <UFormField label="Titel" name="title">
                            <UInput v-model="form.title" class="w-full" />
                        </UFormField>
                        <UFormField label="Text für 'Alle Akzeptieren' Button" name="acceptAllBtn">
                            <UInput v-model="form.acceptAllBtn" class="w-full" />
                        </UFormField>
                        <UFormField label="Text für 'Alle Ablehnen' Button" name="acceptNecessaryBtn">
                            <UInput v-model="form.acceptNecessaryBtn" class="w-full" />
                        </UFormField>
                        <UFormField label="Text für 'Präferenzen' Button" name="savePreferencesBtn">
                            <UInput v-model="form.savePreferencesBtn" class="w-full" />
                        </UFormField>
                        <UFormField label="Text für 'Präferenzen' Button" name="closeIconLabel">
                            <UInput v-model="form.closeIconLabel" class="w-full" />
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
import type { FormSubmitEvent } from '@nuxt/ui';
import { reactive } from 'vue';
import z from 'zod';
import type { PreferencesModalConfig } from '../../../../types';
import { useConsentConfig } from '@/composables/useConsentConfig';

type Props = {
    preferencesConfig: PreferencesModalConfig
}

const props = defineProps<Props>()

const $consentConfig = useConsentConfig()


const schema = z.object({
    title: z.string().min(3, 'Titel muss mindestens 3 Zeichen lang sein').max(64, 'Titel darf maximal 64 Zeichen lang sein'),
    acceptAllBtn: z.string().min(1, 'Text für "Alle Akzeptieren" Button darf nicht leer sein').max(24, 'Text für "Alle Akzeptieren" Button darf maximal 24 Zeichen lang sein'),
    acceptNecessaryBtn: z.string().min(1, 'Text für "Nur Notwendige" Button darf nicht leer sein').max(24, 'Text für "Nur Notwendige" Button darf maximal 24 Zeichen lang sein'),
    savePreferencesBtn: z.string().min(1, 'Text für "Präferenzen Speichern" Button darf nicht leer sein').max(24, 'Text für "Präferenzen Speichern" Button darf maximal 24 Zeichen lang sein'),
    closeIconLabel: z.string().min(1, 'Text für "schließen" Button darf nicht leer sein').max(24, 'Text für "Schließen" Button darf maximal 24 Zeichen lang sein'),
})

type Form = z.infer<typeof schema>

const $emits = defineEmits<{
    (event: 'saved', config: PreferencesModalConfig): void
}>()


const form = reactive<Form>({
    title: props.preferencesConfig.title || '',
    acceptAllBtn: props.preferencesConfig.acceptAllBtn || '',
    acceptNecessaryBtn: props.preferencesConfig.acceptNecessaryBtn || '',
    savePreferencesBtn: props.preferencesConfig.savePreferencesBtn || '',
    closeIconLabel: props.preferencesConfig.closeIconLabel || ''
})

async function onSubmit($event: FormSubmitEvent<Form>) {
    console.log('Form submitted', form)
    $emits('saved', { locale: props.preferencesConfig.locale, ...$event.data })
}
</script>

<style scoped></style>