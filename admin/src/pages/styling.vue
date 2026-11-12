<template>
    <div v-if="config && guiDraft" class="grid grid-cols-1 gap-4
        lg:grid-cols-[minmax(0,1fr)_420px] lg:grid-rows-[minmax(0,1fr)] lg:min-h-[560px]
        lg:h-[calc(100vh_-_var(--wp-admin--admin-bar--height,32px)_-_var(--ui-header-height)_-_2rem)]">
        <StylePreview v-model:view="view" :config="config" :gui-options="guiDraft" :styles="stylesDraft"
            class="h-[560px] lg:h-full" />

        <UCard :ui="{
            root: 'flex flex-col overflow-hidden',
            header: 'shrink-0 px-4 py-3 sm:px-4',
            body: 'flex-1 min-h-0 overflow-y-auto p-0 sm:p-0',
        }">
            <template #header>
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-lg font-bold">Styling</span>
                        <UBadge v-if="isDirty" color="warning" variant="subtle" size="sm">Ungespeichert</UBadge>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <UButton color="neutral" variant="ghost" size="sm" :disabled="!isDirty || isLoading"
                            @click="discard">
                            Zurücksetzen
                        </UButton>
                        <UButton size="sm" :disabled="!isDirty" :loading="isLoading" @click="onSave">
                            Speichern
                        </UButton>
                    </div>
                </div>
            </template>

            <section class="px-4 py-4 border-b border-default">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted mb-3">Layout & Position</p>
                <LayoutFields v-model="guiDraft" />
            </section>

            <StyleFields v-model="stylesDraft" class="px-4" />
        </UCard>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import LayoutFields from '@/components/Styling/LayoutFields.vue';
import StyleFields from '@/components/Styling/StyleFields.vue';
import StylePreview from '@/components/Styling/StylePreview.vue';
import { useConsentConfig } from '@/composables/useConsentConfig';
import { useSaveSection } from '@/composables/useSaveSection';
import { clone, STYLE_DEFAULTS, withStyleDefaults } from '@/styleFields';
import type { CookieConfig, CustomStyles } from '../../../types';

const $consentConfig = useConsentConfig()

const { save } = useSaveSection()

const config = computed(() => $consentConfig.config.value)
const isLoading = computed(() => $consentConfig.isLoading.value)

// What the server has stored; replaced by useSaveSection after every save.
const savedGui = computed(() => $consentConfig.config.value?.guiOptions ?? null)
const savedStyles = computed(() => withStyleDefaults($consentConfig.customStyles.value))

// Unsaved edits, shown live in the preview.
const guiDraft = ref<CookieConfig['guiOptions'] | null>(clone(savedGui.value))
const stylesDraft = ref<CustomStyles>(clone(savedStyles.value))

const view = ref<'consent' | 'preferences'>('consent')

const guiDirty = computed(() => JSON.stringify(guiDraft.value) !== JSON.stringify(savedGui.value))
const stylesDirty = computed(() =>
    Object.keys(STYLE_DEFAULTS).some(key => stylesDraft.value[key] !== savedStyles.value[key])
)
const isDirty = computed(() => guiDirty.value || stylesDirty.value)

// Only the changed sections are sent. A failed save leaves its draft dirty.
async function onSave() {
    if (guiDirty.value && guiDraft.value) {
        await save('gui', clone(guiDraft.value), 'Layout gespeichert')
    }
    if (stylesDirty.value) {
        await save('custom-styles', { ...stylesDraft.value }, 'Stile gespeichert')
    }
}

function discard() {
    guiDraft.value = clone(savedGui.value)
    stylesDraft.value = clone(savedStyles.value)
}
</script>
