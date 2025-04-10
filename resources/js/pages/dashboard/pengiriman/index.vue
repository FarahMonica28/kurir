<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Pengiriman } from "@/types"; // Pastikan tipe data sesuai API

const currentRole = ref<string>("admin"); // bisa juga dari store seperti pinia atau dari props
const column = createColumnHelper<Pengiriman>();
const paginateRef = ref<any>(null);
const selected = ref<string>(""); // ID pengiriman yang dipilih
const openForm = ref<boolean>(false); // Form tambah/edit

// Hook untuk menghapus data
const { delete: deletePengiriman } = useDelete({
  onSuccess: () => refresh(),
  onError: (error) => alert(`Gagal menghapus pengiriman: ${error.message}`),
});

// Definisi kolom tabel pengiriman
const columns = [
  // column.accessor("no_resi", { header: "No. Resi" }),
  column.accessor("no", { header: "#" }),
  column.accessor("kurir_id", { header: "Kurir_id" }),
  column.accessor("paket", { header: "Paket" }),
  column.accessor("penerima", { header: "Penerima" }),
  column.accessor("alamat", { header: "Alamat" }),
  column.accessor("biaya", { header: "biaya" }),
  column.accessor("status", {
    header: "Status",
    cell: (cell) => {
      const status = cell.getValue();
      let badgeClass = "bg-secondary";
      let label = "Tidak Diketahui";

      if (status === "dikemas") {
        badgeClass = "bg-primary";
        label = "Dikemas";
      } else if (status === "dikirim") {
        badgeClass = "bg-warning";
        label = "Dikirim";
      } else if (status === "diterima") {
        badgeClass = "bg-success";
        label = "Diterima";
      }

      return h("span", { class: `badge ${badgeClass}` }, label);
    },
  }),
  column.accessor("tanggal_pengiriman", { header: "Tgl Pengiriman" }),
  column.accessor("tanggal_penerimaan", { header: "Tgl Penerimaan" }),
  // column.accessor("id", {
  //   header: "Aksi",
  //   cell: (cell) =>
  //     h("div", { class: "d-flex gap-2" }, [
  //       // Tombol Edit
  //       h(
  //         "button",
  //         {
  //           class: "btn btn-sm btn-info",
  //           onClick: () => {
  //             selected.value = cell.getValue();
  //             openForm.value = true;
  //           },
  //         },
  //         h("i", { class: "la la-pencil fs-2" })
  //       ),
  //       // Tombol Hapus
  //       h(
  //         "button",
  //         {
  //           class: "btn btn-sm btn-danger",
  //           onClick: () => {
  //             // if (confirm("Apakah Anda yakin ingin menghapus pengiriman ini?")) {
  //               deletePengiriman(`/pengiriman/${cell.getValue()}`);
  //             // }
  //           },
  //         },
  //         h("i", { class: "la la-trash fs-2" })
  //       ),
  //     ]),
  // }),
  column.accessor("id", {
  header: "Aksi",
  cell: (cell) => {
    const id = cell.getValue();
    const aksiButtons = [];

    // Admin: semua aksi
    if (currentRole.value === "admin") {
      aksiButtons.push(
        h("button", {onClick: () => {
              selected.value = id;
              openForm.value = true;
            },
          },
          h("i", { class: "la la-pencil fs-2" })
        ),
        h(
          "button",
          {
            class: "btn btn-sm btn-danger",
            onClick: () => {
              deletePengiriman(`/pengiriman/${id}`);
            },
          },
          h("i", { class: "la la-trash fs-2" })
        )
      );
    }

    // Kurir: hanya bisa ubah status
    if (currentRole.value === "kurirs") {
      aksiButtons.push(
        h(
          "button",
          {
            class: "btn btn-sm btn-warning",
            onClick: () => {
              selected.value = id;
              openForm.value = true; // hanya status yang akan bisa diedit di form
            },
          },
          h("i", { class: "la la-edit fs-2" })
        )
      );
    }

    // Pengguna biasa: tidak ada aksi
    return h("div", { class: "d-flex gap-2" }, aksiButtons);
  },
}),

];

// Fungsi untuk refresh data tabel
const refresh = () => paginateRef.value?.refetch();

// Reset selected ID saat form ditutup
watch(openForm, (val) => {
  if (!val) {
    selected.value = "";
  }
  window.scrollTo(0, 0);
});
</script>

<template>
  <!-- Form Tambah/Edit Pengiriman -->
  <Form v-if="openForm" :selected="selected" @close="openForm = false" @refresh="refresh" />

  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">List Pengiriman</h2>
      <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
        Tambah
        <i class="la la-plus"></i>
      </button>
    </div>
    <div class="card-body">
      <paginate ref="paginateRef" id="table-pengiriman" url="/pengiriman" :columns="columns"></paginate>
    </div>
  </div>
</template>
