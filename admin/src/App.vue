<script setup lang="ts">
import { ref } from 'vue'
import Navbar from '@/components/Navbar.vue';
const count = ref(0)
</script>

<template>
    <Navbar />
    <h1>Dock26 Cookies Admin</h1>
    <div class="card">
        <button @click="count++">Count is {{ count }}</button>
    </div>
</template>
