<script setup lang="ts">
import { h, ref, watch } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import Swal from "sweetalert2";
import axios from "axios";
import Form from "./form.vue";
import type { Pengirimans } from "@/types";
import { StreamQrcodeBarcodeReader } from 'vue3-barcode-qrcode-reader';

const column = createColumnHelper<Pengirimans>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);
const resultScan = ref<string | undefined>(undefined);
const isLoading = ref<boolean>(false);
const scanMode = ref<"ambil" | "kirim" | "selesai" | null>(null);

const columns = [
  column.accessor("no_resi", { header: "No. Resi" }),
  column.accessor("penerima", { header: "Nama Penerima" }),
  column.accessor("alamat_tujuan", { header: "Alamat Tujuan" }),
  column.accessor("status", {
    header: "Status",
    cell: (cell) =>
      h("span", {
        class: statusClass(cell.getValue()),
      }, formatStatus(cell.getValue())),
  }),
  column.accessor("waktu_ambil_kirim", {
    header: "Waktu Ambil & Kirim",
    cell: (cell) => h("div", [
      h("div", `Ambil: ${cell.row.original.waktu_ambil || '-'}`),
      h("div", `Kirim: ${cell.row.original.waktu_kirim || '-'}`)
    ])
  }),
  column.accessor("id", {
    header: "Aksi",
    cell: (cell) => {
      const id = cell.getValue();
      const row = cell.row.original;
      return h("div", { class: "d-flex flex-wrap gap-2" }, [
        h("button", {
          class: "btn btn-sm btn-primary",
          onClick: () => handleAmbilBarang(row.no_resi),
          disabled: row.status !== "belum_diambil"
        }, "Ambil Barang"),

        h("button", {
          class: "btn btn-sm btn-warning",
          onClick: () => handleMulaiKirim(row.no_resi),
          disabled: row.status !== "diambil"
        }, "Mulai Kirim"),

        h("button", {
          class: "btn btn-sm btn-success",
          onClick: () => handleSelesaiKirim(row.no_resi),
          disabled: row.status !== "sedang_dikirim"
        }, "Selesai Kirim"),

        h("button", {
          class: "btn btn-sm btn-danger",
          onClick: () => laporkanMasalah(row.no_resi) // ← kirim no_resi
        }, "Laporkan Masalah")
      ]);
    }
  }),
];

const formatStatus = (status: string) => {
  switch (status) {
    case "belum_diambil": return "Belum Diambil";
    case "diambil": return "Diambil dari Gudang";
    case "sedang_dikirim": return "Sedang Dikirim";
    case "terkirim": return "Terkirim";
    case "gagal": return "Gagal";
    default: return status;
  }
};

const statusClass = (status: string) => {
  switch (status) {
    case "belum_diambil": return "badge bg-secondary";
    case "diambil": return "badge bg-info text-dark";
    case "sedang_dikirim": return "badge bg-warning text-dark";
    case "terkirim": return "badge bg-success";
    case "gagal": return "badge bg-danger";
    default: return "badge bg-light";
  }
};

const refresh = () => paginateRef.value?.refetch();

watch(openForm, (val) => {
  if (!val) {
    selected.value = "";
  }
  window.scrollTo(0, 0);
});

// SCAN HANDLER
// const onResult = (result: string) => {
//   if (!result) return;

//   const noResi = result.trim();
//   if (scanMode.value === 'mulai') {
//     axios.post('/pengirimans/mulai-kirim', { no_resi: noResi })
//       .then(() => toast.success("Pengiriman dimulai"))
//       .catch(err => toast.error(err.response?.data?.message || "Gagal memulai kirim"));
//   } else if (scanMode.value === 'selesai') {
//     axios.post('/pengirimans/selesai-kirim', { no_resi: noResi })
//       .then(() => toast.success("Pengiriman selesai"))
//       .catch(err => toast.error(err.response?.data?.message || "Gagal menyelesaikan kirim"));
//   } else if (scanMode.value === 'ambil') {
//     axios.post('/pengirimans/ambil', { no_resi: noResi })
//       .then(() => toast.success("Barang berhasil diambil"))
//       .catch(err => toast.error(err.response?.data?.message || "Gagal ambil barang"));
//   }

//   scanMode.value = null; // tutup setelah selesai scan
// };

// function onResult(result: { text: string }) {
//   resultScan.value = result.text;
//   if (!scanMode.value) return;

//   if (scanMode.value === "ambil") return ambilBarangByQr(result.text);
//   if (scanMode.value === "kirim") return mulaiKirimByQr(result.text);
//   if (scanMode.value === "selesai") return selesaiKirimByQr(result.text);
// }
function onResult(result: string) {
  if (!result) return;
  const noResi = result.trim();
  if (scanMode.value === "ambil") {
    ambilBarang(noResi);
  } else if (scanMode.value === "mulai") {
    mulaiKirim(noResi);
  } else if (scanMode.value === "selesai") {
    selesaiKirim(noResi);
  }
  scanMode.value = null; // close scan mode
}

function onLoading(loading: boolean) {
  isLoading.value = loading;
}

// Aksi Manual / QR
const handleAmbilBarang = (noResi: string) => {
  Swal.fire({
    title: "Ambil Barang",
    text: `Konfirmasi ambil barang dengan No. Resi: ${noResi}?`,
    icon: "question",
    showCancelButton: true,
  }).then((result) => {
    if (result.isConfirmed) ambilBarangByQr(noResi);
  });
};

const handleMulaiKirim = (noResi: string) => {
  Swal.fire({
    title: "Mulai Kirim",
    text: `Mulai pengiriman untuk No. Resi: ${noResi}?`,
    icon: "question",
    showCancelButton: true,
  }).then((result) => {
    if (result.isConfirmed) mulaiKirimByQr(noResi);
  });
};

const handleSelesaiKirim = (noResi: string) => {
  Swal.fire({
    title: "Selesai Kirim",
    text: `Konfirmasi barang sudah dikirim dengan No. Resi: ${noResi}?`,
    icon: "question",
    showCancelButton: true,
  }).then((result) => {
    if (result.isConfirmed) selesaiKirimByQr(noResi);
  });
};

const handleLaporkanMasalah = (id: string) => {
  Swal.fire("Laporkan Masalah", `Laporkan masalah untuk ID: ${id}`, "warning");
};

// POST KE API
async function ambilBarangByQr(noResi: string) {
  try {
    const res = await axios.post("/pengirimans/ambil-barang", { no_resi: noResi });
    Swal.fire("Berhasil", res.data.message, "success");
    refresh();
  } catch (e: any) {
    Swal.fire("Gagal", e?.response?.data?.message || "Terjadi kesalahan", "error");
  }
}

async function mulaiKirimByQr(noResi: string) {
  try {
    const res = await axios.post("/pengirimans/mulai-kirim", { no_resi: noResi });
    Swal.fire("Berhasil", res.data.message, "success");
    refresh();
  } catch (e: any) {
    Swal.fire("Gagal", e?.response?.data?.message || "Terjadi kesalahan", "error");
  }
}

async function selesaiKirimByQr(noResi: string) {
  try {
    const res = await axios.post("/pengirimans/selesai-kirim", { no_resi: noResi });
    Swal.fire("Berhasil", res.data.message, "success");
    refresh();
  } catch (e: any) {
    Swal.fire("Gagal", e?.response?.data?.message || "Terjadi kesalahan", "error");
  }
}

async function laporkanMasalah(noResi: string) {
  const { value: masalah } = await Swal.fire({
    title: "Laporkan Masalah",
    input: "textarea",
    inputLabel: "Jelaskan masalah pengiriman",
    inputPlaceholder: "Tuliskan masalah di sini...",
    showCancelButton: true,
  });

  if (!masalah) return;

  try {
    const res = await axios.post("/pengirimans/laporkan-masalah", {
      no_resi: noResi,
      masalah,
    });
    Swal.fire("Berhasil", res.data.message, "success");
    refresh(); // jika ingin reload table
  } catch (error: any) {
    Swal.fire("Error", error?.response?.data?.message || "Gagal melaporkan", "error");
  }
}a
</script>

<template>
  <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h2 class="mb-0">List Pengiriman</h2>
      <div class="btn-group">
        <button class="btn btn-outline-primary btn-sm" @click="scanMode = 'ambil'">Scan Ambil</button>
        <button class="btn btn-outline-warning btn-sm" @click="scanMode = 'kirim'">Scan Kirim</button>
        <button class="btn btn-outline-success btn-sm" @click="scanMode = 'selesai'">Scan Selesai</button>
      </div>
    </div>
    <!-- <div>
      <button @click="scanMode = 'ambil'" class="btn btn-outline-primary">Scan Ambil Barang</button>
      <button @click="scanMode = 'mulai'" class="btn btn-outline-success">Scan Mulai Kirim</button>
      <button @click="scanMode = 'selesai'" class="btn btn-outline-info">Scan Selesai Kirim</button> -->

    <!-- <div v-if="scanMode" class="p-3 border-top mt-3">
        <h5>Mode Scan: {{ scanMode }}</h5>
        <StreamQrcodeBarcodeReader :paused="!scanMode" capture="shoot" @result="onResult" @loading="onLoading" />
        <button class="btn btn-sm btn-secondary mt-2" @click="scanMode = null">Tutup Scan</button>
      </div>
    </div> -->


    <div class="card-body">
      <paginate ref="paginateRef" id="table-pengirimans" url="/pengirimans" :columns="columns" />
    </div>

    <div v-if="scanMode" class="p-3 border-top">
      <h5>Mode Scan: {{ scanMode }}</h5>
      <StreamQrcodeBarcodeReader  :paused="!scanMode" capture="shoot" @result="onResult" @loading="onLoading" />
      <button class="btn btn-sm btn-secondary mt-2" @click="scanMode = null">Tutup Scan</button>
    </div>


  </div>
</template>
