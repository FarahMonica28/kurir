<script setup lang="ts">
import { h, ref, watch, computed } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksii } from "@/types";
import { useAuthStore } from "@/stores/auth";
import axios from "axios";
import Swal from "sweetalert2";

const authStore = useAuthStore();
const currentKurir = computed(() => authStore.user);

const column = createColumnHelper<transaksii>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

// ===[2]=== DETAIL DATA
const detailData = ref<transaksii | null>(null);
const showRincian = (data: transaksii) => {
    console.log("rincian");
    detailData.value = data;
    console.log(detailData.value)
};
const closeDetail = () => {
    detailData.value = null;
};
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


const kurirAmbil = computed(() =>
    detailData.value?.ambil
);

const kurirKirim = computed(() =>
    detailData.value?.antar
);

function showPenggunaDetail(pengguna) {
    if (!pengguna || !pengguna.user) {
        Swal.fire('Data tidak tersedia', 'pengguna tidak ditemukan', 'warning');
        return;
    }

    Swal.fire({
        title: pengguna.user.name,
        html: `
      <img src="${pengguna.user.photo ? "/storage/" + pengguna.user.photo : "/default-avatar.png"}" alt="Foto Kurir" class="rounded-circle" width="110" height="110">
     <div style="margin-top: 15px;">
      <p><strong>Email:</strong> ${pengguna.user.email}</p>
      <p><strong>Telepon:</strong> ${pengguna.user.phone}</p>`,
        showCloseButton: true,
    });
}

const columns = [
    column.accessor("no", { header: "#" }),
    column.accessor("transaksii.no_resi", { header: "No Resi" }),
    column.accessor("transaksii.nama_barang", { header: "Nama Barang" }),
    column.accessor("transaksii.penerima", { header: "Nama Penerima" }),
    column.accessor("transaksii.pengguna.user.name", { header: "Pengirim", }),
    column.accessor("transaksii.no_hp_penerima", { header: "No Hp Penerima" }),
    column.accessor("transaksii.alamat_tujuan", { header: "Alamat Tujuan" }),
    // column.accessor("kurir.user.name", { header: "Nama Kurir" }),
    column.accessor("rating", {
        header: "Rating",
        cell: (cell) => {
            const rating = parseInt(cell.getValue());
            const isRated = !isNaN(rating);

            const stars = isRated
                ? "★".repeat(rating) + "☆".repeat(5 - rating)
                : "Belum ada rating";

            return h(
                "span",
                {
                    class: isRated ? "badge bg-succes rating-stars" : "badge bg-warning text-dark",
                    style: isRated ? { color: "gold", fontSize: "16px" } : {},
                },
                stars
            );
        }
    }),

    // column.accessor("status", {
    //     header: "Status",
    //     cell: (cell) => {
    //         const status = cell.getValue();
    //         const statusClass =
    //             status === "selesai"
    //                 ? "badge bg-success fw-bold"
    //                 : status === "dikirim"
    //                     ? "badge bg-warning text-dark fw-bold"
    //                     : status === "diproses"
    //                         ? "badge bg-primary text-light fw-bold"
    //                         : status === "digudang"
    //                             ? "badge bg-secondary fw-bold"
    //                             : status === "dikurir"
    //                                 ? "badge bg-info text-light fw-bold"
    //                                 : status === "diambil kurir"
    //                                     ? "badge bg-info text-dark fw-bold"
    //                                     : status === "menunggu"
    //                                         ? "badge bg-secondary text-light fw-bold"
    //                                         : "badge bg-secondary fw-bold";



    //         return h("span", { class: statusClass }, status);
    //     },
    // }),
    column.display({
        id: "rincian",
        header: "Aksi",
        cell: (cell) => {
            const row = cell.row.original;
            const status = row.status_pembayaran?.toLowerCase();
            const buttons = [];

            buttons.push(h("button", {
                class: "btn btn-sm btn-info d-flex align-items-center gap-1",
                onClick: () => showRincian(row),
            }, [h("i", { class: "bi bi-eye" }), " Detail"]));

            return h("div", { class: "d-flex gap-1" }, buttons);
        }
    })


];

const refresh = () => paginateRef.value?.refetch();

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
            <h2 class="mb-0">Riwayat Ambil</h2>
        </div>
        <div class="card-body">
            <!-- <paginate
                ref="paginateRef"
                id="table-transaksii"
                :url="url"
                :columns="columns"
                /> -->
            <paginate ref="paginateRef" id="table-pengiriman" url="/pengiriman?status=ambil" :columns="columns" />
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
                                <p><strong>No Order:</strong> {{ detailData.transaksii.id }}</p>
                                <p><strong>No Resi:</strong> {{ detailData.transaksii.no_resi }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Ekspedisi:</strong> {{ detailData.transaksii.ekspedisi || '-' }}</p>
                                <p><strong>Layanan:</strong> {{ detailData.transaksii.layanan || '-' }}</p>
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-md-6">
                                <!-- <h2>Informasi Pengirim</h2> -->
                                <!-- <p><strong>Pengirim:</strong> {{ detailData.transaksii.pengirim || '-' }}</p> -->
                                <!-- <p><strong>Pengirim:</strong> {{ detailData.transaksii.pengguna?.user.name || '-' }}</p> -->
                                <p><strong>Pengirim : </strong>
                                    <span @click="showPenggunaDetail(detailData.transaksii.pengguna)"
                                        style="cursor: pointer; color: blue; text-decoration: underline;">
                                        {{ detailData.transaksii.pengguna?.user.name || 'Tidak ada pengguna' }}
                                    </span>
                                </p>
                                <p><strong>No HP Pengirim:</strong> {{ detailData.transaksii.no_hp_pengirim }}</p>
                                <p><strong>Nama Barang:</strong> {{ detailData.transaksii.nama_barang }}</p>
                                <p><strong>Berat Barang:</strong> {{ detailData.transaksii.berat_barang }} kg</p>
                                <p><strong>Provinsi Asal:</strong> {{ detailData.transaksii.asal_provinsi.name || '-' }}
                                </p>
                                <p><strong>Kota Asal:</strong> {{ detailData.transaksii.asal_kota.name || '-' }}</p>
                                <p><strong>Alamat Asal:</strong> {{ detailData.transaksii.alamat_asal }}</p>
                            </div>

                            <div class="col-md-6">
                                <!-- <h2>Informasi Penerima</h2> -->
                                <p><strong>Penerima:</strong> {{ detailData.transaksii.penerima || '-' }}</p>
                                <p><strong>No HP Penerima:</strong> {{ detailData.transaksii.no_hp_penerima }}</p>
                                <p><strong>Provinsi Tujuan:</strong> {{ detailData.transaksii.tujuan_provinsi.name ||
                                    '-' }}</p>
                                <p><strong>Kota Tujuan:</strong> {{ detailData.transaksii.tujuan_kota.name || '-' }}</p>
                                <p><strong>Alamat Tujuan:</strong> {{ detailData.transaksii.alamat_tujuan }}</p>
                            </div>
                        </div>
                    </div>
                    <hr />
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Status:</strong> {{ detailData.transaksii.status }}</p>
                            <p><strong>Status Pembayaran: </strong>
                                <span :class="getPembayaranBadgeClass(detailData.transaksii.status_pembayaran)">
                                    {{
                                        detailData.transaksii.status_pembayaran === 'settlement' ? 'Settlement' :
                                            detailData.transaksii.status_pembayaran === 'pending' ? 'Pending' :
                                                detailData.transaksii.status_pembayaran === 'cancel' ? 'Cancel' :
                                                    detailData.transaksii.status_pembayaran === 'expire' ? 'Expire' :
                                                        detailData.transaksii.status_pembayaran === 'belum dibayar' ? 'belum dibayar' :
                                                            '-'
                                    }}
                                </span>
                            </p>
                            <p><strong>Biaya:</strong> Rp. {{ detailData.transaksii.biaya || '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <!-- <p><strong>Estimasi:</strong> {{ detailData.transaksii.estimasi || '-' }}</p> -->
                            <!-- <p><strong>Kurir : </strong>
                                <span @click="showKurirDetail(detailData.transaksii.kurir)"
                                    style="cursor: pointer; color: blue; text-decoration: underline;">
                                    {{ detailData.transaksii.kurir?.user.name || 'Tidak ada kurir' }}
                                </span>
                            </p> -->
                            <p><strong>Kurir Pengambil : </strong>
                                <!-- <span v-if="kurirAmbil" @click="showKurirDetail(kurirAmbil)"
                                    style="cursor: pointer; color: yellow;">
                                    {{ kurirAmbil?.user?.name }}          
                                </span>
                                <span v-else>Tidak ada kurir</span> -->
                                {{ transaksii.kurirAmbil.name }}       
                            </p>

                            <p><strong>Kurir Pengantar : </strong>
                                <!-- <span v-if="kurirKirim" @click="showKurirDetail(kurirKirim)"
                                    style="cursor: pointer; color: green;">
                                    {{ kurirKirim.user.name }}
                                </span>
                                <span v-else>Tidak ada kurir</span> -->
                                {{ transaksii.kurirKirim.name }}
                            </p>


                        </div>
                    </div>
                    <hr />

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Waktu Dibuat:</strong> {{ detailData.transaksii.waktu || '-' }}</p>
                            <!-- <p><strong>Waktu Penjemputan:</strong> {{ detailData.transaksii.waktu_penjemputan || '-' }}</p> -->
                            <p><strong>Waktu Terkirim:</strong> {{ detailData.transaksii.waktu_kirim || '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <!-- <p><strong>Waktu Proses Pengiriman:</strong> {{ detailData.transaksii.waktu_proses || '-' }}
                            </p> -->
                            <p><strong>Penilaian:</strong>
                                <span v-if="detailData.transaksii.rating" class="rating-stars">
                                    {{ "★".repeat(parseInt(detailData.transaksii.rating)) + "☆".repeat(5 -
                                        parseInt(detailData.transaksii.rating)) }}
                                </span>
                                <span v-else>Belum ada rating</span>
                            </p>
                            <p><strong>Komentar:</strong> {{ detailData.transaksii.komentar || 'Belum ada komentar' }}
                            </p>
                        </div>
                    </div>
                    <!-- <hr /> -->

                    <!-- <div class="row">
                        <div class="col-md-12">
                            <!-- <p><strong>Penilaian:</strong> {{ detailData.transaksii.rating || 'Belum ada rating' }}
                            </p> --
                        </div>
                    </div> -->

                </div>
            </div>
        </div>
    </div>
</template>
