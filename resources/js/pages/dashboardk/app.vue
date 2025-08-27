<template>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-9">
        <div class="container align-items-center">

            <button class="navbar-toggler " type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse w-100" id="mainNavbar">

                <ul class="navbar-nav mx-auto mb- mb-lg-0">
                    <li class="nav-item px-2">
                        <router-link :to="{ name: 'dashboardk' }" class="nav-link fw-bold">
                            Home
                        </router-link>
                    </li>
                    <li class="nav-item px-2">
                        <router-link :to="{ name: 'dashboardk.ongkir' }" class="nav-link fw-bold">
                            Check Ongkir
                        </router-link>
                    </li>
                    <li class="nav-item px-2">
                        <router-link :to="{ name: 'dashboardk.tracking' }" class="nav-link fw-bold">
                            Lacak Barang
                        </router-link>
                    </li>
                    <!-- <button class="btn btn-primary text-light fw-semibold " id="keluar" @click="signOut">
                        Login
                    </button> -->
                </ul>
                
            </div>
                <router-link to="/" class="nav-link fw-bold btn btn-primary text-light fw-semibold">
                    Login
                </router-link>
        </div>
    </nav>

    <!-- Konten Halaman -->
    <main class="min-vh-100">
        <router-view />
    </main>
    <!-- <RouterView></RouterView> -->

</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from "@/stores/auth";
import Swal from 'sweetalert2';
import router from '@/router';
import { RouterView } from 'vue-router';

</script>

<style>
li {
    font-size: 1.5rem;
}

#keluar {
    /* font-size: large; */
    /* width: ; */
    justify-content: right;
    margin-right: auto;
}
</style>