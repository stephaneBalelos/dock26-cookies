<script setup lang="ts">
import { ref, computed } from 'vue'
import Navbar from '@/components/Navbar.vue';
import Home from '@/pages/index.vue';
import Settings from '@/pages/settings.vue';
import Styling from '@/pages/styling.vue';
import { useConsentConfig } from './composables/useConsentConfig';

const adminData = window.dock26CookiesAdmin

if (!adminData?.apiUrl || !adminData.nonce || !adminData.config) {
  throw new Error('Dock26 Cookies: Die Admin-Daten (window.dock26CookiesAdmin) fehlen.')
}

const currentPath = ref<string>(window.location.hash)
window.addEventListener('hashchange', () => {
  currentPath.value = window.location.hash
})

const pages = {
  '#/styling': Styling,
  '#/settings': Settings,
} as const

const currentView = computed(() => pages[currentPath.value as keyof typeof pages] ?? Home)

useConsentConfig().init(adminData)
</script>

<template>
  <UApp>
    <div class="flex flex-col min-h-screen bg-gray-100">
      <Navbar />
      <main class="flex-1 p-4 overflow-auto">
        <component :is="currentView" />
      </main>
    </div>
  </UApp>

</template>
