<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { kurir } from "@/types"; // Pastikan tipe data sesuai API
import axios from "@/libs/axios";

const column = createColumnHelper<kurir>();
const paginateRef = ref<any>(null);
const selected = ref<string>(""); // ID kurir yang dipilih
const openForm = ref<boolean>(false); // Form tambah/edit

// Hook untuk menghapus data
const { delete: deleteKurir } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
  onError: (error) => alert(`Gagal menghapus kurir: ${error.message}`),
});
// const toggleStatus = async (kurir_id: string) => {
//   // const confirm = await Swal.fire({ ... });

//   if (!confirm.isConfirmed) return;

//   try {
//     const response = await axios.put(`/kurir/${kurir_id}/toggle-status`);
//     toast.success(response.data.message);

//     // 🔽 Update langsung data lokal
//     const kurir = paginateRef.value.items.find((k: kurir) => k.kurir_id === kurir_id);
//     if (kurir) {
//       kurir.status = kurir.status === "aktif" ? "nonaktif" : "aktif";
//     }

//   } catch (error) {
//     toast.error("Gagal mengubah status");
//   }
// };
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
    toast.error("Gagal mengubah status");
    console.error(error);
  }
};


// Definisi kolom tabel kurir 
const columns = [
  column.accessor("no", { header: "#" }),
  column.accessor("kurir_id", { header: "ID Kurir" }),
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
  // column.accessor("rating", {
  //   header: "Rating",
  //   cell: (cell) => {
  //     const rating = cell.getValue();
  //     return rating
  //       ? h("span", { class: "fw-bold text-warning" }, "⭐".repeat(rating)) // Menampilkan bintang
  //       : "Belum ada rating";
  //   },
  // }),
  column.accessor("status", {
    header: "Status",
    cell: (cell) => {
      const status = cell.getValue();
      let btnClass = "btn btn-sm ";

      if (status === "aktif") {
        btnClass += "bg-success text-white";
      } else if (status === "nonaktif") {
        btnClass += "bg-danger text-white";
      } else if (status === "sedang menerima orderan") {
        btnClass += "bg-warning text-dark";
      }

      return h(
        "button",
        {
          class: btnClass,
          onClick: () => toggleStatus(cell.row.original.kurir_id),
        },
        status === "aktif"
          ? "Aktif"
          : status === "nonaktif"
            ? "Nonaktif"
            : "Sedang Menerima Orderan"
      );
    },
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
