<script setup lang="ts">
import { toast } from "vue3-toastify";
import Swal from "sweetalert2";
import { h, ref, watch, onMounted } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { kurir } from "@/types"; // Pastikan tipe data sesuai API
import axios from "@/libs/axios";

const column = createColumnHelper<kurir>();
// Import store autentikasi untuk mengambil data user yang sedang login
import { useAuthStore } from "@/stores/auth";
const paginateRef = ref<any>(null);
const selected = ref<string>(""); // ID kurir yang dipilih
const openForm = ref<boolean>(false); // Form tambah/edit
// form.vue
const emit = defineEmits(["close", "refresh"]);

// Inisialisasi store agar bisa akses data user yang login
const store = useAuthStore();
const showForm = ref(true); // untuk toggle form
const openEditForm = () => {
    showForm.value = true;
};

const kurir = ref({
    name: "",
    email: "",
    phone: "",
    photo: "",
    status: "",
    rating: 0,
});
const toggleStatus = async (kurir_id: string) => {
    const confirm = await Swal.fire({
        title: "Ubah Status?",
        text: "Apakah kamu yakin ingin mengubah status kurir ini?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, ubah",
        cancelButtonText: "Batal",
    });

    if (!confirm.isConfirmed) return;

    try {
        const response = await axios.put(`/kurir/${kurir_id}/toggle-status`);
        toast.success(response.data.message);
        console.log("Sebelum refetch");
        paginateRef.value.refetch();
        refresh(); // ⬅️ cek apakah ini jalan
        console.log("Setelah refetch");
    } catch (error) {
        // toast.error("Gagal mengubah status");
        console.error(error);
    }
};

const getProfile = async () => {
    console.log(store.user)
    kurir.value = {
        kurir_id: store.user.kurir?.kurir_id,
        name: store.user.name,
        email: store.user.email,
        phone: store.user.phone,
        photo: store.user.photo ? "/storage/" + store.user.photo : "/default-avatar.png",
        status: store.user.kurir?.status,
        // rating: store.user.rating,
    };
};

const columns = [
    column.accessor("kurir_id", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-info",
                        onClick: () => {
                            selected.value = cell.getValue();
                            openForm.value = true;
                        },
                    },
                    h("i", { class: "la la-pencil fs-2" }) // Ikon Edit
                ),
            ]),
    }),
];

onMounted(() => {
    getProfile();
});
// Fungsi untuk refresh data tabel
const refresh = () => paginateRef.value?.refetch();

watch(openForm, (val) => {
    if (!val) selected.value = "";
    window.scrollTo(0, 0);
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
                    <p><strong>Kurir ID :</strong> {{ kurir.kurir_id }}</p>
                    <p><strong>Nama :</strong> {{ kurir.name }}</p>
                    <p><strong>Email :</strong> {{ kurir.email }}</p>
                    <p><strong>Nomor Telepon :</strong> {{ kurir.phone }}</p>

                    <!-- <p>
                        <strong>Status :</strong>
                        <span role="button" :class="{
                            'text-success': kurir.status === 'aktif',
                            'text-danger': kurir.status === 'nonaktif',
                            'text-warning': kurir.status === 'sedang menerima orderan',
                            // 'cursor-pointer': kurir.status !== 'sedang menerima orderan'
                        }" @click="kurir.status !== 'sedang menerima orderan' && toggleStatus(kurir.kurir_id)">
                            {{ kurir.status === 'sedang menerima orderan' ? 'Sedang Menerima Orderan' : kurir.status }}
                        </span>
                    </p> -->
                    <p>
                        <strong>Status : </strong>
                        <span :class="{
                            'text-success': kurir.status === 'aktif',
                            'text-danger': kurir.status === 'nonaktif',
                            'text-warning': kurir.status === 'sedang menerima orderan'
                        }" role="button" class="cursor-pointer"
                            @click="kurir.status !== 'sedang menerima orderan' && toggleStatus(kurir.kurir_id)">
                            {{ kurir.status === 'sedang menerima orderan' ? 'Sedang Menerima Orderan' : kurir.status }}
                        </span>
                    </p>
                    <!-- Tombol Edit -->
                    <!-- <div class="text-end mt-3">
                        <button class="btn btn-warning btn-sm" @click="openForm.value">
                            Edit Profil
                        </button>
                    </div> -->

                    
                    <!-- Tombol Edit -->
                    <!-- <div class="text-end mt-3">
                        <button class="btn btn-warning btn-sm" @click="openEditForm">
                            Edit Profil
                        </button>
                    </div>

                    <!-- Form Kurir ditampilkan jika showForm = true
                    <KurirForm v-if="showForm" :selected="kurir.kurir_id" @close="showForm = false"
                        @refresh="refresh" /> -->

                </div>
            </div>
            <!-- Tombol Edit -->
            <div class="text-end mt-3">
                <button class="btn btn-warning btn-sm" @click="openEditForm">
                    Edit Profil
                </button>
            </div>
            
            <!-- Form Kurir ditampilkan jika showForm = true -->
            <Form v-if="showForm" :selected="kurir.kurir_id" @close="showForm = false" @refresh="refresh" class="mt-10" />
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
