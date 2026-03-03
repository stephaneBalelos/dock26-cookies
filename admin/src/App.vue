<script setup lang="ts">
import { ref, computed, inject } from 'vue'
import Navbar from '@/components/Navbar.vue';
import Home from '@/pages/index.vue';
import Settings from '@/pages/settings.vue';

const apiUrl = inject('apiUrl', '')
const nonce = inject('nonce', '')

if (!apiUrl || !nonce) {
  console.error('API URL or nonce not provided')
  throw new Error('API URL or nonce not provided')
}

const currentPath = ref<string>(window.location.hash || '/')
window.addEventListener('hashchange', () => {
  currentPath.value = window.location.hash
  console.log('Current path:', currentPath.value) // Debugging log
})

const currentView = computed(() => {
  const path = currentPath.value.replace('#', '') || '/'
  switch (path) {
    case '/':
      return Home
    case '/settings':
      return Settings
    default:
      return Home
  }
})

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
