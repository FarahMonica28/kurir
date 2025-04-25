<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksi } from "@/types";

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

const { delete: deleteOrder } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    // column.accessor("no", { header: "#" }),
    column.accessor("id", {
        header: "No Order",
    }),
    column.accessor("nama_barang", {
        header: "Nama Barang",
    }),
    column.accessor("alamat_asal", {
        header: "Alamat Asal",
    }),
    column.accessor("alamat_tujuan", {
        header: "Alamat Tujuan",
    }),
    // column.accessor("pengirim", {
    //     header: "Pengirim",
    // }),
    column.accessor("penerima", {
        header: "Penerima",
    }),
    column.accessor("no_hp_penerima", {
        header: "No HP Penerima",
    }),
    column.accessor("status", {
        header: "Status",
        cell: (cell) => {
            const status = cell.getValue();
            // const statusClass =
            //     status === "Terkirim"
            //         ? "badge bg-success fw-bold"
            //         : status === "Belum Terkirim"
            //             ? "badge bg-warning text-dark fw-bold"
            //             : "badge bg-secondary fw-bold";
            const statusClass =
                status === "Terkirim"
                    ? "badge bg-success fw-bold"
                    : status === "Belum Terkirim"
                        ? "badge bg-warning text-dark fw-bold"
                        : status === "Penjemputan Barang"
                            ? "badge bg-primary text-dark fw-bold"
                            : "badge bg-danger fw-bold";

            return h("span", { class: statusClass }, status);
        },
    }),
    column.accessor("waktu", {
        header: "Waktu Order",
    }),
    // column.accessor("id", {
    //     header: "Aksi",
    //     cell: (cell) =>
    //         h("div", { class: "d-flex gap-2" }, [
    //             // h(
    //             //     "button",
    //             //     {
    //             //         class: "btn btn-sm btn-icon btn-info",
    //             //         onClick: () => {
    //             //             selected.value = cell.getValue();
    //             //             openForm.value = true;
    //             //         },
    //             //     },
    //             //     h("i", { class: "la la-pencil fs-2" })
    //             // ),
    //             h(
    //                 "button",
    //                 {
    //                     class: "btn btn-sm btn-icon btn-danger",
    //                     onClick: () =>
    //                         deleteOrder(`/transaksi/${cell.getValue()}`),
    //                 },
    //                 // h("i", { class: "la la-trash fs-2" })
    //                 h("i", { class: "bi bi-x-circle fs-2" })
    //             ),
    //         ]),
    // }),
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
            <h2 class="mb-0">List Order</h2>
            <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
                Tambah
                <i class="la la-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <!-- <paginate ref="paginateRef" id="table-transaksi" url="/transaksi" :columns="columns"></paginate> -->
            <paginate ref="paginateRef" id="table-transaksi" url="/transaksi?exclude_status=Terkirim"
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
                            <p><strong>Nama Barang:</strong> {{ detailData.nama_barang }}</p>
                            <p><strong>Alamat Asal:</strong> {{ detailData.alamat_asal }}</p>
                            <p><strong>Alamat Tujuan:</strong> {{ detailData.alamat_tujuan }}</p>
                            <p><strong>Pengirim:</strong> {{ detailData.pengirim }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> {{ detailData.status }}</p>
                            <p><strong>Penerima:</strong> {{ detailData.penerima }}</p>
                            <p><strong>No HP Penerima:</strong> {{ detailData.no_hp_penerima }}</p>
                            <p><strong>Jarak:</strong> {{ detailData.berat_barang || '-' }} km</p>
                            <p><strong>Biaya:</strong> Rp.{{ detailData.biaya || '-' }}</p>
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
                            <!-- <p><strong>Kurir:</strong> {{ detailData.kurir_id}}</p> -->
                            <p><strong>Kurir:</strong> {{ detailData.kurir?.name || '-' }}</p>
                            <p><strong>Penilaian:</strong> {{ detailData.penilaian || 'belum ada penilaian' }}</p>
                            <p><strong>Komentar:</strong> {{ detailData.komentar || 'belum ada komentar' }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
