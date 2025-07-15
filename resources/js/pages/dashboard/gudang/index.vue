<script setup lang="ts">
/**
 * Import library dan dependensi
 */
import { h, ref, watch, nextTick, computed } from "vue";
import { useDelete } from "@/libs/hooks"; // (tidak digunakan di kode ini, bisa dihapus jika tidak dipakai)
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksii } from "@/types";
import Swal from "sweetalert2";
import html2pdf from "html2pdf.js";
import axios from "axios";

/**
 * Inisialisasi helper TanStack Table
 */
const column = createColumnHelper<transaksii>();

/**
 * State dan referensi komponen
 */
const paginateRef = ref<any>(null); // Referensi ke komponen pagination
const selected = ref<string>(""); // Data yang dipilih
const openForm = ref<boolean>(false); // Kontrol form tambah/edit
const detailData = ref<transaksii | null>(null); // Data untuk modal detail
const printData = ref<transaksii | null>(null); // Data yang akan dicetak sebagai resi

/**
 * Fungsi untuk mencetak resi dan update status transaksi menjadi 'diproses'
 */
const printResi = async (data: transaksii) => {
    const result = await Swal.fire({
        title: "Cetak Resi?",
        text: "Apakah Anda ingin mengunduh no resi?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Download",
        cancelButtonText: "Batal",
        reverseButtons: true,
    });

    if (result.isConfirmed) {
        try {
            // Update status transaksi ke 'diproses'
            await axios.put(`/transaksii/${data.id}/gudang`, { status: "diproses" });

            // Refresh tabel agar status langsung terlihat berubah
            refresh();

            // Tunggu hingga DOM selesai update, lalu cetak resi
            printData.value = data;
            await nextTick();

            const element = document.getElementById("print-resi");
            if (element) {
                const opt = {
                    margin: 0.5,
                    filename: `resi-${data.no_resi}.pdf`,
                    image: { type: "jpeg", quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: "in", format: "letter", orientation: "portrait" },
                };
                html2pdf().set(opt).from(element).save();
            }

            Swal.fire({
                title: "Berhasil",
                text: "No resi berhasil diunduh.",
                icon: "success",
                timer: 1500,
                showConfirmButton: false,
            });

        } catch (error) {
            console.error("Gagal mencetak no resi:", error);
            Swal.fire({
                title: "Gagal",
                text: "Terjadi kesalahan saat mencetak resi.",
                icon: "error",
            });
        }
    }
};

/**
 * Fungsi untuk menandai bahwa barang telah sampai di gudang tujuan
 */
const markAsArrived = async (data: transaksii) => {
    const result = await Swal.fire({
        title: "Barang sudah sampai di gudang tujuan?",
        text: "Status akan diperbarui menjadi tiba digudang.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya, sudah sampai",
        cancelButtonText: "Batal",
        reverseButtons: true,
    });

    if (result.isConfirmed) {
        try {
            // Update status ke 'tiba digudang'
            await axios.put(`/transaksii/${data.id}/gudang`, { status: "tiba digudang" });
            refresh();

            Swal.fire({
                title: "Berhasil",
                text: "Barang telah tiba digudang.",
                icon: "success",
                timer: 1500,
                showConfirmButton: false,
            });
        } catch (error) {
            console.error("Gagal ubah status:", error);
            Swal.fire({
                title: "Gagal",
                text: "Terjadi kesalahan saat mengubah status.",
                icon: "error",
            });
        }
    }
};

/**
 * Endpoint data transaksi, hanya menampilkan status tertentu (tidak termasuk selesai, dikirim, dll)
 */
const url = computed(() => {
    const params = new URLSearchParams();
    ['menunggu', 'dikurir', 'diambil kurir', 'dikirim', 'selesai'].forEach(status => {
        params.append('exclude_status[]', status);
    });
    return `/transaksii?${params.toString()}`;
});

/**
 * Fungsi untuk menampilkan modal/detail transaksi
 */
const showRincian = (data: transaksii) => {
    detailData.value = data;
};

/**
 * Fungsi untuk menutup detail modal
 */
const closeDetail = () => {
    detailData.value = null;
};

/**
 * Definisi kolom tabel
 */
const columns = [
    column.accessor("no", { header: "#" }),
    column.accessor("no_resi", { header: "No Resi" }),
    column.accessor("tujuan_provinsi.name", { header: "Provinsi Tujuan" }),
    column.accessor("tujuan_kota.name", { header: "Kota Tujuan" }),
    column.accessor("pengguna.user.name", { header: "Pengirim" }),
    column.accessor("nama_barang", { header: "Nama Barang" }),

    // Kolom status dengan badge warna dinamis
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
                                : status === "diambil kurir"
                                    ? "badge bg-info text-dark fw-bold"
                                    : status === "menunggu"
                                        ? "badge bg-light text-dark border fw-bold"
                                        : status === "tiba digudang"
                                            ? "badge bg-success text-white fw-bold"
                                            : "badge bg-dark fw-bold";
            return h("span", { class: statusClass }, status);
        },
    }),

    // Kolom aksi (Detail, Tiba Digudang, Cetak Resi)
    column.display({
        id: "aksi",
        header: "Aksi",
        cell: (cell) => {
            const data = cell.row.original;

            // Tombol "Tiba Digudang" hanya muncul jika status 'diproses' atau 'dikirim'
            const showTibaDigudang = data.status === "diproses" || data.status === "dikirim";

            return h("div", { class: "d-flex gap-2" }, [
                // Tombol Detail
                h("button", {
                    class: "btn btn-sm btn-info d-flex align-items-center gap-1",
                    onClick: () => showRincian(data),
                }, [h("i", { class: "bi bi-eye" }), "Detail"]),

                // Tombol Tiba Digudang
                showTibaDigudang &&
                h("button", {
                    class: "btn btn-sm btn-success d-flex align-items-center gap-1",
                    onClick: () => markAsArrived(data),
                }, [h("i", { class: "bi bi-check2-circle" }), "Tiba Digudang"]),

                // Tombol Cetak Resi (jika belum tiba di gudang)
                data.status !== "tiba digudang" &&
                h("button", {
                    class: "btn btn-sm btn-secondary d-flex align-items-center gap-1",
                    onClick: () => printResi(data),
                }, [h("i", { class: "bi bi-printer" })]),
            ]);
        }
    }),
];

/**
 * Fungsi untuk me-refresh table
 */
const refresh = () => paginateRef.value?.refetch();

/**
 * Reset selected jika form ditutup, dan scroll ke atas
 */
watch(openForm, (val) => {
    if (!val) selected.value = "";
    window.scrollTo(0, 0);
});
</script>

<template>
    <Form v-if="openForm" :selected="selected" @close="openForm = false" @refresh="refresh" />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">List Order</h2>
        </div>

        <div class="card-body">
            <paginate ref="paginateRef" id="table-transaksii" :url="url" :columns="columns" />


            <!-- Area Resi untuk PDF -->
            <div id="print-resi" v-if="printData" class="print-preview">
                <div class="resi-container">
                    <div class="resi-header">
                        No Resi: {{ printData.no_resi }}
                    </div>

                    <div class="resi-content">
                        <div class="resi-left">
                            <div class="mb-2 text-center mt-4">
                                <strong>Ekspedisi:</strong><br />
                                <h1><strong>{{ printData.ekspedisi || 'JNE / J&T' }}</strong></h1>
                            </div>
                            <strong>
                                <hr />
                            </strong>
                            <div class="mt-6">
                                <strong>Informasi Pengirim:</strong>
                                <!-- <p  -4">Nama Pengirim : {{ printData.pengirim || '-' }}</p> -->
                                <!-- <p><strong>Pengirim:</strong> {{detailData.pengguna?.user.name  || '-' }}</p> -->
                                <p class="mt-2">Nama Pengirim : {{ printData.pengguna?.user.name || '-' }}</p>
                                <p>Asal Provinsi : {{ printData.asal_provinsi?.name }}</p>
                                <p>Asal Kota : {{ printData.asal_kota?.name }}</p>
                                <p>Alamat Asal : {{ printData.alamat_asal }} </p>
                            </div>
                        </div>

                        <div class="resi-right">
                            <div class="mb-2">
                                <strong>Tanggal:</strong> {{ printData.waktu }}<br />
                                <strong>Jumlah:</strong> {{ printData.berat_barang }} kg<br />
                                <strong>Biaya:</strong> Rp{{ printData.biaya?.toLocaleString() || '-' }} <br />
                                <strong>Status Pembayaran :</strong> {{ printData.status_pembayaran }}<br />

                            </div>
                            <div class="mt-4">
                                <strong>Informasi Penerima:</strong><br />
                                <p class="mt-2">Nama Penerima : {{ printData.penerima }}</p>
                                <p>Tujuan Provinsi : {{ printData.tujuan_provinsi?.name }}</p>
                                <p>Tujuan Kota : {{ printData.tujuan_kota?.name }}</p>
                                <p>Alamat Tujuan : {{ printData.alamat_tujuan }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


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
                    <hr />
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.print-preview {
    color: #000;
    background-color: #fff;
    margin-top: 4%;
}

.resi-container {
    width: 600px;
    border: 3px solid #000;
    padding: 16px;
    background-color: #fff;
    font-family: "times new roman";
}

.resi-header {
    border-bottom: 3px solid #000;
    padding-bottom: 8px;
    margin-bottom: 12px;
    text-align: center;
    font-weight: bold;
    font-size: 18px;
}

.resi-content {
    display: flex;
    border-top: 3px solid #000;
}

.resi-left {
    width: 40%;
    border-right: 3px solid #000;
    padding-right: 12px;
}

.resi-right {
    width: 60%;
    padding-left: 12px;
}

p {
    margin: 4px 0;
}

/* hr{
    border: 1px solid #000;
} */

@media print {
    body * {
        visibility: hidden;
    }

    #print-resi,
    #print-resi * {
        visibility: visible;
    }

    #print-resi {
        position: absolute;
        left: 0;
        top: 0;
    }

    .d-print-none {
        display: none !important;
    }
}
</style>
