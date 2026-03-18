<template>
    <UCard title="Kategorie bearbeiten" :description="props.id">
        <template #default>
            <UForm :state="state" class="space-y-4" @submit="onSubmit">
                <UFormField label="Kategorie Name" name="name">
                    <UInput v-model="state.name" class="w-full" />
                </UFormField>
                <UFormField v-if="props.id" label="Beschreibung" name="description">
                    <UTextarea v-model="state.description" class="w-full" />
                </UFormField>
                <div class="flex items-center py-4 gap-2">
                    <UButton type="submit">
                        Speichern
                    </UButton>
                    <UButton v-if="props.id" :icon="'i-heroicons-trash'" color="error" variant="ghost"
                        @click="onDelete()" />
                </div>
            </UForm>
        </template>
    </UCard>
</template>

<script setup lang="ts">
import { useClient } from '@/composables/client';
import type { FormSubmitEvent } from '@nuxt/ui';
import { onMounted, reactive } from 'vue';


type Props = {
    id?: string
}

const $emits = defineEmits(['created', 'updated', 'deleted'])

const props = defineProps<Props>()

const client = useClient()


const state = reactive({
    name: '',
    description: ''
})

const toast = useToast()

async function onSubmit(event: FormSubmitEvent<{ name: string, description: string }>) {
    try {
        if (props.id) {
            const res = await client.updateConsentCategory(props.id, {
                name: event.data.name,
                description: event.data.description
            })
            console.log(res)
            $emits('updated')
        } else {
            const res = await client.createConsentCategory(event.data.name)
            console.log(res)
            $emits('created')
        }
    } catch (error) {
        console.log(error)
    }
}

async function onDelete() {
    try {
        if (props.id) {
            const res = await client.deleteConsentCategory(props.id)
            if (res == true) {
                $emits('deleted')
            } else if (res == 0) {
                toast.add({
                    title: 'Standard Kategorie kann nicht gelöscht werden',
                    icon: 'i-heroicons-exclamation-triangle',
                    color: 'error'
                })
            }
        }
    } catch (error) {
        console.log(error)
    }
}

onMounted(async () => {
    if (!props.id) {
        return
    }
    try {
        const res = await client.getConsentCategory(props.id)
        console.log(res)
        state.name = res.name
        state.description = res.description
    } catch (error) {
        console.error(error)
    }
})
</script>

<style scoped></style>