<template>
    <div class="">
        Home

        <UForm :state="state" class="space-y-4" @submit="onSubmit">
            <UFormField label="Name" name="name">
                <UInput v-model="state.name" />
            </UFormField>

            <UButton type="submit">
                Submit
            </UButton>
        </UForm>

        <ul class="mt-8">
            <li v-for="term in categories">
                {{ term.name }} <EditConsentCategoryForm :id="term.term_id" @updated="loadCategories" />  <UButton @click="() => deleteTerm(term.term_id)">Delete</UButton>
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">

import { useClient } from '@/composables/client';
import type { FormSubmitEvent } from '@nuxt/ui';
import { onMounted, onUnmounted, reactive, ref } from 'vue'

const categories = ref([])
const client = useClient()

const state = reactive({
    name: ''
})

async function onSubmit(event: FormSubmitEvent<{ name: string }>) {
    console.log(event.data)
    try {
        const res = await client.createConsentCategory(event.data.name)
        console.log(res)
        loadCategories()
    } catch (error) {
        console.log(error)
    }
}

async function deleteTerm(id: string) {
    try {
        const res = await client.deleteConsentCategory(id)
        console.log(res)
        loadCategories()
    } catch (error) {
        console.error(error)
    }
}

async function loadCategories() {
        try {
        const res = await client.getConsentCategories()
        console.log(res)
        categories.value = res
    } catch (err) {
        console.error(err)
    }
}

onMounted(async () => {
    await loadCategories()
})

onUnmounted(() => {
    console.log('Unmounted')
})
</script>

<style scoped></style>