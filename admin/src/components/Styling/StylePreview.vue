<template>
    <UCard :ui="{
        root: 'flex flex-col overflow-hidden',
        header: 'shrink-0 px-3 py-2 sm:px-3',
        body: 'relative flex-1 min-h-0 p-0 sm:p-0',
    }">
        <template #header>
            <div class="flex items-center justify-between gap-2">
                <UTabs :items="tabs" :model-value="view" :content="false" size="sm" class="w-auto"
                    @update:model-value="onTabChange" />
                <div class="flex items-center gap-1">
                    <UButton v-for="option in devices" :key="option.value" size="xs" color="neutral"
                        :variant="device === option.value ? 'soft' : 'ghost'" :icon="option.icon"
                        :aria-label="option.label" :aria-pressed="device === option.value" :title="option.label"
                        @click="device = option.value" />
                    <UButton size="xs" color="neutral" variant="ghost" icon="i-heroicons-arrow-path" @click="render()">
                        Neu anzeigen
                    </UButton>
                </div>
            </div>
        </template>

        <div ref="stage" class="absolute inset-0 overflow-hidden" :class="{ 'bg-elevated': device === 'mobile' }">
            <iframe ref="frame" :srcdoc="SRCDOC" title="Vorschau des Cookie-Banners" :style="frameStyle"
                class="absolute top-0 border-0 bg-white origin-top-left"
                :class="{ 'shadow-lg ring ring-default': device === 'mobile' }" @load="onFrameLoad" />
        </div>

        <div v-if="modalHidden" class="absolute inset-0 grid place-items-center bg-default/30">
            <UButton icon="i-heroicons-eye" @click="render()">Vorschau erneut anzeigen</UButton>
        </div>
    </UCard>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { useElementSize } from '@vueuse/core';
import type { TabsItem } from '@nuxt/ui';
import type { CookieConsentConfig } from 'vanilla-cookieconsent';
// The preview runs its own copy of the library inside the iframe, so the
// modals, their <html> classes and their focus handling stay out of the admin page.
import ccScript from 'vanilla-cookieconsent/dist/cookieconsent.umd.js?raw';
import ccCss from 'vanilla-cookieconsent/dist/cookieconsent.css?inline';
import { PREVIEW_COOKIE_NAME } from '@/composables/useConsentConfig';
import { clone, customStylesCss } from '@/styleFields';
import type { CookieConfig, CustomStyles } from '../../../../types';

type PreviewView = 'consent' | 'preferences'

type PreviewWindow = Window & typeof globalThis & {
    CookieConsent?: typeof import('vanilla-cookieconsent')
}

const props = defineProps<{
    config: CookieConfig
    guiOptions: CookieConfig['guiOptions']
    styles: CustomStyles
}>()

const view = defineModel<PreviewView>('view', { required: true })

const tabs: TabsItem[] = [
    { label: 'Einwilligungs-Modal', value: 'consent' },
    { label: 'Präferenzen-Modal', value: 'preferences' },
]

// A neutral stand-in for a website, so the overlay and the modal position are visible.
const PAGE_CSS = `
*{box-sizing:border-box}
html,body{margin:0;min-height:100%}
body{background:#f4f5f7;font-family:system-ui,sans-serif}
.site-header{display:flex;align-items:center;justify-content:space-between;height:56px;padding:0 32px;background:#fff;border-bottom:1px solid #e5e7eb}
.logo{width:96px;height:20px;border-radius:4px;background:#d1d5db}
.nav{display:flex;gap:16px}
.nav span{width:56px;height:10px;border-radius:5px;background:#e5e7eb}
.content{max-width:960px;margin:0 auto;padding:32px}
.hero{height:200px;border-radius:12px;background:linear-gradient(135deg,#e5e7eb,#d1d5db)}
.lines{display:grid;gap:12px;margin:28px 0}
.lines span{height:10px;border-radius:5px;background:#e5e7eb}
.lines span:nth-child(3n){width:70%}
.lines span:nth-child(4n){width:85%}
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.cards div{height:120px;border-radius:10px;background:#fff;border:1px solid #e5e7eb}
`

// The library's stylesheet and script are inserted on load (see onFrameLoad),
// which keeps them out of HTML parsing.
const SRCDOC = `<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>${PAGE_CSS}</style>
<style id="cc-vendor"></style>
<style id="cc-custom"></style>
</head>
<body>
<header class="site-header"><span class="logo"></span><span class="nav"><span></span><span></span><span></span></span></header>
<div class="content">
<div class="hero"></div>
<div class="lines"><span></span><span></span><span></span><span></span><span></span><span></span></div>
<div class="cards"><div></div><div></div><div></div></div>
</div>
</body>
</html>`

const frame = ref<HTMLIFrameElement | null>(null)
const stage = ref<HTMLElement | null>(null)
const modalHidden = ref(false)

type Device = 'desktop' | 'mobile'

const devices: { value: Device; label: string; icon: string }[] = [
    { value: 'desktop', label: 'Desktop-Ansicht', icon: 'i-heroicons-computer-desktop' },
    { value: 'mobile', label: 'Mobil-Ansicht', icon: 'i-heroicons-device-phone-mobile' },
]

const device = ref<Device>('desktop')

// The library switches to its mobile layouts below 640px. The desktop view
// therefore renders at least 1024px wide and is scaled down to fit the box.
const DESKTOP_MIN_WIDTH = 1024
const MOBILE_WIDTH = 390

const { width: stageWidth, height: stageHeight } = useElementSize(stage)

const frameStyle = computed(() => {
    const width = stageWidth.value
    const height = stageHeight.value
    if (!width || !height) {
        return { width: '100%', height: '100%' }
    }

    const virtualWidth = device.value === 'desktop' ? Math.max(width, DESKTOP_MIN_WIDTH) : MOBILE_WIDTH
    const scale = Math.min(1, width / virtualWidth)

    return {
        width: `${virtualWidth}px`,
        height: `${height / scale}px`,
        left: `${(width - virtualWidth * scale) / 2}px`,
        transform: scale < 1 ? `scale(${scale})` : undefined,
    }
})

let previewWindow: PreviewWindow | null = null
let observer: MutationObserver | null = null
let rendering = false
let renderId = 0

function onFrameLoad() {
    const win = frame.value?.contentWindow as PreviewWindow | null | undefined
    const doc = frame.value?.contentDocument
    if (!win || !doc) {
        return
    }

    const vendorStyles = doc.getElementById('cc-vendor')
    if (vendorStyles) {
        vendorStyles.textContent = ccCss
    }

    // The library focuses a modal whenever it opens one. Only allow that while
    // the preview itself has focus, so a re-render never pulls the focus out of
    // the style panel.
    const nativeFocus = win.HTMLElement.prototype.focus
    win.HTMLElement.prototype.focus = function (this: HTMLElement, options?: FocusOptions) {
        if (doc.hasFocus()) {
            nativeFocus.call(this, options)
        }
    }

    // Footer links must not navigate the preview away.
    doc.addEventListener('click', (event) => {
        if (event.target instanceof win.Element && event.target.closest('a[href]')) {
            event.preventDefault()
        }
    }, true)

    // The library marks the open modal with a class on <html>; follow it to
    // keep the tabs in sync and to notice when the modal was closed.
    observer?.disconnect()
    observer = new win.MutationObserver(syncModalState)
    observer.observe(doc.documentElement, { attributes: true, attributeFilter: ['class'] })

    const script = doc.createElement('script')
    script.textContent = ccScript
    doc.head.append(script)

    previewWindow = win
    applyStyles()
    render()
}

function applyStyles() {
    const element = previewWindow?.document.getElementById('cc-custom')
    if (element) {
        element.textContent = customStylesCss(props.styles)
    }
}

/** Opens the requested modal from scratch with the current draft. */
async function render(target: PreviewView = view.value) {
    const cookieConsent = previewWindow?.CookieConsent
    if (!cookieConsent) {
        return
    }

    const id = ++renderId
    rendering = true
    try {
        cookieConsent.reset(true)
        await cookieConsent.run(runOptions())
        if (id !== renderId) {
            return
        }
        if (target === 'preferences') {
            cookieConsent.showPreferences()
        } else {
            cookieConsent.show(true)
        }
    } finally {
        if (id === renderId) {
            rendering = false
            syncModalState()
        }
    }
}

function syncModalState() {
    const classes = previewWindow?.document.documentElement.classList
    if (rendering || !classes) {
        return
    }

    if (classes.contains('show--preferences')) {
        view.value = 'preferences'
    } else if (classes.contains('show--consent')) {
        view.value = 'consent'
    }
    modalHidden.value = !classes.contains('show--preferences') && !classes.contains('show--consent')
}

function runOptions(): CookieConsentConfig {
    const config = clone(props.config)

    // Without a configured privacy/imprint URL the consent modal has no footer,
    // which would leave the footer colors without anything to preview.
    const { privacy_btn: privacy, imprint_btn: imprint } = config.settings
    const placeholderFooter = `<a href="#">${escapeHtml(privacy.label)}</a> <a href="#">${escapeHtml(imprint.label)}</a>`
    for (const translation of Object.values(config.language.translations)) {
        translation.consentModal.footer ||= placeholderFooter
    }

    return {
        // Same options as the frontend (frontend/src/main.ts), but with the
        // preview's own cookie and without bot detection or auto show.
        cookie: { name: PREVIEW_COOKIE_NAME },
        autoShow: false,
        hideFromBots: false,
        disablePageInteraction: true,
        categories: config.categories,
        guiOptions: clone(props.guiOptions),
        language: config.language,
    }
}

function escapeHtml(text: string): string {
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
}

function onTabChange(value: string | number) {
    const target: PreviewView = value === 'preferences' ? 'preferences' : 'consent'
    view.value = target
    render(target)
}

// Colors and sizes only swap the stylesheet; everything that changes the
// markup opens the modal again.
watch(() => props.styles, applyStyles, { deep: true })
watch(() => props.guiOptions, () => render(), { deep: true })
watch(() => props.config, () => render())

onBeforeUnmount(() => {
    observer?.disconnect()
    previewWindow = null
})
</script>
