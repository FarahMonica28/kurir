<script setup lang="ts">
import { computed, h, ref, watch } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksii } from "@/types";
import axios from "axios";
import Swal from "sweetalert2";

const column = createColumnHelper<transaksii>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

// ===[2]=== DETAIL DATA
const detailData = ref<transaksii | null>(null);
const showRincian = (data: transaksii) => {
    console.log("rincian");
    detailData.value = data;
};
const closeDetail = () => {
    detailData.value = null;
};

// ===[✅ TAMBAHAN: KOMPUTED UNTUK KURIR AMBIL DAN KIRIM]===
const kurirAmbil = computed(() =>
    detailData.value?.ambil
);

const kurirKirim = computed(() =>
    detailData.value?.antar
);

const getPembayaranBadgeClass = (status: string | undefined) => {
    const statusMap: Record<string, string> = {
        settlement: "badge bg-success fw-bold",
        pending: "badge bg-warning text-dark fw-bold",
        expire: "badge bg-secondary fw-bold",
        cancel: "badge bg-dark fw-bold",
        deny: "badge bg-danger fw-bold",
        failure: "badge bg-danger fw-bold",
        refund: "badge bg-info text-dark fw-bold",
        "belum di bayar": "badge bg-danger fw-bold",
    };

    return statusMap[status?.toLowerCase() ?? ""] || "badge bg-secondary fw-bold";
};

// Kolom tabel
const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("no_resi", {
        header: "No Resi",
    }),
    column.accessor("nama_barang", {
        header: "Nama Barang",
    }),
    column.accessor("tujuan_provinsi.name", {
        header: "Tujuan Provinsi",
    }),
    column.accessor("tujuan_kota.name", {
        header: "Tujuan Kota",
    }),
    column.accessor("alamat_asal", {
        header: "Alamat Pengambilan Barang",
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
                                            ? "badge bg-secondary text-light fw-bold"
                                            : "badge bg-secondary fw-bold";

            return h("span", { class: statusClass }, status);
        },
    }),
    column.accessor("pernah_digudang", {
        header: "Pernah Digudang",
        cell: (cell) => {
            return h(
                "span",
                { class: "badge bg-info" },
                cell.getValue() ? "Ya" : "Tidak"
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
                    "Detail"
                ]
            ),
    }),
];

const refresh = () => paginateRef.value?.refetch();

watch(openForm, (val) => {
    if (!val) selected.value = "";
    window.scrollTo(0, 0);
});
</script>

<template>
    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Riwayat Digudang</h2>
            <!-- <h2 class="mb-0">Riwayat Pengiriman (Status: Digudang)</h2> -->
        </div>
        <div class="card-body">
            <!-- <paginate ref="paginateRef" id="table-riwayat" url="/transaksii?status=digudang" :columns="columns" /> -->
            <paginate ref="paginateRef" id="table-riwayat" url="/transaksii?pernah_digudang=true" :columns="columns" />

            <div v-if="detailData" class="card mt-5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Detail Transaksi</h3>
                    <button class="btn btn-sm btn-danger" @click="closeDetail">
                        Tutup <i class="bi bi-x-circle"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>No Resi:</strong> {{ detailData.no_resi }}</p>
                            </div>

                            <div class="col-md-6">
                                <p><strong>Status Pembayaran:</strong> {{ detailData.status_pembayaran }}</p>

                            </div>
                        </div>
                        <hr class="mt-4" />
                        <div class="row">
                            <div class="col-md-6">
                                <!-- <p><strong>Pengirim:</strong> {{ detailData.pengirim || '-' }}</p> -->
                                <p><strong>Pengirim:</strong> {{ detailData.pengguna?.user.name || '-' }}</p>
                                <p><strong>Nama Barang:</strong> {{ detailData.nama_barang }}</p>
                                <p><strong>Berat Barang:</strong> {{ detailData.berat_barang }} kg</p>
                                <p><strong>Provinsi Asal:</strong> {{ detailData.asal_provinsi?.name || '-' }}</p>
                                <p><strong>Kota Asal:</strong> {{ detailData.asal_kota?.name || '-' }}</p>
                                <p><strong>Alamat Asal:</strong> {{ detailData.alamat_asal }}</p>
                            </div>

                            <div class="col-md-6">
                                <p><strong>Penerima:</strong> {{ detailData.penerima || '-' }}</p>
                                <p><strong>No HP Penerima:</strong> {{ detailData.no_hp_penerima }}</p>
                                <p><strong>Provinsi Tujuan:</strong> {{ detailData.tujuan_provinsi?.name || '-' }}</p>
                                <p><strong>Kota Tujuan:</strong> {{ detailData.tujuan_kota?.name || '-' }}</p>
                                <p><strong>Alamat Tujuan:</strong> {{ detailData.alamat_tujuan }}</p>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <!-- <hr /> -->
                </div>
            </div>

            <!-- DETAIL -->
            <!-- <div v-if="detailData" class="card mt-5">
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
                                <!-- <p><strong>Pengirim:</strong> {{ detailData.pengirim || '-' }}</p> --
                                <p><strong>Pengirim:</strong> {{ detailData.pengguna?.user.name || '-' }}</p>
                                <p><strong>Nama Barang:</strong> {{ detailData.nama_barang }}</p>
                                <p><strong>Berat Barang:</strong> {{ detailData.berat_barang }} kg</p>
                                <p><strong>Provinsi Asal:</strong> {{ detailData.asal_provinsi.name || '-' }}</p>
                                <p><strong>Kota Asal:</strong> {{ detailData.asal_kota.name || '-' }}</p>
                                <p><strong>Alamat Asal:</strong> {{ detailData.alamat_asal }}</p>
                            </div>

                            <div class="col-md-6">
                                <!-- <h2>Informasi Penerima</h2> --
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
                            <p><strong>Biaya:</strong> Rp. {{ detailData.biaya || '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status Pembayaran: </strong>
                                <span :class="getPembayaranBadgeClass(detailData.status_pembayaran)">
                                    {{
                                        detailData.status_pembayaran === 'settlement' ? 'Settlement' :
                                            detailData.status_pembayaran === 'pending' ? 'Pending' :
                                                detailData.status_pembayaran === 'cancel' ? 'Cancel' :
                                                    detailData.status_pembayaran === 'expire' ? 'Expire' :
                                                        detailData.status_pembayaran === 'belum dibayar' ? 'belum dibayar' :
                                                            '-'
                                    }}
                                </span>
                            </p>
                            <!-- <p><strong>Estimasi:</strong> {{ detailData.estimasi || '-' }}</p> -->
                            <!-- <p><strong>Kurir : </strong>
                                <span @click="showKurirDetail(detailData.kurir)"
                                    style="cursor: pointer; color: blue; text-decoration: underline;">
                                    {{ detailData.kurir?.user.name || 'Tidak ada kurir' }}
                                </span>
                            </p> --
                            <p><strong>Kurir Pengambil : </strong>
                                <span>
                                    {{ kurirAmbil.name }}
                                </span>
                                <!-- <span>Tidak ada kurir</span> --
                            </p>

                            <p><strong>Kurir Pengantar : </strong>
                                <span>
                                    {{ kurirKirim.name }}
                                </span>
                                <!-- <span>Tidak ada kurir</span> --
                            </p>


                        </div>
                    </div>
                    <hr />

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Waktu Dibuat:</strong> {{ detailData.waktu || '-' }}</p>
                            <!-- <p><strong>Waktu Penjemputan:</strong> {{ detailData.waktu_penjemputan || '-' }}</p> --
                        </div>
                        <div class="col-md-6">
                            <!-- <p><strong>Waktu Proses Pengiriman:</strong> {{ detailData.waktu_proses || '-' }}
                            </p> --
                            <p><strong>Waktu Terkirim:</strong> {{ detailData.waktu_kirim || '-' }}</p>
                        </div>
                    </div>

                    <hr />

                    <div class="row">

                        <div class="col-md-12">
                            <!-- <p><strong>Penilaian:</strong> {{ detailData.rating || 'Belum ada rating' }}
                            </p> --
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
            </div> -->
        </div>
    </div>
</template>
