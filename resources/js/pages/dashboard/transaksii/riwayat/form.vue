<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import ApiService from "@/core/services/ApiService";
import type { transaksii } from "@/types";
import Swal from "sweetalert2";
import { useForm, Field, ErrorMessage, Form as VForm } from "vee-validate";


const props = defineProps({
    selected: { type: String, default: null },
});
const emit = defineEmits(["close", "refresh"]);
const transaksii = ref<transaksii>({} as transaksii);
const formRef = ref();
const rating = ref("");
const komentar = ref("");

const formSchema = Yup.object().shape({
    // rating: Yup.string().required("Penilaian harus diisi"),
    rating: Yup.string()
  .required("Penilaian harus diisi")
  .oneOf(["1", "2", "3", "4", "5"], "Rating harus antara 1 sampai 5"),
    komentar: Yup.string().required("komentar harus diisi"),
});

function getEdit() {
    block(document.getElementById("form-transaksii"));
    ApiService.get("transaksii", props.selected)
        .then(({ data }) => {
            // order.value = data.order;
            console.log(data);
            transaksii.value = {
                id: data.id, // ← Tambahkan id
                rating: data.rating || "",
                komentar: data.komentar || "",
            };

            console.log(transaksii);
        })
        .catch((err: any) => {
            toast.error(err.response.data.message);
        })
        .finally(() => {
            unblock(document.getElementById("form-transaksii"));
        });
}



function submit() {
    const formData = new FormData();
    formData.append("id", props.selected || "");
    formData.append("rating", rating.value || '');
    formData.append("komentar", komentar.value || '');

    block(document.getElementById("form-transaksii"));

    axios({
        method: "post",
        url: "/rating", // <- pastikan endpoint sesuai
        data: formData,
        headers: {
            "Content-Type": "multipart/form-data",
        },
    })
        .then(() => {
            emit("close");
            emit("refresh");
            formRef.value.resetForm();

            Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "Terima kasih atas ratingnya!",
                customClass: {
                    popup: "swal-fire"
                }
            });
        })
        .catch((err: any) => {
            if (err.response?.data?.errors) {
                formRef.value.setErrors(err.response.data.errors);
            }

            Swal.fire({
                icon: "error",
                title: "Gagal",
                text: err.response?.data?.message || "Gagal menyimpan rating",
                customClass: {
                    popup: "swal-fire"
                }
            });
        })
        .finally(() => {
            unblock(document.getElementById("form-transaksii"));
        });
}



onMounted(() => {
    if (props.selected) getEdit();
});

watch(
    () => props.selected,
    () => {
        if (props.selected) getEdit();
    }
);
</script>

<template>
    <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-transaksii" ref="formRef">
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Riwayat</h2>
            <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
                Batal <i class="la la-times-circle p-0"></i>
            </button>
        </div>

        <div class="card-body">
            <div class="row">

                <!-- <div class="col-md-3 mb-7">
          <label class="form-label fw-bold">Rating</label>
          <Field type="number" class="form-control" name="rating" v-model="rating" placeholder="Contoh: 1/5" />
          <ErrorMessage name="rating" class="text-danger small" />
        </div> -->

                <div class="col-md-3 mb-7">
                    <label class="form-label fw-bold">Rating</label>
                    <Field as="select" class="form-select" name="rating" v-model="rating">
                        <option disabled value="">-- Pilih rating --</option>
                        <option v-for="n in 5" :key="n" :value="n">{{ '⭐'.repeat(n) }} ({{ n }})</option>
                    </Field>
                    <ErrorMessage name="rating" class="text-danger small" />
                </div>
                
                <div class="col-md-6 mb-7">
                    <label class="form-label fw-bold">Komentar</label>
                    <Field as="textarea" rows="2" class="form-control" name="komentar" v-model="komentar"
                        placeholder="Tulis komentar..." />
                    <ErrorMessage name="komentar" class="text-danger small" />
                </div>

            </div>
        </div>

        <div class="card-footer d-flex">
            <button type="submit" class="btn btn-primary ms-auto">Simpan</button>
        </div>
    </VForm>
</template>
