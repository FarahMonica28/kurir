<script setup lang="ts">
import { h, ref, watch } from "vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksii } from "@/types";
import axios from "axios";
import Swal from "sweetalert2";

const column = createColumnHelper<transaksii>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

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

    column.accessor("id", {
        header: "Aksi",
        cell: () => {
            return h("span", { class: "text-muted fst-italic" }, "-");
        }
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
            <h2 class="mb-0">Riwayat Pengiriman (Status: Digudang)</h2>
        </div>
        <div class="card-body">
            <paginate ref="paginateRef" id="table-riwayat" url="/transaksii?status=digudang" :columns="columns" />
        </div>
    </div>
</template>
