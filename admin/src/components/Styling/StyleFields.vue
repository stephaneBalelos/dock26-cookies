<template>
    <UAccordion type="multiple" :items="items" :default-value="['0']" :ui="{
        trigger: 'py-3 text-xs font-semibold uppercase tracking-wide text-muted',
        body: 'pb-4 space-y-2',
    }">
        <template #body="{ item }">
            <UFormField v-for="field in fieldsOf(item.value)" :key="field.key" :label="fieldLabel(field)"
                orientation="horizontal" size="sm" :ui="{ root: 'items-center', label: 'font-normal' }">
                <UInputNumber v-if="field.type === 'size'" :model-value="sizeValue(field.key)" :min="0" size="sm"
                    class="w-44" @update:model-value="(value: number | null | undefined) => setSizeValue(field.key, value)" />
                <ColorField v-else :model-value="modelValue[field.key] ?? field.default" :default-value="field.default"
                    :label="field.label" :format="field.format"
                    @update:model-value="(value: string) => update(field.key, value)" />
            </UFormField>
        </template>
    </UAccordion>
</template>

<script setup lang="ts">
import type { AccordionItem } from '@nuxt/ui';
import ColorField from '@/components/Styling/ColorField.vue';
import { STYLE_GROUPS, type StyleField } from '@/styleFields';
import type { CustomStyles } from '../../../../types';

const props = defineProps<{
    modelValue: CustomStyles
}>()

const $emits = defineEmits<{
    (event: 'update:modelValue', styles: CustomStyles): void
}>()

const items: AccordionItem[] = STYLE_GROUPS.map((group, index) => ({
    label: group.label,
    value: String(index),
}))

function fieldsOf(value: string | undefined): StyleField[] {
    return STYLE_GROUPS[Number(value)]?.fields ?? []
}

function fieldLabel(field: StyleField): string {
    return field.type === 'size' ? `${field.label} (px)` : field.label
}

function update(key: string, value: string) {
    $emits('update:modelValue', { ...props.modelValue, [key]: value })
}

// Sizes are stored as `8px` strings like every other value, so the number input
// converts on the way in and out.
function sizeValue(key: string): number {
    return parseFloat(props.modelValue[key] ?? '0')
}

function setSizeValue(key: string, value: number | null | undefined) {
    update(key, `${value ?? 0}px`)
}
</script>
