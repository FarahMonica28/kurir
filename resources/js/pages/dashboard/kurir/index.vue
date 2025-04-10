<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { kurir } from "@/types"; // Pastikan tipe data sesuai API

const column = createColumnHelper<kurir>();
const paginateRef = ref<any>(null);
const selected = ref<string>(""); // ID kurir yang dipilih
const openForm = ref<boolean>(false); // Form tambah/edit

// Hook untuk menghapus data
const { delete: deleteKurir } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
  onError: (error) => alert(`Gagal menghapus kurir: ${error.message}`),
});

// Definisi kolom tabel kurir
const columns = [
  column.accessor("no", { header: "#" }),
  column.accessor("kurir_id", { header: "ID Kurir" }),
  // column.accessor("phone", { header: "No. Telp" }),
  // column.accessor("name", { header: "Nama Kurir" }),
  // column.accessor("email", { header: "Email" }),
  column.accessor("user.name", { header: "Nama Kurir" }),
  column.accessor("user.email", { header: "Email" }),
  column.accessor("user.phone", { header: "No. Telp" }),
  column.accessor("user.photo", {
  header: "Foto Profil",
  cell: (cell) =>
    cell.getValue()
      ? h("img", {
          src: `/storage/${cell.getValue()}`,
          alt: "Foto Kurir",
          style: "width: 50px; height: 50px; border-radius: 8px;",
        })
      : "Tidak ada foto",
}),
  column.accessor("rating", {
    header: "Rating",
    cell: (cell) => {
      const rating = cell.getValue();
      return rating
        ? h("span", { class: "fw-bold text-warning" }, "⭐".repeat(rating)) // Menampilkan bintang
        : "Belum ada rating";
    },
  }),
  // column.accessor("photo", {
  //   header: "Foto Profil",
  //   cell: (cell) =>
  //     cell.getValue()
  //       ? h("img", {
  //         src: `/storage/${cell.getValue()}`,
  //         alt: "Foto Kurir",
  //         style: "width: 50px; height: 50px; border-radius: 8px;",
  //       })
  //       : "Tidak ada foto",
  // }),
  column.accessor("status", {
    header: "Status",
    cell: (cell) =>
      h(
        "span",
        {
          class: `badge ${cell.getValue() === "aktif" ? "bg-success" : "bg-danger"}`,
        },
        cell.getValue() === "aktif" ? "Aktif" : "Nonaktif"
      ),
  }),
  column.accessor("kurir_id", {
    header: "Aksi",
    cell: (cell) =>
      h("div", { class: "d-flex gap-2" }, [
        // Tombol Edit
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
        // Tombol Hapus
        h(
          "button",
          {
            class: "btn btn-sm btn-danger",
            onClick: () => {
              // if (confirm("Apakah Anda yakin ingin menghapus kurir ini?")) {
              deleteKurir(`/kurir/${cell.getValue()}`);
              // }
            },
          },
          h("i", { class: "la la-trash fs-2" }) // Ikon Hapus
        ),
      ]),
  }),
];

// Fungsi untuk refresh data tabel
const refresh = () => paginateRef.value?.refetch();

// Reset selected ID saat form ditutup
watch(openForm, (val) => {
  if (!val) selected.value = "";
  window.scrollTo(0, 0);
});
</script>

<template>
  <!-- Form Tambah/Edit Kurir -->
  <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">List Kurir</h2>
      <!-- <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
        Tambah
        <i class="la la-plus"></i>
      </button> -->
    </div>
    <div class="card-body">
      <paginate ref="paginateRef" id="table-kurir" url="/kurir" :columns="columns"></paginate>
    </div>
  </div>
</template>
