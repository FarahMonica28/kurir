<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from '@/libs/axios';
import type { User } from "@/types";
// Import store autentikasi untuk mengambil data user yang sedang login
import { useAuthStore } from "@/stores/auth";

// Inisialisasi store agar bisa akses data user yang login
const store = useAuthStore();
const User = ref({
    name: "",
});
const getProfile = async () => {
    console.log(store.user)
    User.value = {
        name: store.user.name
    };
};


onMounted(() => {
    getProfile();
});

</script>

<template>
  <main>
    <h1>Selamat datang, {{ User.name }}👋🏻</h1>
  </main>
</template>

<style>


</style>
