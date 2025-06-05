<script setup lang="ts">
import { computed, h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { transaksii } from "@/types";
import axios from "axios";
import Swal from "sweetalert2";


const column = createColumnHelper<transaksii>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);


const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("no_resi", {
        header: " No Resi",
    }),
    // column.accessor("pengguna.name", {
    //     header: "Pengirim",
    // }),
    column.accessor("nama_barang", {
        header: "Nama Barang",
    }),
    column.accessor("tujuan_provinsi.name", {
        header: "Provinsi Tujuan",
    }),
    column.accessor("tujuan_kota.name", {
        header: "Kota Tujuan",
    }),
    column.accessor("alamat_asal", {
        header: "Alamat Pengmbilan barang",
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
                                            ? "badge bg-dark text-white fw-bold" 
                                            : "badge bg-secondary fw-bold";


            return h("span", { class: statusClass }, status);
        },
    }),
    column.accessor("id", {
        header: "Aksi",
        cell: (cell) => {
            const row = cell.row.original;
            const status = row.status;

            let buttonText = "";
            let nextStatus = "";
            let swalTitle = "";
            let swalText = "";

            if (status === "menunggu") {
                buttonText = "Ambil";
                nextStatus = "diambil kurir";
                swalTitle = "Ambil Orderan";
                swalText = "Apakah Anda akan mengambil orderan ini?";
            } else if (status === "diambil kurir") {
                buttonText = "Ambil Barang";
                nextStatus = "dikurir";
                swalTitle = "Apakah barang sudah di tangan Anda?";
                // swalText = "Apakah barang sudah di tangan Anda?";
            } else if (status === "dikurir") {
                buttonText = "Digudang";
                nextStatus = "digudang";
                swalTitle = "Kirim ke Gudang";
                swalText = "Apakah Anda sudah mengirim barang ke gudang?";
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
                        refresh(); // refresh data table

                        await Swal.fire({
                            title: "Berhasil!",
                            // text: `Status berhasil diubah menjadi "${nextStatus}".`,
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false,
                        });
                    } catch (error) {
                        console.error("Gagal update status:", error);
                        Swal.fire({
                            title: "Gagal!",
                            text: "Terjadi kesalahan saat mengubah status.",
                            icon: "error",
                        });
                    }
                }
            };

            return h(
                "button",
                {
                    class: "btn btn-sm btn-primary",
                    onClick: handleClick,
                },
                buttonText
            );
        }

    }),

];
const url = computed(() => {
    const params = new URLSearchParams();
    ['digudang', 'tiba digudang', 'diproses', 'dikirim', 'selesai'].forEach(status => {
        params.append('exclude_status[]', status);
    });
    return `/transaksii?${params.toString()}`;
});


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
            <h2 class="mb-0">List Orderan</h2>
        </div>
        <div class="card-body">
            <!-- <paginate ref="paginateRef" id="table-transaksi" url="/transaksi" :columns="columns"></paginate> -->
            <paginate ref="paginateRef" id="table-transaksii" :url=url
                :columns="columns" />

        </div>
    </div>
</template>
