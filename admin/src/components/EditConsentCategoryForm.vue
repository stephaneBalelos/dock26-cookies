<template>
    <UModal     :close="{
      color: 'primary',
      variant: 'outline',
      class: 'rounded-full'
    }">
        <UButton label="Open" color="neutral" variant="subtle" />

        <template #content>
            <UForm :state="state" class="space-y-4" @submit="onSubmit">
                <UFormField label="Name" name="name">
                    <UInput v-model="state.name" />
                </UFormField>

                <UButton type="submit">
                    Submit
                </UButton>
            </UForm>
        </template>
    </UModal>
</template>

<script setup lang="ts">
import { useClient } from '@/composables/client';
import type { FormSubmitEvent } from '@nuxt/ui';
import { onMounted, reactive } from 'vue';


type Props = {
    id: string
}

const $emits = defineEmits(['updated'])

const props = defineProps<Props>()

const client = useClient()

const overlays = useOverlay()

const state = reactive({
    name: ''
})

async function onSubmit(event: FormSubmitEvent<{ name: string }>) {
    console.log(event.data)
    try {
        const res = await client.updateConsentCategory(props.id, event.data.name)
        console.log(res)
        $emits('updated')
        overlays.closeAll()
    } catch (error) {
        console.log(error)
    }
}

onMounted(async () => {
    try {
        const res = await client.getConsentCategory(props.id)
        console.log(res)
        state.name = res.name
    } catch (error) {
        console.error(error)
    }
})
</script>

<style scoped></style>