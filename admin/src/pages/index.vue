<template>
    <UPage :ui="{
        center: 'lg:col-span-7',
        right: 'lg:col-span-3'
    }">
        <UPageBody class="mt-0">
            <h2>Darstellung</h2>
            <div class="grid grid-cols-3 gap-4">
                <div class="">
                    <UPageCard title="Einwilligung Popup"
                        description="Nuxt UI integrates with latest Tailwind CSS, bringing significant improvements."
                        class="h-full">
                        <div class="flex flex-col">
                            <div class="flex-1 mb-4">
                                <ConsentModalOptions />
                            </div>
                            <UButton :icon="'i-heroicons-eye'" color="neutral" variant="outline"
                                @click="() => $consentConfig.showConsentModal()">
                                Einwiligung Modal anzeigen
                            </UButton>
                        </div>
                    </UPageCard>
                </div>
                <div>
                    <UPageCard title="Einwilligung Popup"
                        description="Nuxt UI integrates with latest Tailwind CSS, bringing significant improvements."
                        class="h-full">
                        <div class="flex flex-col">
                            <div class="flex-1 mb-4">
                                dsa
                            </div>
                            <UButton :icon="'i-heroicons-eye'" color="neutral" variant="outline">
                                Modal Anzeigen
                            </UButton>
                        </div>
                    </UPageCard>
                </div>
            </div>
        </UPageBody>
        <template #right>
            <div>
                <h2>Cookie Kategorien</h2>
                <UAccordion ref="accordionRef" v-model="activeCategory" :items="accordionsItems" :ui="{
                    leadingIcon: 'handle'
                }">
                    <template #body="{ item }">
                        <EditConsentCategoryForm :id="item.id" @updated="() => {
                            activeCategory = undefined
                            loadCategories()
                        }" @deleted="() => loadCategories()" />
                    </template>
                </UAccordion>

                <div v-if="!activeCategory && isAddingNewCategory" class="py-4">
                    <EditConsentCategoryForm @created="() => {
                        isAddingNewCategory = false
                        loadCategories()
                    }" />
                </div>
                <div v-if="!activeCategory" class="py-4 flex justify-center">
                    <UButton v-if="!isAddingNewCategory" label="New Cookie Kategorie" :icon="'i-heroicons-plus-circle'"
                        color="neutral" variant="outline" @click="isAddingNewCategory = true" />
                    <UButton v-if="isAddingNewCategory" label="Abbrechen" color="neutral" variant="ghost"
                        @click="isAddingNewCategory = false" />
                </div>
            </div>
        </template>
    </UPage>
</template>

<script setup lang="ts">

import ConsentModalOptions from '@/components/Customizer/ConsentModalOptions.vue';
import EditConsentCategoryForm from '@/components/EditConsentCategoryForm.vue';
import { useClient } from '@/composables/client';
import { useConsentConfig } from '@/composables/useConsentConfig';
import type { AccordionItem } from '@nuxt/ui';
import { nextTick, onMounted, onUnmounted, ref, shallowRef, useTemplateRef } from 'vue'
import { moveArrayElement, useSortable } from '@vueuse/integrations/useSortable'
import { watchArray } from '@vueuse/core';


const client = useClient()

const $consentConfig = useConsentConfig()

const isAddingNewCategory = ref(false)
const activeCategory = ref()

const accordionsItems = shallowRef<AccordionItem[]>([])

watchArray($consentConfig.consentCategories, () => {
    const categories = $consentConfig.consentCategories.value
    accordionsItems.value = categories.map((cat) => {
        const item: AccordionItem = {
            label: cat.name,
            icon: 'i-heroicons-bars-2',
            id: cat.id
        }
        console.log(item)
        return item
    })
})

const accrodionRef = useTemplateRef<HTMLElement>('accordionRef')

useSortable(accrodionRef, accordionsItems, {
    animation: 300,
    handle: '.handle',
    onUpdate: (e: any) => {
        moveArrayElement(accordionsItems, e.oldIndex, e.newIndex, e)
        nextTick(() => {
            updateCategoryOrder(accordionsItems.value.map(item => item.id))
        })
    }
})

async function loadCategories() {
    try {
        const res = await client.getConsentCategories()
        console.log(res)
        $consentConfig.init({
            categories: res
        })
    } catch (err) {
        console.error(err)
    }
}

async function updateCategoryOrder(order: number[]) {
    try {
        console.log(order)
        await client.updateConsentCategoriesOrder(order)
    } catch (error) {
        console.error(error)
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