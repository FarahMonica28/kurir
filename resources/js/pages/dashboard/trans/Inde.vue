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
    column.accessor("pengirim", {
        header: "Pengirim",
    }),
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
    column.accessor("id", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => {
                            selected.value = cell.getValue();
                            openForm.value = true;
                        },
                    },
                    h("i", { class: "la la-pencil fs-2" })
                ),
            ]),
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
        </div>
        <div class="card-body">
            <!-- <paginate ref="paginateRef" id="table-transaksi" url="/transaksi" :columns="columns"></paginate> -->
            <paginate ref="paginateRef" id="table-transaksi" url="/trans?exclude_status=Terkirim"
                :columns="columns" />

        </div>
    </div>
</template>
