<script setup lang="ts">
import { ref } from 'vue';

// Komponen halaman
import DashboardHome from '@/pages/dashboardk/beranda.vue' // ← Tambahkan file ini
import OrderPage from "@/pages/dashboard/transaksi/index.vue";
import RiwayatPage from "@/pages/dashboard/riwayatt/idex.vue";

// Tab aktif
const currentTab = ref<'dashboard' | 'order' | 'riwayat'>('dashboard');

// Fungsi Logout
const handleLogout = () => {
  alert('Logout berhasil') // Ganti dengan logika asli
}
</script>

<template>
  <div>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
      <div class="d-flex align-items-center w-100 justify-content-between">
        <!-- Tab-tombol sejajar -->
        <div class="d-flex align-items-center gap-2">
          <button
            class="btn btn-outline-light"
            :class="{ 'fw-bold': currentTab === 'dashboard' }"
            @click="currentTab = 'dashboard'"
          >
            Dashboard Pengguna
          </button>
          <button
            class="btn btn-outline-light"
            :class="{ 'fw-bold': currentTab === 'order' }"
            @click="currentTab = 'order'"
          >
            Order
          </button>
          <button
            class="btn btn-outline-light"
            :class="{ 'fw-bold': currentTab === 'riwayat' }"
            @click="currentTab = 'riwayat'"
          >
            Riwayat
          </button>
        </div>

        <!-- Tombol Logout -->
        <button class="btn btn-light text-primary fw-semibold" @click="handleLogout">
          Logout
        </button>
      </div>
    </nav>


    
    <!-- Konten -->
    <div class="container py-4">
      <DashboardHome v-if="currentTab === 'dashboard'" />
      <OrderPage v-if="currentTab === 'order'" />
      <RiwayatPage v-if="currentTab === 'riwayat'" />
    </div>
  </div>
</template>

<style scoped>
.btn-outline-light.fw-bold {
  border: 2px solid white;
}
</style>
