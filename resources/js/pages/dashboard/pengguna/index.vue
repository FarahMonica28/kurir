<script setup lang="ts">
import { h, onMounted, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { pengguna } from "@/types"; // Pastikan tipe data sesuai API
import axios from "axios";
import Swal from "sweetalert2";

const column = createColumnHelper<pengguna>();
const paginateRef = ref<any>(null);
const selected = ref<string>(""); // ID pengguna yang dipilih
const openForm = ref<boolean>(false); // Form tambah/edit

// Hook untuk menghapus data
const { delete: deletePengguna } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
  onError: (error) => alert(`Gagal menghapus pengguna: ${error.message}`),
});

const otpCode = ref("");
const verifyingUserId = ref("");
const sendOtp = async (uuid: string) => {
  try {
    const res = await axios.post(`/otp/send/${uuid}`);

    // Optional: Cek isi response, bisa pakai console.log
    console.log(res.data);

    verifyingUserId.value = uuid;

    // ✅ Jika sukses, lanjut tampilkan Swal input OTP
    await Swal.fire({
      title: "Verifikasi Email",
      input: "text",
      inputLabel: "Masukkan Kode OTP yang dikirim ke email",
      inputPlaceholder: "Contoh: 123456",
      showCancelButton: true,
      confirmButtonText: "Verifikasi",
      preConfirm: (otp) => {
        otpCode.value = otp;
        return verifyOtp(); // Fungsi verifikasi ke server
      },
    });
  } catch (error) {
    console.error(error);
    Swal.fire("Gagal", "Gagal mengirim kode OTP.", "error");
  }
};


// const sendOtp = async (uuid: string) => {
//     try {
//         await axios.post(`/otp/send/${uuid}`); // Ini memanggil controller di atas
//         verifyingUserId.value = uuid;

//         // Munculkan input OTP dari user
//         Swal.fire({
//             title: "Verifikasi Email",
//             input: "text",
//             inputLabel: "Masukkan Kode OTP yang dikirim ke email",
//             inputPlaceholder: "Contoh: 123456",
//             showCancelButton: true,
//             confirmButtonText: "Verifikasi",
//             preConfirm: (otp) => {
//                 otpCode.value = otp;
//                 return verifyOtp(); // Verifikasi ke server
//             },
//         });
//     } catch (error) {
//         console.error(error);
//         Swal.fire("Gagal", "Gagal mengirim kode OTP.", "error");
//     }
// };


const verifyOtp = async () => {
  try {
    const res = await axios.post(`/otp/verify/${verifyingUserId.value}`, {
      otp: otpCode.value,
    });
    if (res.data.success) {
      Swal.fire("Berhasil", "Email berhasil diverifikasi!", "success");
      refresh();
    } else {
      Swal.fire("Gagal", "Kode OTP salah atau sudah kadaluarsa.", "error");
    }
  } catch (error) {
    console.error(error);
    Swal.fire("Gagal", "Terjadi kesalahan saat verifikasi.", "error");
  }
};



const verifiedUsers = ref<pengguna[]>([]);

const getVerifiedUsers = async () => {
  try {
    // const response = await axios.get("/");
    verifiedUsers.value = response.data;
  } catch (error) {
    console.error("Gagal mengambil data pengguna terverifikasi", error);
  }
};
// Definisi kolom tabel pengguna 
const columns = [
  column.accessor("no", { header: "#" }),
  column.accessor("pengguna_id", { header: "ID Pengguna" }),
  column.accessor("user.name", { header: "Nama Pengguna" }),
  column.accessor("user.email", { header: "Email" }),
  column.accessor("user.phone", { header: "No. Telp" }),
  column.accessor("user.photo", {
    header: "Foto Profil",
    cell: (cell) =>
      cell.getValue()
        ? h("img", {
          src: `/storage/${cell.getValue()}`,
          alt: "Foto Pengguna",
          style: "width: 50px; height: 50px; border-radius: 8px;",
        })
        : "Tidak ada foto",
  }),
  column.accessor("alamat", { header: "Alamat" }),
  //   column.accessor("email_verified_at", {
  //     header: "Verifikasi",
  //     cell: (cell) => {
  //         const row = cell.row.original;

  //         // Jika email_verified_at null → tombol verifikasi
  //         if (!row.email_verified_at) {
  //             return h(
  //                 "button",
  //                 {
  //                     class: "btn btn-sm btn-warning d-flex align-items-center gap-1",
  //                     onClick: () => sendOtp(row.uuid),
  //                 },
  //                 [
  //                     h("i", { class: "la la-key fs-2" }),
  //                     h("span", { class: "fw-semibold" }, "Verifikasi"),
  //                 ]
  //             );
  //         }

  //         // Jika email_verified_at terisi → tampilkan tulisan
  //         return h(
  //             "span",
  //             { class: "text-success fw-semibold" },
  //             "Sudah Terverifikasi"
  //         );
  //     },
  // }),

  column.accessor("email_verified_at", {
    header: "Verifikasi",
    cell: (cell) => {
      const row = cell.row.original;

      // Jika email_verified_at null → tampilkan tombol verifikasi
      if (!row.user.email_verified_at) {
        return h(
          "button",
          {
            class: "btn btn-sm btn-warning",
            onClick: () => sendOtp(row.user.uuid), // UUID dari tabel users
          },
          "Verifikasi"
        );
      }


      // Jika sudah terverifikasi
      return h("span", { class: "text-success fw-semibold" }, "Sudah Terverifikasi");
    },
  }),

column.accessor("pengguna_id", {
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
            // if (confirm("Apakah Anda yakin ingin menghapus pengguna ini?")) {
            deletePengguna(`/pengguna/${cell.getValue()}`);
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
onMounted(() => {
  getVerifiedUsers();
});
</script>

<template>
  <!-- Form Tambah/Edit Pengguna -->
  <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">List Pengguna</h2>
      <!-- <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
        Tambah
        <i class="la la-plus"></i>
      </button> -->
    </div>
    <div class="card-body">
      <paginate ref="paginateRef" id="table-pengguna" url="/pengguna" :columns="columns"></paginate>
    </div>
  </div>
</template>
