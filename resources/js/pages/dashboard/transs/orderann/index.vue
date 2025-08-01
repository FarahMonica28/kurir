<script setup lang="ts">
import { h, ref, watch, computed } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksii } from "@/types";
import axios from "axios";
import Swal from "sweetalert2";

import { useAuthStore } from "@/stores/auth";
const authStore = useAuthStore();
const currentKurir = computed(() => authStore.user);

const column = createColumnHelper<transaksii>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const detailData = ref<transaksii | null>(null);
const showRincian = (data: transaksii) => {
    detailData.value = data;
};
const closeDetail = () => {
    detailData.value = null;
};

function showPenggunaDetail(pengguna: any) {
    if (!pengguna || !pengguna.user) {
        Swal.fire('Data tidak tersedia', 'pengguna tidak ditemukan', 'warning');
        return;
    }

    Swal.fire({
        title: pengguna.user.name,
        html: `
            <img src="${pengguna.user.photo ? "/storage/" + pengguna.user.photo : "/default-avatar.png"}" 
                 alt="Foto Kurir" class="rounded-circle" width="110" height="110">
            <div style="margin-top: 15px;">
            <p><strong>Email:</strong> ${pengguna.user.email}</p>
            <p><strong>Telepon:</strong> ${pengguna.user.phone}</p>
        `,
        showCloseButton: true,
    });
}

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

const kurirAmbil = computed(() => detailData.value?.ambil);
const kurirKirim = computed(() => detailData.value?.antar);

const handleUpdateStatus = async (id: string, currentStatus: string) => {
    const nextStatus = currentStatus === "tiba digudang" ? "dikirim" : "selesai";
    const label = nextStatus === "dikirim" ? "Apakah Anda Akan Mengantar Paket ini?" : "Konfirmasi selesai mengantar paket?";

    const result = await Swal.fire({
        title: label,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya",
        cancelButtonText: "Batal",
    });

    if (result.isConfirmed) {
        try {
            const kurirId = currentKurir.value?.kurir?.kurir_id;
            if (!kurirId) throw new Error("Kurir belum login");

            await axios.put(`/transaksii/${id}/antar`, {
                status: nextStatus,
                kurir_id: kurirId,
            });

            Swal.fire("Berhasil", "", "success");
            refresh();
        } catch (error: any) {
            Swal.fire("Error", error.message || "Gagal mengubah status", "error");
        }
    }
};

const url = computed(() => {
    const params = new URLSearchParams();
    ['menunggu', 'diambil kurir', 'dikurir', 'diproses', 'digudang', 'selesai'].forEach(status => {
        params.append('exclude_status[]', status);
    });
    params.append('status_pembayaran', 'settlement');
    return `/transaksii?${params.toString()}`;
});

const columns = [
    column.accessor("no", { header: "#" }),
    column.accessor("no_resi", { header: "No Resi" }),
    column.accessor("nama_barang", { header: "Nama Barang" }),
    column.accessor("penerima", { header: "Nama Penerima" }),
    column.accessor("no_hp_penerima", { header: "No Hp Penerima" }),
    column.accessor("alamat_tujuan", { header: "Alamat Tujuan" }),
    column.accessor("status", {
        header: "Status",
        cell: (cell) => {
            const status = cell.getValue();
            const statusClass =
                status === "selesai" ? "badge bg-success fw-bold" :
                status === "dikirim" ? "badge bg-warning text-dark fw-bold" :
                status === "diproses" ? "badge bg-primary text-light fw-bold" :
                status === "digudang" ? "badge bg-secondary fw-bold" :
                status === "dikurir" ? "badge bg-info text-light fw-bold" :
                status === "diambil kurir" ? "badge bg-info text-dark fw-bold" :
                status === "menunggu" ? "badge bg-secondary text-light fw-bold" :
                "badge bg-secondary fw-bold";

            return h("span", { class: statusClass }, status);
        },
    }),
    column.accessor("id", {
        header: "Aksi",
        cell: (cell) => {
            const row = cell.row.original;
            const status = row.status;

            if (status !== "tiba digudang" && status !== "dikirim") return null;

            const buttonLabel = status === "tiba digudang" ? "Antar" : "Selesai";
            const buttonColor = status === "tiba digudang" ? "btn-info" : "btn-success";

            return h("div", { class: "d-flex gap-2" }, [
                h("button", {
                    class: `btn btn-sm ${buttonColor} d-flex align-items-center gap-1`,
                    onClick: () => handleUpdateStatus(row.id, status),
                }, [h("i", { class: "bi bi-check2-circle" }), buttonLabel]),

                h("button", {
                    class: "btn btn-sm btn-info d-flex align-items-center gap-1",
                    onClick: () => showRincian(row),
                }, [h("i", { class: "bi bi-eye" }), "Detail"]),
            ]);
        },
    }),
];

const refresh = () => paginateRef.value?.refetch();

watch(openForm, (val) => {
    if (!val) selected.value = "";
    window.scrollTo(0, 0);
});
</script>

<template>
    <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

    <div class="card">
        <div class="card-header">
            <h2 class="mb-0">List Orderan</h2>
        </div>
        <div class="card-body">
            <paginate ref="paginateRef" id="table-transaksii" :url="url" :columns="columns" />

            <div v-if="detailData" class="card mt-5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>Detail Transaksi</h3>
                    <button class="btn btn-sm btn-danger" @click="closeDetail">
                        Tutup <i class="bi bi-x-circle"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>No Order:</strong> {{ detailData.id }}</p>
                            <p><strong>No Resi:</strong> {{ detailData.no_resi }}</p>
                            <p><strong>Pengirim:</strong>
                                <span @click="showPenggunaDetail(detailData.pengguna)" class="text-primary" style="cursor: pointer;">
                                    {{ detailData.pengguna?.user.name || '-' }}
                                </span>
                            </p>
                            <p><strong>No HP Pengirim:</strong> {{ detailData.no_hp_pengirim }}</p>
                            <p><strong>Nama Barang:</strong> {{ detailData.nama_barang }}</p>
                            <p><strong>Berat:</strong> {{ detailData.berat_barang }} kg</p>
                            <p><strong>Alamat Asal:</strong> {{ detailData.alamat_asal }}</p>
                            <p><strong>Kota Asal:</strong> {{ detailData.asal_kota.name || '-' }}</p>
                            <p><strong>Provinsi Asal:</strong> {{ detailData.asal_provinsi.name || '-' }}</p>
                        </div>

                        <div class="col-md-6">
                            <p><strong>Penerima:</strong> {{ detailData.penerima || '-' }}</p>
                            <p><strong>No HP:</strong> {{ detailData.no_hp_penerima }}</p>
                            <p><strong>Alamat Tujuan:</strong> {{ detailData.alamat_tujuan }}</p>
                            <p><strong>Kota Tujuan:</strong> {{ detailData.tujuan_kota.name || '-' }}</p>
                            <p><strong>Provinsi Tujuan:</strong> {{ detailData.tujuan_provinsi.name || '-' }}</p>
                            <p><strong>Status:</strong> {{ detailData.status }}</p>
                            <p><strong>Layanan:</strong> {{ detailData.layanan || '-' }}</p>
                            <p><strong>Biaya:</strong> Rp. {{ detailData.biaya || '-' }}</p>
                            <p><strong>Status Pembayaran:</strong>
                                <span :class="getPembayaranBadgeClass(detailData.status_pembayaran)">
                                    {{ detailData.status_pembayaran || '-' }}
                                </span>
                            </p>
                            <p><strong>Kurir Pengambil:</strong> {{ kurirAmbil?.name || '-' }}</p>
                            <p><strong>Kurir Pengantar:</strong> {{ kurirKirim?.name || '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

