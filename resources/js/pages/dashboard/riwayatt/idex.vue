<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksi } from "@/types";
import axios from "axios";
import Swal from "sweetalert2";

const column = createColumnHelper<transaksi>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const detailData = ref<transaksi | null>(null);

const showRincian = (data: transaksi) => {
  detailData.value = data;
};

const closeDetail = () => {
  detailData.value = null;
};

const columns = [
  column.accessor("no", { header: "#" }),
  column.accessor("id", {
    header: "No Order",
  }),
  column.accessor("nama_barang", {
    header: "Nama Barang",
  }),
  // column.accessor("alamat_asal", {
  //   header: "Alamat Asal",
  // }),
  column.accessor("alamat_tujuan", {
    header: "Alamat Tujuan",
  }),
  // column.accessor("pengguna.user.name", {
  //   header: "Pengirim",
  // }),
  column.accessor("penerima", {
    header: "Penerima",
  }),
  // column.accessor("no_hp_penerima", {
  //   header: "No HP Penerima",
  // }),
  column.accessor("id", {
  header: "Penilaian",
  cell: (cell) => {
    const transaksi = cell.row.original;
    const isRated = !!transaksi.penilaian;

    return h(
      "button",
      {
        class: `btn btn-sm ${isRated ? "btn-success text-white" : "btn-warning"}`,
        disabled: isRated,
        onClick: () => {
          if (!isRated) {
            selected.value = cell.getValue();
            openForm.value = true;
          }
        },
      },
      transaksi.penilaian || "Beri Penilaian"
    );
  },
}),

  column.display({
    id: "rincian",
    header: "Aksi",
    cell: (cell) =>

      h(
        "button",
        {
          class: "btn btn-sm btn-info d-flex align-items-center gap-1",
          onClick: () => showRincian(cell.row.original),
        },
        [
          h("i", { class: "bi bi-eye" }),
          "Lihat"
        ]
      ),
  }),
];

function showKurirDetail(kurir) {
  if (!kurir || !kurir.user) {
    Swal.fire('Data tidak tersedia', 'Kurir belum ditugaskan', 'warning');
    return;
  }

  Swal.fire({
    title: kurir.user.name,
    html: `
      <img src="${kurir.user.photo  ? "/storage/" + kurir.user.photo : "/default-avatar.png"}" alt="Foto Kurir" class="rounded-circle" width="110" height="110">
     <div style="margin-top: 15px;">
      <p><strong>Email:</strong> ${kurir.user.email}</p>
      <p><strong>Telepon:</strong> ${kurir.user.phone}</p>`,
      showCloseButton: true,
  });
}




const refresh = () => paginateRef.value.refetch();

watch(openForm, (val) => {
  if (!val) {
    selected.value = "";
  }
  window.scrollTo(0, 0);
});
</script>

<template>
  <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">Riwayat Order</h2>
    </div>
    <div class="card-body">
      <!-- <paginate ref="paginateRef" id="table-transaksi" url="/transaksi" :columns="columns"></paginate> -->
      <paginate ref="paginateRef" id="table-transaksi" url="/transaksi?status=Terkirim" :columns="columns"></paginate>

      <!-- DETAIL -->
      <div v-if="detailData" class="card mt-5">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h3 class="mb-0">Detail Transaksi</h3>
          <button class="btn btn-sm btn-danger" @click="closeDetail">
            Tutup <i class="bi bi-x-circle"></i>
          </button>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <p><strong>No Order:</strong> {{ detailData.id }}</p>
              <p><strong>Nama Barang:</strong> {{ detailData.nama_barang }}</p>
              <p><strong>Alamat Asal:</strong> {{ detailData.alamat_asal }}</p>
              <p><strong>Alamat Tujuan:</strong> {{ detailData.alamat_tujuan }}</p>
              <p><strong>Pengirim:</strong> {{ detailData.pengguna?.user.name  }}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Status:</strong> {{ detailData.status }}</p>
              <p><strong>Penerima:</strong> {{ detailData.penerima }}</p>
              <p><strong>No HP Penerima:</strong> {{ detailData.no_hp_penerima }}</p>
              <p><strong>Jarak:</strong> {{ detailData.jarak }} km</p>
              <p><strong>Biaya:</strong> Rp.{{ detailData.biaya }}</p>
            </div>
          </div>
          <hr />
          <div class="row">
            <div class="col-md-6">
              <p><strong>Waktu Dibuat:</strong> {{ detailData.waktu || '-' }}</p>
              <p><strong>Waktu Penjemputan Barang:</strong> {{ detailData.waktu_penjemputan || '-' }}</p>
            </div>
            <div class="col-md-6">
              <p><strong>Waktu Proses Pengiriman:</strong> {{ detailData.waktu_proses || '-' }}</p>
              <p><strong>Waktu Terkirim:</strong> {{ detailData.waktu_terkirim || '-' }}</p>
            </div>
          </div>
          <hr />
          <div class="row">
            <div class="col-md-12">
              <p><strong>Kurir : </strong>
                <span @click="showKurirDetail(detailData.kurir)"
                  style="cursor: pointer; color: blue; text-decoration: underline;">
                  {{ detailData.kurir?.user.name || 'Tidak ada kurir' }}
                </span>
              </p>
              <p><strong>Penilaian:</strong> {{ detailData.penilaian || 'belum ada penilaian' }}</p>
              <p><strong>Komentar:</strong> {{ detailData.komentar || 'belum ada komentar' }}</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style>
.swal-fire {
  width: 50rem !important;
  /* atur sesuai kebutuhan */
  font-size: 1.2rem;
  padding: 1.5rem;
}
</style>
