<script setup lang="ts">
import { h, ref, watch, computed, onMounted } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksii, Pengiriman } from "@/types";
import Swal from "sweetalert2";
import axios from "axios";

// ===[1]=== VARIABEL DASAR
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
// const kurirAmbil = computed(() =>
//     detailData.value?.pengiriman?.find(p =>
//         p.deskripsi?.toLowerCase().includes("menuju rumahmu untuk mengambil barang")
//     )?.kurir
// );

// const kurirKirim = computed(() =>
//     detailData.value?.pengiriman?.find(p =>
//         p.deskripsi?.toLowerCase().includes("menuju ke alamat tujuan")
//     )?.kurir
// );
// yg benar
// ===[✅ TAMBAHAN: KOMPUTED UNTUK KURIR AMBIL DAN KIRIM]===
const kurirAmbil = computed(() =>
    detailData.value?.ambil
);

const kurirKirim = computed(() =>
    detailData.value?.antar
);

// ===[3]=== MODAL KURIR DETAIL
function showKurirDetail(kurir: any) {
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
        <p><strong>Telepon:</strong> ${kurir.user.phone}</p>
      </div>`,
        showCloseButton: true,
    });
}

// ===[4]=== MIDTRANS SNAP GLOBAL
declare global {
    interface Window {
        snap: any;
    }
}


const redirectToPayment = async (id: number) => {
    try {
        const { data } = await axios.get(`/payment/token/${id}`);
        const snapToken = data.snap_token;

        if (!snapToken) {
            Swal.fire({
                icon: 'error',
                title: 'Token Tidak Tersedia',
                text: 'Token pembayaran tidak tersedia.',
            });
            return;
        }

        if (typeof window.snap === 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memuat Pembayaran',
                text: 'Snap.js belum termuat. Silakan refresh halaman.',
            });
            return;
        }

        window.snap.pay(snapToken, {
            onSuccess: async (result: any) => {
                console.log("✅ Pembayaran berhasil:", result);
                // Panggil endpoint manual update status
                await axios.post('/manual-update-status', {
                    order_id: result.order_id,
                    transaction_status: result.transaction_status,
                    payment_type: result.payment_type
                });
                await Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil',
                    text: 'Terima kasih, pembayaran Anda telah berhasil.',
                });
            },
            onPending: async (result: any) => {
                // console.log("⏳ Pembayaran pending:", result);
                await axios.post('/manual-update-status', {
                    order_id: result.order_id,
                    transaction_status: result.transaction_status,
                    payment_type: result.payment_type
                });
                await Swal.fire({
                    icon: 'info',
                    title: 'Menunggu Pembayaran',
                    text: 'Pembayaran sedang menunggu penyelesaian.',
                });
            },
            onError: (result: any) => {
                console.error("❌ Terjadi kesalahan saat pembayaran:", result);
                Swal.fire({
                    icon: 'error',
                    title: 'Pembayaran Gagal',
                    text: 'Terjadi kesalahan saat memproses pembayaran.',
                });
            },
            onClose: () => {
                console.warn("❗ Pembayaran dibatalkan oleh pengguna.");
                Swal.fire({
                    icon: 'warning',
                    title: 'Pembayaran Dibatalkan',
                    text: 'Anda telah membatalkan proses pembayaran.',
                });
            }
        });

    } catch (error: any) {
        console.error("🚫 Gagal mengambil token pembayaran:", error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal Memproses',
            text: 'Gagal memproses pembayaran. Silakan coba beberapa saat lagi.',
        });
    }
};



// const getPembayaranBadgeClass = (status: string | undefined) => {
//     const statusMap: Record<string, string> = {
//         settlement: "badge bg-success fw-bold",
//         pending: "badge bg-warning text-dark fw-bold",
//         expire: "badge bg-secondary fw-bold",
//         cancel: "badge bg-dark fw-bold",
//         deny: "badge bg-danger fw-bold",
//         failure: "badge bg-danger fw-bold",
//         refund: "badge bg-info text-dark fw-bold",
//     };
//     return statusMap[status?.toLowerCase() ?? ""] || "badge bg-secondary fw-bold";
// };
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

// ===[5]=== KOLUMN
const columns = [
    column.accessor("no", { header: "#" }),
    column.accessor("pengguna.user.name", {
        header: "Pengirim",
    }),
    column.accessor("nama_barang", { header: "Nama Barang" }),
    column.accessor("no_resi", { header: "No Resi" }),
    column.accessor("status", {
        header: "Status",
        cell: (cell) => {
            const status = cell.getValue();
            const statusClass =
                status === "selesai" ? "badge bg-success fw-bold"
                    : status === "dikirim" ? "badge bg-warning text-dark fw-bold"
                        : status === "diproses" ? "badge bg-primary text-light fw-bold"
                            : status === "digudang" ? "badge bg-secondary fw-bold"
                                : status === "dikurir" ? "badge bg-info text-light fw-bold"
                                    : status === "diambil kurir" ? "badge bg-info text-dark fw-bold"
                                        : status === "menunggu" ? "badge bg-dark text-white fw-bold"
                                            : "badge bg-secondary fw-bold";

            return h("span", { class: statusClass }, status);
        },
    }),
    column.accessor("status_pembayaran", {
        header: "Pembayaran",
        cell: (cell) => {
            const status = cell.getValue()?.toLowerCase();
            const statusMap: Record<string, { label: string; class: string }> = {
                settlement: { label: "settlement", class: "badge bg-success fw-bold" },
                pending: { label: "Pending", class: "badge bg-warning text-dark fw-bold" },
                expire: { label: "expire", class: "badge bg-secondary fw-bold" },
                failure: { label: "cancel", class: "badge bg-danger fw-bold" },
                refund: { label: "Refund", class: "badge bg-info text-dark fw-bold" },
            };

            const { label, class: badgeClass } = statusMap[status] || {
                label: status ?? "Tidak Diketahui",
                class: "badge bg-secondary fw-bold"
            };

            return h("span", { class: badgeClass }, label);
        }
    }),
    column.accessor("waktu", { header: "Waktu Order" }),
    // column.accessor("status_pembayaran", { header: "Status Pembayaran" }),
    column.display({
        id: "rincian",
        header: "Aksi",
        cell: (cell) => {
            const row = cell.row.original;
            const status = row.status_pembayaran?.toLowerCase();
            const buttons = [];

            if (status !== "settlement") {
                buttons.push(h("button", {
                    class: "btn btn-sm btn-success me-1",
                    onClick: () => redirectToPayment(row.id),
                }, [h("i", { class: "bi bi-credit-card" }), " Bayar"]));
            }

            buttons.push(h("button", {
                class: "btn btn-sm btn-info d-flex align-items-center gap-1",
                onClick: () => showRincian(row),
            }, [h("i", { class: "bi bi-eye" }), " Detail"]));

            return h("div", { class: "d-flex gap-1" }, buttons);
        }
    })
];

const refresh = () => paginateRef.value.refetch();

watch(openForm, (val) => {
    if (!val) selected.value = "";
    window.scrollTo(0, 0);
});

// ===[6]=== LOAD SNAP MIDTRANS
const snapLoaded = ref(false);
onMounted(() => {
    if (!window.snap) {
        const script = document.createElement("script");
        script.src = "https://app.sandbox.midtrans.com/snap/snap.js";
        script.setAttribute("data-client-key", "SB-Mid-client-JuHAlpsbUGhh4cvF");
        script.async = true;
        script.onload = () => {
            snapLoaded.value = true;
        };
        document.body.appendChild(script);
    } else {
        snapLoaded.value = true;
    }
});
</script>


<template>
    <!-- <input class="form-control form-control-sm w-25 ms-auto" type="text" placeholder="Cari..."
        @input="e => paginateRef.value.search = e.target.value" /> -->

    <Form :selected="selected" @close="openForm = false" v-if="openForm" @refresh="refresh" />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">List Order</h2>
            <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
                Tambah
                <i class="la la-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <!-- <paginate ref="paginateRef" id="table-transaksi" url="/transaksi" :columns="columns"></paginate> -->
            <paginate ref="paginateRef" id="table-transaksii" url="/transaksii?exclude_status=selesai"
                :columns="columns" />


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
                                <!-- <p><strong>Pengirim:</strong> {{ detailData.pengirim || '-' }}</p> -->
                                <p><strong>Pengirim:</strong> {{ detailData.pengguna?.user.name || '-' }}</p>
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
                                <span v-if="kurirAmbil" @click="showKurirDetail(kurirAmbil)"
                                    style="cursor: pointer; color: blue;">
                                    {{ kurirAmbil.name }}
                                </span>
                                <span v-else>Tidak ada kurir</span>
                            </p>

                            <p><strong>Kurir Pengantar : </strong>
                                <span v-if="kurirKirim" @click="showKurirDetail(kurirKirim)"
                                    style="cursor: pointer; color: blue;">
                                    {{ kurirKirim.name }}
                                </span>
                                <span v-else>Tidak ada kurir</span>
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
