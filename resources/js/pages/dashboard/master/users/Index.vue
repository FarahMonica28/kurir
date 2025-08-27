<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { User } from "@/types";

const column = createColumnHelper<User>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deleteUser } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

import axios from "axios";
import Swal from "sweetalert2";

const otpCode = ref("");
const verifyingUserId = ref("");

const sendOtp = async (uuid: string) => {
    try {
        await axios.post(`/master/otp/send/${uuid}`);
        verifyingUserId.value = uuid;

        Swal.fire({
            title: "Verifikasi Email",
            input: "text",
            inputLabel: "Masukkan Kode OTP yang dikirim ke email",
            inputPlaceholder: "Contoh: 123456",
            showCancelButton: true,
            confirmButtonText: "Verifikasi",
            preConfirm: (otp) => {
                otpCode.value = otp;
                return verifyOtp();
            },
        });
    } catch (error) {
        console.error(error);
        Swal.fire("Gagal", "Gagal mengirim kode OTP.", "error");
    }
};

const verifyOtp = async () => {
    try {
        const res = await axios.post(`/master/otp/verify/${verifyingUserId.value}`, {
            otp: otpCode.value,
        });
        if (res.data.success) {
            Swal.fire("Berhasil", "Email berhasil diverifikasi!", "success");
            refresh();
        } else {
            Swal.fire("Gagal", "Kode OTP salah atau sudah kadaluarsa.", "error");
        }
    } catch (error) {
        console.error(error);
        Swal.fire("Gagal", "Terjadi kesalahan saat verifikasi.", "error");
    }
};

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("name", {
        header: "Nama",
    }),
    column.accessor("email", {
        header: "Email",
    }),
    column.accessor("phone", {
        header: "No. Telp",
    }),
    column.accessor("roles_name", {
        header: "Role",
    }),
    column.accessor("email_verified_at", {
        header: "Verifikasi",
        cell: (cell) => {
            const row = cell.row.original;

            // Jika email_verified_at null → tombol verifikasi
            if (!row.email_verified_at) {
                return h(
                    "button",
                    {
                        class: "btn btn-sm btn-warning d-flex align-items-center gap-1",
                        onClick: () => sendOtp(row.uuid),
                    },
                    [
                        h("i", { class: "la la-key fs-2" }),
                        h("span", { class: "fw-semibold" }, "Verifikasi"),
                    ]
                );
            }

            // Jika email_verified_at terisi → tampilkan tulisan
            return h(
                "span",
                { class: "text-success fw-semibold" },
                "Sudah Terverifikasi"
            );
        },
    }),
    column.accessor("uuid", {
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
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () => deleteUser(`/master/users/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),

    // column.accessor("uuid", {
    //     header: "Aksi",
    //     cell: (cell) => {
    //         const row = cell.row.original;

    //         return h("div", { class: "d-flex gap-2" }, [
    //             h(
    //                 "button",
    //                 {
    //                     class: "btn btn-sm btn-icon btn-info",
    //                     onClick: () => {
    //                         selected.value = cell.getValue();
    //                         openForm.value = true;
    //                     },
    //                 },
    //                 h("i", { class: "la la-pencil fs-2" })
    //             ),
    //             h(
    //                 "button",
    //                 {
    //                     class: "btn btn-sm btn-icon btn-danger",
    //                     onClick: () => deleteUser(`/master/users/${cell.getValue()}`),
    //                 },
    //                 h("i", { class: "la la-trash fs-2" })
    //             ),
    //             !row.email_verified_at &&
    //             h(
    //                 "button",
    //                 {
    //                     class: "btn btn-sm btn-warning d-flex align-items-center gap-1",
    //                     onClick: () => sendOtp(cell.getValue()),
    //                 },
    //                 [h("i", { class: "la la-key fs-2" }), "Verifikasi" ]// Tombol OTP

    //             ),
    //         ]);
    //     },
    // }),


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
            <h2 class="mb-0">List Users</h2>
            <button type="button" class="btn btn-sm btn-primary ms-auto" v-if="!openForm" @click="openForm = true">
                Tambah
                <i class="la la-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <paginate ref="paginateRef" id="table-users" url="/master/users" :columns="columns"></paginate>
        </div>
    </div>
</template>
