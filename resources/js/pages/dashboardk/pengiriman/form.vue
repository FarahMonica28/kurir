<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Pengirimans } from "@/types";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
  selected: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["close", "refresh"]);
const pengirimans = ref<Pengirimans>({} as Pengirimans);
// const pengirimans = ref<Pengirimans>({
//   no_resi: "",
//   // paket: "",
//   penerima: "",
//   alamat_tujuan: "",
//   status: "belum_diambil",
// });

const formRef = ref();

const formSchema = Yup.object().shape({
  no_resi: Yup.string().required("No. Resi harus diisi"),
  paket: Yup.string().required("Nama paket harus diisi"),
  penerima: Yup.string().required("Nama penerima harus diisi"),
  alamat_tujuan: Yup.string().required("Alamat tujuan harus diisi"),
  status: Yup.string()
    .oneOf(["belum_diambil", "diambil", "sedang_dikirim", "terkirim", "gagal"])
    .required("Status harus diisi"),
});

const getEdit = () => {
  block(document.getElementById("form-pengirimans"));
  ApiService.get("pengirimans", props.selected)
    .then(({ data }) => {
      pengirimans.value = data.pengirimans;
    })
    .catch((err: any) => {
      toast.error(err.response?.data?.message || "Gagal mengambil data");
    })
    .finally(() => {
      unblock(document.getElementById("form-pengirimans"));
    });
};

const submit = () => {
  const formData = new FormData();
  formData.append("no_resi", pengirimans.value.no_resi);
  // formData.append("paket", pengirimans.value.paket || "");
  formData.append("penerima", pengirimans.value.penerima);
  formData.append("alamat_tujuan", pengirimans.value.alamat_tujuan);
  formData.append("status", pengirimans.value.status);

  if (props.selected) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-pengirimans"));

  axios({
    method: "post",
    url: props.selected ? `/pengirimans/${props.selected}` : "/pengirimans/store",
    data: formData,
    headers: { "Content-Type": "multipart/form-data" },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data pengiriman berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      formRef.value?.setErrors?.(err.response?.data?.errors || {});
      toast.error("Gagal menyimpan data: " + err.response?.data?.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-pengirimans"));
    });
};

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
  <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-pengirimans" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Pengiriman</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 mb-4">
          <label class="form-label fw-bold">No. Resi</label>
          <Field class="form-control" type="text" name="no_resi" v-model="pengirimans.no_resi" placeholder="Masukkan No. Resi" />
          <ErrorMessage name="no_resi" class="text-danger" />
        </div>

        <div class="col-md-6 mb-4">
          <label class="form-label fw-bold">Nama Paket</label>
          <Field class="form-control" type="text" name="paket" v-model="pengirimans.paket" placeholder="Masukkan Nama Paket" />
          <ErrorMessage name="paket" class="text-danger" />
        </div>

        <div class="col-md-6 mb-4">
          <label class="form-label fw-bold">Nama Penerima</label>
          <Field class="form-control" type="text" name="penerima" v-model="pengirimans.penerima" placeholder="Masukkan Nama Penerima" />
          <ErrorMessage name="penerima" class="text-danger" />
        </div>

        <div class="col-md-6 mb-4">
          <label class="form-label fw-bold">Alamat Tujuan</label>
          <Field class="form-control" type="text" name="alamat_tujuan" v-model="pengirimans.alamat_tujuan" placeholder="Masukkan Alamat Tujuan" />
          <ErrorMessage name="alamat_tujuan" class="text-danger" />
        </div>

        <div class="col-md-6 mb-4">
          <label class="form-label fw-bold">Status</label>
          <Field as="select" class="form-select" name="status" v-model="pengirimans.status">
            <option value="belum_diambil">Belum Diambil</option>
            <option value="diambil">Diambil dari Gudang</option>
            <option value="sedang_dikirim">Sedang Dikirim</option>
            <option value="terkirim">Terkirim</option>
            <option value="gagal">Gagal</option>
          </Field>
          <ErrorMessage name="status" class="text-danger" />
        </div>
      </div>
    </div>
    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary btn-sm ms-auto">Simpan</button>
    </div>
  </VForm>
</template>
