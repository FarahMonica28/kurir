<template>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-9">
        <div class="container align-items-center">

            <button class="navbar-toggler " type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse w-100" id="mainNavbar">

                <ul class="navbar-nav mx-auto mb- mb-lg-0">
                    <li class="nav-item px-2">
                        <router-link :to="{ name: 'dashboard_pengguna.ongkir' }" class="nav-link fw-bold">
                            Check Ongkir
                        </router-link>
                    </li>
                    <li class="nav-item px-2">
                        <router-link :to="{ name: 'dashboard_pengguna.order' }" class="nav-link fw-bold">
                            Order
                        </router-link>
                    </li>
                    <li class="nav-item px-2">
                        <router-link :to="{ name: 'dashboard_pengguna.riwayat' }" class="nav-link fw-bold">
                            Riwayat
                        </router-link>
                    </li>

                    <li class="nav-item px-2">
                        <router-link :to="{ name: 'dashboard_pengguna.tracking' }" class="nav-link fw-bold">
                            Lacak Barang
                        </router-link>
                    </li>
                </ul>

            </div>
            <button class="btn btn-primary text-light fw-semibold " id="keluar" @click="signOut">
                Logout
            </button>
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


const emit = defineEmits(["succes", "refresh"]);

const store = useAuthStore();
const signOut = () => {
    Swal.fire({
        icon: "warning",
        text: "Apakah Anda yakin ingin keluar?",
        showCancelButton: true,
        confirmButtonText: "Ya, Keluar",
        cancelButtonText: "Batal",
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
            confirmButton: "btn fw-semibold btn-light-primary",
            cancelButton: "btn fw-semibold btn-light-danger",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            store.logout();
            Swal.fire({
                icon: "success",
                text: "Berhasil keluar",
            }).then(() => {
                router.push({ name: "sign-in" });
            });
        }
    });
};

</script>

<style>
li {
    font-size: 1.5rem;
}
#keluar{
    /* font-size: large; */
    width: 10%;
    margin-right: auto;
}
</style>