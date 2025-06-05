<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksii } from "@/types";
import Swal from "sweetalert2";

const column = createColumnHelper<transaksii>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const detailData = ref<transaksii | null>(null);

const showRincian = (data: transaksii) => {
    console.log("rincian");
    detailData.value = data;
};

const closeDetail = () => {
    detailData.value = null;
};
function showKurirDetail(kurir) {
    if (!kurir || !kurir.user) {
        Swal.fire('Data tidak tersedia', 'Kurir belum ditugaskan', 'warning');
        return;
    }

    Swal.fire({
        title: kurir.user.name,
        html: `
      <img src="${kurir.user.photo ? "/storage/" + kurir.user.photo : "/default-avatar.png"}" alt="Foto Kurir" class="rounded-circle" width="110" height="110">
     <div style="margin-top: 15px;">
      <p><strong>Email:</strong> ${kurir.user.email}</p>
      <p><strong>Telepon:</strong> ${kurir.user.phone}</p>`,
        showCloseButton: true,
    });
}

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("pengirim", {
        header: "Pengirim",
    }),
    column.accessor("nama_barang", {
        header: "Nama Barang",
    }),
    column.accessor("no_resi", {
        header: " No Resi",
    }),
    column.accessor("id", {
        header: "Rating",
        cell: (cell) => {
            const transaksii = cell.row.original;
            const isRated = !!transaksii.rating;

            return h(
                "button",
                {
                    // class: `${isRated ? "text-white bg-success rounded px-2 py-1" : "bg-warning text-dark rounded px-2 py-1 border-0 fw-normal"}`,
                    class: `${isRated ? "text-white rounded px-2 py-1" : "bg-warning text-dark rounded px-2 py-1 border-0 fw-normal"}`,
                    style: isRated ? { fontSize: "13px" } : { fontSize: "12px" },
                    disabled: isRated,
                    onClick: () => {
                        if (!isRated) {
                            selected.value = cell.getValue();
                            openForm.value = true;
                        }
                    },
                },
                [
                    h(
                        "span",
                        { class: isRated ? "rating-stars" : "" },
                        isRated
                            ? "★".repeat(parseInt(transaksii.rating)) + "☆".repeat(5 - parseInt(transaksii.rating))
                            : "Beri Rating"
                    )
                ]
            );
        }
    }),



    column.accessor("waktu", {
        header: "Waktu Order",
    }),
    column.accessor("status", {
        header: "Status",
        cell: (cell) => {
            const status = cell.getValue();
            const statusClass =
                status === "selesai"
                    ? "badge bg-success fw-bold"
                    : status === "dikirim"
                        ? "badge bg-warning text-dark fw-bold"
                        : status === "diproses"
                            ? "badge bg-primary text-light fw-bold"
                            : status === "digudang"
                                ? "badge bg-secondary fw-bold"
                                : status === "dikurir"
                                    ? "badge bg-info text-light fw-bold"
                                    : status === "diambil kurir"
                                        ? "badge bg-info text-dark fw-bold"
                                        : status === "menunggu"
                                            ? "badge bg-light text-dark border fw-bold"
                                            : "badge bg-dark fw-bold";


            return h("span", { class: statusClass }, status);
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
                    "Detail"
                ]
            ),
    }),
];

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
            <h2 class="mb-0">Riwayat Pemesanan</h2>
        </div>
        <div class="card-body">
            <!-- <paginate ref="paginateRef" id="table-transaksi" url="/transaksi" :columns="columns"></paginate> -->
            <paginate ref="paginateRef" id="table-transaksii" url="/transaksii?status=selesai" :columns="columns" />


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
                            <p><strong>No Resi:</strong> {{ detailData.no_resi }}</p>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-md-6">
                                <!-- <h2>Informasi Pengirim</h2> -->
                                <!-- <p><strong>Pengirim:</strong> {{ detailData.pengguna?.name || '-' }}</p> -->
                                <p><strong>Pengirim:</strong> {{ detailData.pengirim || '-' }}</p>
                                <p><strong>Nama Barang:</strong> {{ detailData.nama_barang }}</p>
                                <p><strong>Berat Barang:</strong> {{ detailData.berat_barang }} kg</p>
                                <p><strong>Provinsi Asal:</strong> {{ detailData.asal_provinsi.name || '-' }}</p>
                                <p><strong>Kota Asal:</strong> {{ detailData.asal_kota.name || '-' }}</p>
                                <p><strong>Alamat Asal:</strong> {{ detailData.alamat_asal }}</p>
                            </div>

                            <div class="col-md-6">
                                <!-- <h2>Informasi Penerima</h2> -->
                                <p><strong>Penerima:</strong> {{ detailData.penerima || '-' }}</p>
                                <p><strong>No HP Penerima:</strong> {{ detailData.no_hp_penerima }}</p>
                                <p><strong>Provinsi Tujuan:</strong> {{ detailData.tujuan_provinsi.name || '-' }}</p>
                                <p><strong>Kota Tujuan:</strong> {{ detailData.tujuan_kota.name || '-' }}</p>
                                <p><strong>Alamat Tujuan:</strong> {{ detailData.alamat_tujuan }}</p>
                            </div>
                        </div>
                    </div>
                    <hr />
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Status:</strong> {{ detailData.status }}</p>
                            <p><strong>Layanan:</strong> {{ detailData.layanan || '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <!-- <p><strong>Estimasi:</strong> {{ detailData.estimasi || '-' }}</p> -->
                            <p><strong>Biaya:</strong> Rp. {{ detailData.biaya || '-' }}</p>
                            <p><strong>Kurir:</strong>
                                <span @click="showKurirDetail(detailData.kurir)"
                                    style="cursor: pointer; color: blue; text-decoration: underline;">
                                    {{ detailData.kurir?.user.name || 'Tidak ada kurir' }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <hr />

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Waktu Dibuat:</strong> {{ detailData.waktu || '-' }}</p>
                            <!-- <p><strong>Waktu Penjemputan:</strong> {{ detailData.waktu_penjemputan || '-' }}</p> -->
                        </div>
                        <div class="col-md-6">
                            <!-- <p><strong>Waktu Proses Pengiriman:</strong> {{ detailData.waktu_proses || '-' }}
                            </p> -->
                            <p><strong>Waktu Terkirim:</strong> {{ detailData.waktu_kirim || '-' }}</p>
                        </div>
                    </div>

                    <hr />

                    <div class="row">
                        <div class="col-md-12">
                            <!-- <p><strong>Penilaian:</strong> {{ detailData.rating || 'Belum ada rating' }}
                            </p> -->
                            <p><strong>Penilaian:</strong>
                                <span v-if="detailData.rating" class="rating-stars">
                                    {{ "★".repeat(parseInt(detailData.rating)) + "☆".repeat(5 -
                                        parseInt(detailData.rating)) }}
                                </span>
                                <span v-else>Belum ada rating</span>
                            </p>


                            <p><strong>Komentar:</strong> {{ detailData.komentar || 'Belum ada komentar' }}</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
<style>
.rating-stars {
    color:rgb(255, 255, 46);
    font-size: 1.2rem;
    letter-spacing: 2px;
}
</style>
