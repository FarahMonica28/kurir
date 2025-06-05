<script setup lang="ts">
import { toast } from "vue3-toastify";
import Swal from "sweetalert2";
import { h, ref, watch, onMounted } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { kurir } from "@/types";
import axios from "@/libs/axios";
import { useAuthStore } from "@/stores/auth";

const store = useAuthStore();
const paginateRef = ref<any>(null);

const selectedId = ref<string>("");
const openForm = ref(false);

const kurir = ref({
  kurir_id: "",
  name: "",
  email: "",
  phone: "",
  photo: "",
  status: "",
  rating: 0,
});

const form = ref({
  name: "",
  email: "",
  phone: "",
  photo: ""
});

const column = createColumnHelper<kurir>();
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
              selectedId.value = cell.getValue();
              openForm.value = true;
            },
          },
          h("i", { class: "la la-pencil fs-2" })
        ),
      ]),
  }),
];

const editKurir = (id: string) => {
  selectedId.value = id;
  openForm.value = true;
};

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
    paginateRef.value?.refetch();
    refresh();
  } catch (error) {
    console.error(error);
  }
};

const getProfile = async () => {
  kurir.value = {
    kurir_id: store.user.kurir?.kurir_id,
    name: store.user.name,
    email: store.user.email,
    phone: store.user.phone,
    photo: store.user.photo ? "/storage/" + store.user.photo : "/default-avatar.png",
    status: store.user.kurir?.status,
    rating: 0,
  };
};

const refresh = () => paginateRef.value?.refetch();

onMounted(() => {
  getProfile();
});

watch(openForm, (val) => {
  if (!val) selectedId.value = "";
  window.scrollTo(0, 0);
});
</script>

<template>
  <Form
    :selected="selectedId"
    v-if="openForm"
    @close="openForm = false"
    @refresh="() => { getProfile(); refresh(); }"
  />

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

          <p>
            <strong>Status : </strong>
            <span
              :class="{
                'text-success': kurir.status === 'aktif',
                'text-danger': kurir.status === 'nonaktif',
                'text-warning': kurir.status === 'sedang menerima orderan'
              }"
              role="button"
              class="cursor-pointer"
              @click="kurir.status !== 'sedang menerima orderan' && toggleStatus(kurir.kurir_id)"
            >
              {{ kurir.status === 'sedang menerima orderan' ? 'Sedang Menerima Orderan' : kurir.status }}
            </span>
          </p>
        </div>
      </div>

      <div class="text-end mt-3">
        <button class="btn btn-warning btn-sm" @click="editKurir(kurir.kurir_id)">
          Edit Profil
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.text-muted {
  color: #376186;
}
img {
  margin-left: 0%;
}
.nama {
  margin-top: -20%;
}
</style>
