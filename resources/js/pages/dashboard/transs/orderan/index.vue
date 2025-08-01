<script setup lang="ts">
// ✅ Perbaikan import dan penggunaan
import { computed, h, ref, watch } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksii } from "@/types";

// Ambil user (kurir) yang sedang login
import { useAuthStore } from "@/stores/auth";
const authStore = useAuthStore();
const currentKurir = computed(() => authStore.user);

// Column helper untuk tabel
const column = createColumnHelper<transaksii>();

// State dan referensi
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const detailData = ref<transaksii | null>(null);

// ✅ Fungsi tampil detail transaksi
const showRincian = (data: transaksii) => {
    detailData.value = data;
};

// ✅ Tutup detail
const closeDetail = () => {
    detailData.value = null;
};

// ✅ Tampilkan detail pengguna
function showPenggunaDetail(pengguna) {
    if (!pengguna?.user) {
        Swal.fire('Data tidak tersedia', 'Pengguna tidak ditemukan', 'warning');
        return;
    }

    Swal.fire({
        title: pengguna.user.name,
        html: `
            <img src="${pengguna.user.photo ? "/storage/" + pengguna.user.photo : "/default-avatar.png"}" alt="Foto Kurir" class="rounded-circle" width="110" height="110">
            <div style="margin-top: 15px;">
                <p><strong>Email:</strong> ${pengguna.user.email}</p>
                <p><strong>Telepon:</strong> ${pengguna.user.phone}</p>
            </div>
        `,
        showCloseButton: true,
    });
}

// ✅ Mapping badge class untuk status pembayaran
const getPembayaranBadgeClass = (status: string | undefined) => {
    const statusMap: Record<string, string> = {
        settlement: "badge bg-success fw-bold",
        pending: "badge bg-warning text-dark fw-bold",
        expire: "badge bg-secondary fw-bold",
        cancel: "badge bg-dark fw-bold",
        deny: "badge bg-danger fw-bold",
        failure: "badge bg-danger fw-bold",
        refund: "badge bg-info text-dark fw-bold",
        "belum dibayar": "badge bg-danger fw-bold",
    };

    return statusMap[status?.toLowerCase() ?? ""] || "badge bg-secondary fw-bold";
};

// ✅ Computed info kurir
const kurirAmbil = computed(() => detailData.value?.ambil);
const kurirKirim = computed(() => detailData.value?.antar);

// ✅ Kolom tabel transaksi
const columns = [
    column.accessor("no", { header: "#" }),
    column.accessor("no_resi", { header: "No Resi" }),
    column.accessor("nama_barang", { header: "Nama Barang" }),
    column.accessor("tujuan_provinsi.name", { header: "Provinsi Tujuan" }),
    column.accessor("tujuan_kota.name", { header: "Kota Tujuan" }),
    column.accessor("alamat_asal", { header: "Alamat Pengambilan Barang" }),
    column.accessor("status", {
        header: "Status",
        cell: (cell) => {
            const status = cell.getValue();
            const classMap: Record<string, string> = {
                selesai: "bg-success",
                dikirim: "bg-warning text-dark",
                diproses: "bg-primary text-light",
                digudang: "bg-secondary",
                dikurir: "bg-info text-light",
                "diambil kurir": "bg-info text-dark",
                menunggu: "bg-dark text-white",
            };
            const badgeClass = classMap[status] || "bg-secondary";
            return h("span", { class: `badge fw-bold ${badgeClass}` }, status);
        },
    }),
    column.accessor("id", {
        header: "Aksi",
        cell: (cell) => {
            const row = cell.row.original;
            const status = row.status;

            let buttonText = "", nextStatus = "", swalTitle = "", swalText = "";

            if (status === "menunggu") {
                buttonText = "Ambil";
                nextStatus = "diambil kurir";
                swalTitle = "Ambil Orderan";
                swalText = "Apakah Anda akan mengambil orderan ini?";
            } else if (status === "diambil kurir") {
                buttonText = "Ambil Barang";
                nextStatus = "dikurir";
                swalTitle = "Ambil Barang";
                swalText = "Apakah barang sudah di tangan Anda?";
            } else if (status === "dikurir") {
                buttonText = "Digudang";
                nextStatus = "digudang";
                swalTitle = "Kirim ke Gudang";
                swalText = "Apakah barang sudah sampai di gudang?";
            } else if (status === "digudang") {
                return h("span", { class: "text-muted fst-italic" }, "Menunggu dikirim");
            } else {
                return h("span", { class: "text-muted fst-italic" }, "-");
            }

            const handleClick = async () => {
                const result = await Swal.fire({
                    title: swalTitle,
                    text: swalText,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Ya, lanjutkan",
                    cancelButtonText: "Batal",
                });

                if (result.isConfirmed) {
                    try {
                        await axios.put(`/transaksii/${cell.getValue()}/ambil`, {
                            status: nextStatus,
                        });
                        await new Promise(resolve => setTimeout(resolve, 300));
                        await refresh();

                        Swal.fire("Berhasil!", "", "success");
                    } catch (error) {
                        console.error("Gagal update status:", error);
                        Swal.fire("Gagal!", "Terjadi kesalahan saat mengubah status.", "error");
                    }
                }
            }

            return h("div", { class: "d-flex gap-2" }, [
                h("button", { class: "btn btn-sm btn-primary", onClick: handleClick }, buttonText),
                h("button", {
                    class: "btn btn-sm btn-info d-flex align-items-center gap-1",
                    onClick: () => showRincian(cell.row.original),
                }, [h("i", { class: "bi bi-eye" }), "Detail"])
            ]);
        }
    }),
];

// ✅ URL fetch data, hanya status tertentu dan pembayaran settled
const url = computed(() => {
    const params = new URLSearchParams();
    ['digudang', 'tiba digudang', 'diproses', 'dikirim', 'selesai'].forEach(status => {
        params.append("exclude_status[]", status);
    });
    params.append("status_pembayaran", "settlement");
    return `/transaksii?${params.toString()}`;
});

// ✅ Refresh paginate
const refresh = () => paginateRef.value.refetch();

// ✅ Reset selected ketika form ditutup
watch(openForm, (val) => {
    if (!val) selected.value = "";
    window.scrollTo(0, 0);
});

</script>


<template>
    <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">List Orderan</h2>
        </div>
        <div class="card-body">
            <!-- <paginate ref="paginateRef" id="table-transaksi" url="/transaksi" :columns="columns"></paginate> -->
            <paginate ref="paginateRef" id="table-transaksii" :url=url :columns="columns" />

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
                                <!-- <p><strong>Pengirim:</strong> {{ detailData.pengirim || '-' }}</p> -->
                                <p><strong>Pengirim : </strong>
                                    <span @click="showPenggunaDetail(detailData.pengguna)"
                                        style="cursor: pointer; color: blue; text-decoration: underline;">
                                        {{ detailData.pengguna?.user.name || 'Tidak ada pengguna' }}
                                    </span>
                                </p>
                                <p><strong>No HP Pengirim:</strong> {{ detailData.no_hp_pengirim }}</p>
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
                            </p> -->
                            <p><strong>Kurir Pengambil : </strong>
                                <!-- <span v-if="kurirAmbil" @click="showKurirDetail(kurirAmbil)"
                                    style="cursor: pointer; color: yellow;">
                                </span>
                                <span v-else>Tidak ada kurir</span> -->
                                {{ kurirAmbil?.user?.name }}
                            </p>

                            <p><strong>Kurir Pengantar : </strong>
                                <!-- <span v-if="kurirKirim" @click="showKurirDetail(kurirKirim)"
                                    style="cursor: pointer; color: green;">
                                    {{ kurirKirim.user.name }}
                                </span>
                                <span v-else>Tidak ada kurir</span> -->
                                {{ kurirKirim?.user?.name }}
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
