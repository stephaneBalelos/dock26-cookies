<template>
    <UForm :schema="schema" :state="form" class="space-y-4" @submit="onSubmit">
        <UFormField label="Titel" name="title">
            <UInput v-model="form.title" class="w-full" />
        </UFormField>

        <UFormField label="Beschreibung" name="description">
            <UTextarea v-model="form.description" class="w-full" />
        </UFormField>

        <UFormField label="Text für 'Alle Akzeptieren' Button" name="acceptAllBtn">
            <UInput v-model="form.acceptAllBtn" class="w-full" />
        </UFormField>

        <UFormField label="Text für 'Alle Ablehnen' Button" name="acceptNecessaryBtn">
            <UInput v-model="form.acceptNecessaryBtn" class="w-full" />
        </UFormField>

        <UFormField label="Text für 'Präferenzen' Button" name="showPreferencesBtn">
            <UInput v-model="form.showPreferencesBtn" class="w-full" />
        </UFormField>

        <UButton type="submit">
            Submit
        </UButton>
    </UForm>
</template>

<script setup lang="ts">
import type { FormSubmitEvent } from '@nuxt/ui';
import { reactive } from 'vue';
import z from 'zod';
import type { ConsentModalConfig } from '../../../../types';

type Props = {
    consentConfig: ConsentModalConfig
}

const props = defineProps<Props>()

const schema = z.object({
    title: z.string().min(3, 'Titel muss mindestens 3 Zeichen lang sein').max(64, 'Titel darf maximal 64 Zeichen lang sein'),
    description: z.string().min(3, 'Beschreibung muss mindestens 3 Zeichen lang sein').max(256, 'Beschreibung darf maximal 256 Zeichen lang sein'),
    acceptAllBtn: z.string().min(1, 'Text für "Alle Akzeptieren" Button darf nicht leer sein').max(24, 'Text für "Alle Akzeptieren" Button darf maximal 24 Zeichen lang sein'),
    acceptNecessaryBtn: z.string().min(1, 'Text für "Alle Ablehnen" Button darf nicht leer sein').max(24, 'Text für "Alle Ablehnen" Button darf maximal 24 Zeichen lang sein'),
    showPreferencesBtn: z.string().min(1, 'Text für "Präferenzen" Button darf nicht leer sein').max(24, 'Text für "Präferenzen" Button darf maximal 24 Zeichen lang sein')
})

type Form = z.infer<typeof schema>

const $emits = defineEmits <{
    (event: 'saved', config: ConsentModalConfig): void
}>()

const form = reactive<Form>({
    title: props.consentConfig.title || '',
    description: props.consentConfig.description || '',
    acceptAllBtn: props.consentConfig.acceptAllBtn || '',
    acceptNecessaryBtn: props.consentConfig.acceptNecessaryBtn || '',
    showPreferencesBtn: props.consentConfig.showPreferencesBtn || ''
})

async function onSubmit($event: FormSubmitEvent<Form>) {
    console.log('Form submitted', form)
    $emits('saved', {locale: props.consentConfig.locale, ...$event.data })
}
</script>

<style scoped></style>