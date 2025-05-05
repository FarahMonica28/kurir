<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { kurir } from "@/types";

// Import store autentikasi untuk mengambil data user yang sedang login
import { useAuthStore } from "@/stores/auth";

// Inisialisasi store agar bisa akses data user yang login
const store = useAuthStore();

const kurir = ref({
    name: "",
    email: "",
    phone: "",
    photo: "",
    status: "",
    rating: 0,
});

const getProfile = async () => {
    console.log(store.user)
    kurir.value = {
        name: store.user.name,
        email: store.user.email,
        phone: store.user.phone,
        photo: store.user.photo ? "/storage/" + store.user.photo : "/default-avatar.png",
        status: store.user.kurir?.status,
        rating: store.user.rating,
    };
};

onMounted(() => {
    getProfile();
});
</script>

<template>
    <div class="card">
        <div class="card-header text-center">
            <h3 class="card-title">Profil</h3>
        </div>

        <div class="card-body">
            <div class="row align-items-center justify-content-center text-center">
                <div class="col-md-4 mb-3">
                    <img :src="kurir.photo" class="rounded-circle" width="180" height="180" alt="Foto Kurir" />
                </div>

                <div class="col-md-8 text-start">
                    <p><strong>Nama :</strong> {{ kurir.name }}</p>
                    <p><strong>Email :</strong> {{ kurir.email }}</p>
                    <p><strong>Nomor Telepon :</strong> {{ kurir.phone }}</p>

                    <p>
                        <strong>Status :</strong>
                        <span :class="kurir.status === 'aktif' ? 'text-success' : 'text-danger'">
                            {{ kurir.status }}
                        </span>
                    </p>

                    <p>
                        <strong>Rating:</strong>
                        <span class="fw-bold">{{ kurir.rating }} / 5</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>


<style scoped>
.text-muted {
    color: #376186;
    /* color: #6c757d; */
}

img {
    margin-left: 0%;
}

.nama {
    margin-top: -20%;
}
</style>
