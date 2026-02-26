<script setup lang="ts">
import { ref, computed } from 'vue'
import Navbar from '@/components/Navbar.vue';
import Home from '@/pages/index.vue';
import Settings from '@/pages/settings.vue';

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
    <Navbar />
    <h1>Dock26 Cookies Admin</h1>
    <component :is="currentView" />
</template>
