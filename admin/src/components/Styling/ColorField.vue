<template>
    <UPopover :content="{ align: 'end', sideOffset: 6 }" @update:open="onOpenChange">
        <UButton color="neutral" variant="outline" size="sm" class="w-44 shrink-0 justify-start font-mono"
            :aria-label="`${label}: ${modelValue}`">
            <template #leading>
                <span class="swatch">
                    <span :style="{ background: modelValue }" />
                </span>
            </template>
            <span class="truncate">{{ modelValue }}</span>
        </UButton>

        <template #content>
            <div class="w-64 p-3 space-y-3">
                <UColorPicker :model-value="pickerValue" size="sm" class="mx-auto" @update:model-value="onPick" />

                <div v-if="rgba" class="flex items-center gap-2">
                    <span class="text-xs text-muted shrink-0">Deckkraft</span>
                    <USlider :model-value="alphaPercent" :min="0" :max="100" size="sm" class="flex-1"
                        @update:model-value="onAlpha" />
                    <span class="text-xs tabular-nums w-9 text-end">{{ alphaPercent }}%</span>
                </div>

                <UInput v-model="text" size="sm" class="w-full" :color="textValid ? 'primary' : 'error'"
                    :highlight="!textValid" :ui="{ base: 'font-mono' }" aria-label="Farbwert" />

                <UButton size="xs" color="neutral" variant="ghost" icon="i-heroicons-arrow-uturn-left"
                    class="w-full" :title="`Standard: ${defaultValue}`" :disabled="modelValue === defaultValue"
                    @click="commit(defaultValue)">
                    Standard
                    <span class="ms-auto flex items-center gap-1.5 min-w-0">
                        <span class="swatch">
                            <span :style="{ background: defaultValue }" />
                        </span>
                        <span class="font-mono truncate">{{ defaultValue }}</span>
                    </span>
                </UButton>
            </div>
        </template>
    </UPopover>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { COLOR_PATTERN } from '@/styleFields';

type Rgba = { r: number; g: number; b: number; a: number }

const props = withDefaults(defineProps<{
    modelValue: string
    defaultValue: string
    label: string
    // Notation written back when the picker or the opacity slider is used.
    format?: 'hex' | 'rgb'
}>(), {
    format: 'hex',
})

const $emits = defineEmits<{
    (event: 'update:modelValue', value: string): void
}>()

// The text field is edited freely; only values the server accepts reach the model.
const text = ref(props.modelValue)
const textValid = computed(() => COLOR_PATTERN.test(text.value.trim()))

watch(() => props.modelValue, (value) => {
    if (value !== text.value.trim()) {
        text.value = value
    }
})

watch(text, (value) => {
    const color = value.trim()
    if (COLOR_PATTERN.test(color)) {
        commit(color)
    }
})

// Hex and rgb() values are split into channels so the alpha survives picking a
// new color (UColorPicker has no alpha channel). hsl() values and anything else
// the server accepts are passed through and can only be replaced as a whole.
const rgba = computed(() => parseColor(props.modelValue))
const pickerValue = computed(() => rgba.value ? formatColor({ ...rgba.value, a: 1 }, 'hex') : props.modelValue)
const alphaPercent = computed(() => Math.round((rgba.value?.a ?? 1) * 100))

function onPick(value: string | undefined) {
    const picked = value ? parseColor(value) : null
    if (picked) {
        commit(formatColor({ ...picked, a: rgba.value?.a ?? 1 }, props.format))
    }
}

function onAlpha(value: number | number[] | undefined) {
    const percent = Array.isArray(value) ? value[0] : value
    if (rgba.value && percent !== undefined) {
        commit(formatColor({ ...rgba.value, a: percent / 100 }, props.format))
    }
}

function commit(value: string) {
    if (value !== props.modelValue) {
        $emits('update:modelValue', value)
    }
}

// Drop an invalid, uncommitted text when the dropdown closes.
function onOpenChange(open: boolean) {
    if (!open) {
        text.value = props.modelValue
    }
}

function parseColor(value: string): Rgba | null {
    const color = value.trim().toLowerCase()
    if (color === 'transparent') {
        return { r: 0, g: 0, b: 0, a: 0 }
    }

    const hex = /^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/.exec(color)?.[1]
    if (hex) {
        const full = hex.length <= 4 ? [...hex].map(digit => digit + digit).join('') : hex
        const channel = (index: number) => parseInt(full.slice(index, index + 2), 16)
        return {
            r: channel(0),
            g: channel(2),
            b: channel(4),
            a: full.length === 8 ? channel(6) / 255 : 1,
        }
    }

    const rgb = /^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+)\s*)?\)$/.exec(color)
    if (rgb) {
        const channel = (index: number) => Math.min(255, Number(rgb[index]))
        return {
            r: channel(1),
            g: channel(2),
            b: channel(3),
            a: rgb[4] === undefined ? 1 : Math.min(1, Number(rgb[4])),
        }
    }

    return null
}

// Produces only notations COLOR_PATTERN accepts: an rgba() alpha of 0, 1 or 0.xx.
function formatColor({ r, g, b, a }: Rgba, format: 'hex' | 'rgb'): string {
    const alpha = Math.round(a * 100) / 100
    if (format === 'rgb') {
        return alpha < 1 ? `rgba(${r}, ${g}, ${b}, ${alpha})` : `rgb(${r}, ${g}, ${b})`
    }

    const hex = (channel: number) => channel.toString(16).padStart(2, '0')
    return `#${hex(r)}${hex(g)}${hex(b)}${alpha < 1 ? hex(Math.round(alpha * 255)) : ''}`
}
</script>

<style scoped>
/* Checkerboard behind the color, so transparency is visible. */
.swatch {
    display: inline-block;
    flex-shrink: 0;
    width: 1rem;
    height: 1rem;
    border-radius: 0.25rem;
    overflow: hidden;
    background: repeating-conic-gradient(#d4d4d8 0 25%, #ffffff 0 50%) 50% / 8px 8px;
}

.swatch > span {
    display: block;
    width: 100%;
    height: 100%;
    box-shadow: inset 0 0 0 1px rgb(0 0 0 / 0.15);
}
</style>
