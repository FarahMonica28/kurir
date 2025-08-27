<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Barang } from "@/types";

const columnHelper = createColumnHelper<Barang>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

// Hook untuk hapus barang
const { delete: deleteBarang } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
});

// Kolom-kolom tabel barang
const columns = [
  columnHelper.accessor("no", { header: "#" }),
  columnHelper.accessor("nama", { header: "Nama Barang" }),
  columnHelper.accessor("stok", { header: "Stok" }),
  columnHelper.accessor("harga_sewa", {
    header: "Harga",
    cell: (info) => {
      const value = Number(info.getValue() || 0);
      return `Rp ${value.toLocaleString("id-ID")}`;
    },
  }),

  columnHelper.accessor("kategori.nama", {
    header: "Kategori",
    cell: (info) => info.getValue() || "-",
  }),
  columnHelper.accessor("photo", {
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
  columnHelper.accessor("id", {
    header: "Aksi",
    cell: ({ getValue }) =>
      h("div", { class: "d-flex gap-2" }, [
        h(
          "button",
          {
            class: "btn btn-sm btn-icon btn-info",
            title: "Edit",
            onClick: () => {
              selected.value = getValue();
              openForm.value = true;
            },
          },
          h("i", { class: "la la-pencil fs-2" })
        ),
        h(
          "button",
          {
            class: "btn btn-sm btn-icon btn-danger",
            title: "Hapus",
            onClick: () => {
              const id = getValue();
              deleteBarang(`/tambah/barang/${id}`);
            },
          },
          h("i", { class: "la la-trash fs-2" })
        ),
      ]),
  }),
];

// Fungsi refresh data
const refresh = () => paginateRef.value?.refetch();

// Reset selected saat form ditutup
watch(openForm, (val) => {
  if (!val) selected.value = "";
  window.scrollTo({ top: 0, behavior: "smooth" });
});
</script>

<template>
  <!-- Form Tambah/Edit Barang -->
  <Form v-if="openForm" :selected="selected" @close="openForm = false" @refresh="refresh" />

  <!-- Tabel Barang -->
  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">List Barang</h2>
      <button v-if="!openForm" type="button" class="btn btn-sm btn-primary ms-auto" @click="openForm = true">
        Tambah <i class="la la-plus"></i>
      </button>
    </div>
    <div class="card-body">
      <paginate ref="paginateRef" id="table-barang" url="/tambah/barang" :columns="columns" />
    </div>
  </div>
</template>
