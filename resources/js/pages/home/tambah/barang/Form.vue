<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
    selected: {
        type: String,
        default: null,
    },
});
const emit = defineEmits(["succes", "refresh"]);
// const emit = defineEmits(["close", "refresh"]);

const barang = ref<any>({});
const photo = ref<any>([]);
const formRef = ref();
const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);

const kategoriList = ref<any[]>([]);

const formSchema = Yup.object().shape({
    nama: Yup.string().required("Nama barang harus diisi"),
    stok: Yup.number().required("Stok harus diisi"),
    harga_sewa: Yup.string().required("Harga sewa harus diisi"),
    kategori_id: Yup.string().required("Kategori harus dipilih"),
    deskripsi: Yup.string().nullable(),
});

function getKategori() {
    ApiService.get("/tambah/kategori")
        .then((res) => {
            console.log("Response:", res.data);
            kategoriList.value = res.data.map((item: any) => ({
                id: item.id,
                text: item.nama,
            }));
            console.log("Kategori List:", kategoriList.value);
        })

        .catch(() => toast.error("Gagal mengambil data kategori"));
}

function getEdit() {
    block(document.getElementById("form-barang"));
    ApiService
        .get(`tambah/barang/${props.selected}`)
        .then(({ data }) => {
            console.log("Edit Data:", data);
            barang.value = data;
            barang.value.kategori_id = data.kategori.id
            photo.value = data.photo
                ? ["/storage/" + data.photo]
                : [];
            console.log("Barang after edit:", barang.value);
            // console.log("Photo after edit:", photo.value);
        })
        .catch((err: any) => toast.error(err.response.data.message))
        .finally(() => unblock(document.getElementById("form-barang")));
}

function submit() {
    const formData = new FormData();
    formData.append("nama", barang.value.nama);
    formData.append("stok", barang.value.stok);
    formData.append("harga_sewa", barang.value.harga_sewa);
    formData.append("kategori_id", barang.value.kategori_id);
    formData.append("deskripsi", barang.value.deskripsi || "");

    if (photo.value.length) {
        formData.append("photo", photo.value[0].file);
    }

    if (props.selected) {
        formData.append("_method", "PUT");
    }

    block(document.getElementById("form-barang"));
    axios({
        method: "post",
        url: props.selected
            ? `/tambah/barang/${props.selected}`
            : "/tambah/barang/store",
        data: formData,
        headers: {
            "Content-Type": "multipart/form-data",
        },
    })
        .then(() => {
            emit("succes");
            emit("close");
            emit("refresh");
            toast.success("Data barang berhasil disimpan");
            formRef.value.resetForm();
        })
        .catch((err: any) => {
            formRef.value.setErrors(err.response.data.errors);
            toast.error(err.response.data.message);
        })
        .finally(() => unblock(document.getElementById("form-barang")));
}

onMounted(() => {
    getKategori();
    if (props.selected) getEdit();
});

watch(() => props.selected, () => {
    if (props.selected) getEdit();
});
</script>

<template>
    <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-barang" ref="formRef">
        <div class="card-header align-items-center">
            <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Barang</h2>
            <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
                Batal
                <i class="la la-times-circle p-0"></i>
            </button>
        </div>
        <div class="card-body row">
            <div class="col-md-6 mb-5">
                <label class="form-label required">Nama Barang</label>
                <Field class="form-control" name="nama" v-model="barang.nama" placeholder="Nama Barang" />
                <ErrorMessage name="nama" class="text-danger" />
            </div>

            <div class="col-md-6 mb-5">
                <label class="form-label required">Stok</label>
                <Field type="number" class="form-control" name="stok" v-model="barang.stok" placeholder="Jumlah stok" />
                <ErrorMessage name="stok" class="text-danger" />
            </div>

            <div class="col-md-6 mb-5">
                <label class="form-label required">Harga Sewa</label>
                <Field type="string" class="form-control" name="harga_sewa" v-model="barang.harga_sewa"
                    placeholder="Harga Sewa" />
                <ErrorMessage name="harga_sewa" class="text-danger" />
            </div>

            <div class="col-md-6 mb-5">
                <label class="form-label required">Kategori</label>
                <Field name="kategori_id" type="hidden" v-model="barang.kategori_id">
                    <select2 class="form-select" :options="kategoriList" placeholder="Pilih kategori"
                        v-model="barang.kategori_id" />
                </Field>
                <ErrorMessage name="kategori_id" class="text-danger" />
            </div>

            <div class="col-md-12 mb-5">
                <label class="form-label">Deskripsi</label>
                <Field as="textarea" class="form-control" name="deskripsi" v-model="barang.deskripsi"
                    placeholder="Deskripsi barang" />
                <ErrorMessage name="deskripsi" class="text-danger" />
            </div>

            <div class="col-md-12 mb-5">
                <label class="form-label">Foto Barang</label>
                <file-upload :files="photo" :accepted-file-types="fileTypes"
                    v-on:updatefiles="(file) => (photo = file)" />
                <ErrorMessage name="photo" class="text-danger" />
            </div>
        </div>
        <div class="card-footer d-flex">
            <button type="submit" class="btn btn-primary btn-sm ms-auto">
                Simpan
            </button>
        </div>
    </VForm>
</template>
