<template>
    <UForm :schema="schema" :state="form" @submit="onSubmit">
        <UCard :ui="{
            header: 'w-full m-0',
            body: 'w-full',
            footer: 'w-full'
        }">
            <template #header>
                <div class="flex justify-between w-full">
                    <div class="text-lg font-bold m-0">Allgemeine Einstellungen</div>

                    <div class="flex">
                        <UButton color="neutral" variant="outline" class="mr-2" icon="i-heroicons-eye"
                            @click="() => $consentConfig.showConsentModal()">
                            Einwilligungs-Modal anzeigen
                        </UButton>
                    </div>
                </div>
            </template>
            <template #default>
                <div class="flex flex-col">
                    <div class="flex-1 space-y-4 mb-4">
                        <UFormField label="Cookie-Name" name="cookie_name"
                            help="Name des Cookies, in dem die Einwilligung der Besucher gespeichert wird.">
                            <UInput v-model="form.cookie_name" class="w-full" />
                        </UFormField>

                        <UAlert v-if="cookieNameChanged" color="warning" variant="subtle"
                            icon="i-heroicons-exclamation-triangle" title="Änderung des Cookie-Namens"
                            description="Beim Speichern wird die bereits erteilte Einwilligung aller bestehenden Besucher verworfen. Sie werden erneut nach ihrer Einwilligung gefragt." />

                        <UFormField label="URL der Datenschutzerklärung" name="privacy_btn.url">
                            <UInput v-model="form.privacy_btn.url" class="w-full" />
                        </UFormField>

                        <UFormField label="URL des Impressums" name="imprint_btn.url">
                            <UInput v-model="form.imprint_btn.url" class="w-full" />
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
import { computed, reactive } from 'vue';
import z from 'zod';
import type { CookieConfig, GlobalSettingsConfig } from '../../../../types';
import { useConsentConfig } from '@/composables/useConsentConfig';

type Props = {
    settings: CookieConfig['settings']
}

const props = defineProps<Props>()

const $consentConfig = useConsentConfig()

const $emits = defineEmits<{
    (event: 'saved', config: GlobalSettingsConfig): void
}>()

// Absolute URLs, site-relative paths and pure fragments are all valid targets
// here, so z.url() would be too strict.
function urlField(label: string) {
    return z.object({
        url: z.string()
            .trim()
            .min(1, `${label} ist erforderlich`)
            .refine(
                (value) => /^https?:\/\/.+/.test(value) || value.startsWith('/') || value.startsWith('#'),
                `${label} muss mit http://, https://, / oder # beginnen`
            )
    })
}

const schema = z.object({
    cookie_name: z.string()
        .trim()
        .min(1, 'Cookie-Name darf nicht leer sein')
        .max(64, 'Cookie-Name darf maximal 64 Zeichen lang sein')
        .regex(/^[A-Za-z0-9_-]+$/, 'Cookie-Name darf nur Buchstaben, Ziffern, Bindestriche und Unterstriche enthalten'),
    privacy_btn: urlField('URL der Datenschutzerklärung'),
    imprint_btn: urlField('URL des Impressums'),
})

type Form = z.infer<typeof schema>

const form = reactive<Form>({
    cookie_name: props.settings.cookie_name || '',
    privacy_btn: { url: props.settings.privacy_btn.url || '' },
    imprint_btn: { url: props.settings.imprint_btn.url || '' },
})

const cookieNameChanged = computed(() => form.cookie_name !== props.settings.cookie_name)

async function onSubmit($event: FormSubmitEvent<Form>) {
    $emits('saved', $event.data)
}
</script>

<style scoped></style>
