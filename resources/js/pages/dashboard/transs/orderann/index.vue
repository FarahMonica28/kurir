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


const handleUpdateStatus = async (id: string, currentStatus: string) => {
    const nextStatus = currentStatus === "tiba digudang" ? "dikirim" : "selesai";
    const label = nextStatus === "dikirim" ? "Apakah Anda Akan Mengantar Paket ini?" : "Konfirmasi selesai mengantar paket?";

    const result = await Swal.fire({
        title: label,
        // text: `Apakah kamu yakin ingin mengubah status menjadi "${nextStatus}"?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Ya ",
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

            Swal.fire("Berhasil", "Terimakasih", "success");
            refresh();
        } catch (error: any) {
            Swal.fire("Error", error.message || "Gagal mengubah status", "error");
        }
    }
};
const url = computed(() => {
    const params = new URLSearchParams();
    ['menunggu', 'diambil kurir', 'digudang','diproses', 'selesai'].forEach(status => {
        params.append('exclude_status[]', status);
    });
    return `/transaksii?${params.toString()}`;
});

const columns = [
    column.accessor("no", { header: "#" }),
    column.accessor("no_resi", { header: "No Resi" }),
    column.accessor("nama_barang", { header: "Nama Barang" }),
    column.accessor("penerima", { header: "Nama Penerima" }),
    column.accessor("no_hp_penerima", { header: "No Hp Penerima" }),
    column.accessor("alamat_tujuan", { header: "Alamat Tujuan" }),
    // column.accessor("kurir.user.name", { header: "Nama Kurir" }),
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
    column.accessor("id", {
        header: "Aksi",
        cell: (cell) => {
            const row = cell.row.original;
            const status = row.status;

            if (status !== "tiba digudang" && status !== "dikirim") return null;

            const buttonLabel = status === "tiba digudang" ? "Antar" : "Selesai";
            const buttonColor = status === "tiba digudang" ? "btn-info" : "btn-success";

            return h(
                "button",
                {
                    class: `btn btn-sm ${buttonColor} d-flex align-items-center gap-1`,
                    onClick: () => handleUpdateStatus(row.id, status),
                },
                [
                    h("i", { class: "bi bi-check2-circle" }),
                    buttonLabel
                ]
            );
        },
    }),

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
            <h2 class="mb-0">List Orderan</h2>
        </div>
        <div class="card-body">
            <paginate ref="paginateRef" id="table-transaksii" :url="url" :columns="columns" />
            <!-- <paginate
                ref="paginateRef"
                id="table-transaksii"
                url="/transaksii?exclude_status=selesai"
                :columns="columns"
            /> -->
        </div>
    </div>
</template>
