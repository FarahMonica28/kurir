<script setup lang="ts">
import { ref } from 'vue';

// Komponen halaman
import DashboardHome from '@/pages/dashboardk/beranda.vue' // ← Tambahkan file ini
import Check from "@/pages/dashboard/transaksii/ongkir/ongkir.vue";
// import OrderPage from "@/pages/dashboard/transaksi/order/index.vue";
import RiwayatPage from "@/pages/dashboard/transaksii/riwayat/index.vue";
import OrdePage from "@/pages/dashboard/transaksii/order/index.vue";
import TrackPage from "@/pages/dashboard/tracking/index.vue";
// import RiwayatPage from "@/pages/dashboard/transaksi/riwayatt/idex.vue";

// Tab aktif
const currentTab = ref<'dashboard' | 'ongkir' | 'order' | 'orde' | 'riwayat' | 'riwayatt' | 'tracking'>('dashboard');

// Fungsi Logout
// const handleLogout = () => {
//   alert('Logout berhasil') // Ganti dengan logika asli
// }
</script>

<template>
  <div>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
      <div class="d-flex align-items-center w-100 justify-content-between">
        <!-- Tab-tombol sejajar -->
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-outline-light" :class="{ 'fw-bold': currentTab === 'dashboard' }"
            @click="currentTab = 'dashboard'">
            Dashboard Pengguna
          </button>
          <!-- <button
        class="btn btn-outline-light"
        :class="{ 'fw-bold': currentTab === 'order' }"
        @click="currentTab = 'order'"
        >
        Order Khusus Surabaya
      </button> -->
          <!-- <button
        class="btn btn-outline-light"
        :class="{ 'fw-bold': currentTab === 'riwayat' }"
        @click="currentTab = 'riwayat'"
      >
        Riwayat
      </button> -->
          <button class="btn btn-outline-light" :class="{ 'fw-bold': currentTab === 'ongkir' }"
            @click="currentTab = 'ongkir'">
            Check Ongkir
          </button>
          <button class="btn btn-outline-light" :class="{ 'fw-bold': currentTab === 'orde' }"
            @click="currentTab = 'orde'">
            Order Kurir
          </button>
          <button class="btn btn-outline-light" :class="{ 'fw-bold': currentTab === 'riwayatt' }"
            @click="currentTab = 'riwayatt'">
            Riwayat
          </button>
          <button class="btn btn-outline-light" :class="{ 'fw-bold': currentTab === 'tracking' }"
            @click="currentTab = 'tracking'">
            Lacak Barang
          </button>
        </div>

        <!-- Tambahkan bagian login di kanan -->
        <ul class="navbar-nav ms-auto"> <!-- gunakan ml-auto untuk Bootstrap 4 -->
          <li class="nav-item">
            <router-link to="/" class="nav-link">
              Login
            </router-link>
          </li>
        </ul>
        <!-- Tombol Logout
        <button class="btn btn-light text-primary fw-semibold" @click="handleLogout">
          Logout
        </button> -->
      </div>
    </nav>



    <!-- Konten -->
    <div class="container py-4">
      <DashboardHome v-if="currentTab === 'dashboard'" />
      <Check v-if="currentTab === 'ongkir'" />
      <!-- <OrderPage v-if="currentTab === 'order'" /> -->
      <OrdePage v-if="currentTab === 'orde'" />
      <!-- <RiwayatPage v-if="currentTab === 'riwayat'" /> -->
      <RiwayatPage v-if="currentTab === 'riwayatt'" />
      <TrackPage v-if="currentTab === 'tracking'" />
    </div>
  </div>
</template>

<style scoped>
.btn-outline-light.fw-bold {
  border: 2px solid white;
}

body {
  background-color: white;
}

.btn.fw-bold {
  font-weight: bold;
}

/* button{
   background-color: blue; 
} */
 .navbar {
  padding-left: 1rem;
  padding-right: 1rem;
}

/* Style untuk tombol Login */
.navbar .nav-link {
  font-weight: 500;
  color: #fff;
}

.navbar .nav-link:hover {
  color: #ddd;
}

/* Jika ingin tombol mirip btn */
.navbar .nav-link.btn-login {
  padding: 6px 12px;
  border: 1px solid #fff;
  border-radius: 4px;
}

.navbar .nav-link.btn-login:hover {
  background-color: rgba(255,255,255,0.2);
}
</style>
