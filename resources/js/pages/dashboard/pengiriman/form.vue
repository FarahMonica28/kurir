<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { kurir, Pengiriman } from "@/types";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
  selected: {
    type: String, // Ubah ke Number karena ID di database bertipe integer
    default: null,
  },
});

const emit = defineEmits(["close", "refresh"]);

// const pengiriman = ref<Pengiriman>({
//   id: 0,
//   no_resi: "",
//   paket: "",
//   penerima: "",
//   alamat: "",
//   kurir: {
//     id: 0,
//     nama: "",
//   },
//   status: "dikemas",
//   tanggal_pengiriman: null,
//   tanggal_penerimaan: null,
//   biaya: 0,
// });
const pengiriman = ref<Pengiriman>({} as Pengiriman);
  const kurir = ref<kurir>({} as kurir);
const formRef = ref();

// Skema validasi Yup
const formSchema = Yup.object().shape({
  // no_resi: Yup.string().required("No. Resi harus diisi"),
  paket: Yup.string().required("Nama paket harus diisi"),
  penerima: Yup.string().required("Nama penerima harus diisi"),
  alamat: Yup.string().required("Alamat harus diisi"),
  kurir_id: Yup.string().required("Id kurir harus diisi"),
  status: Yup.string().oneOf(["dikemas", "dikirim", "diterima"]).required("Pilih status pengiriman"),
  tanggal_pengiriman: Yup.date().nullable(),
  tanggal_penerimaan: Yup.date().nullable(),
  biaya: Yup.number().min(0).required("Biaya harus diisi"),
});

// Ambil data untuk Edit
function getEdit() {
  block(document.getElementById("form-pengiriman"));
  ApiService.get("pengiriman", props.selected)
    .then(({ data }) => {
      pengiriman.value = data.pengiriman;
    })
    .catch((err: any) => {
      toast.error(err.response?.data?.message || "Terjadi kesalahan saat mengambil data pengiriman");
    })
    .finally(() => {
      unblock(document.getElementById("form-pengiriman"));
    });
}


// Submit data (Tambah/Edit)
function submit() {
  const formData = new FormData();
  formData.append("no_resi", pengiriman.value.no_resi);
  formData.append("paket", pengiriman.value.paket);
  formData.append("penerima", pengiriman.value.penerima);
  formData.append("alamat", pengiriman.value.alamat);
  formData.append("kurir_id", Number(pengiriman.value.kurir_id)); // Kirim ID kurir
  formData.append("status", pengiriman.value.status);
  formData.append("biaya", String(pengiriman.value.biaya));
  formData.append("tanggal_penerimaan", Date(pengiriman.value.tanggal_penerimaan));
  formData.append("tanggal_pengiriman", Date(pengiriman.value.tanggal_pengiriman));

  if (pengiriman.value.tanggal_pengiriman) {
    formData.append("tanggal_pengiriman", pengiriman.value.tanggal_pengiriman);
  }

  if (pengiriman.value.tanggal_penerimaan) {
    formData.append("tanggal_penerimaan", pengiriman.value.tanggal_penerimaan);
  }

  if (props.selected) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-pengiriman"));

  axios({
    method: "post",
    url: props.selected ? `/pengiriman/${props.selected}` : "/pengiriman/store",
    data: formData,
    headers: {
      "Content-Type": "multipart/form-data",
    },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data pengiriman berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      formRef.value.setErrors(err.response.data.errors);
      toast.error("Gagal menyimpan data: " + err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-pengiriman"));
    });
}

// Load data jika ID berubah
onMounted(() => {
  if (props.selected) {
    getEdit();
  }
});

watch(
  () => props.selected,
  () => {
    if (props.selected) {
      getEdit();
    }
  }
);
</script>

<template>
  <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-pengiriman" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Pengiriman</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">No. Resi</label>
            <Field class="form-control" type="text" name="no_resi" v-model="pengiriman.no_resi"
              placeholder="Masukkan No. Resi" />
            <ErrorMessage name="no_resi" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Nama Paket</label>
            <Field class="form-control" type="text" name="paket" v-model="pengiriman.paket"
              placeholder="Masukkan Nama Paket" />
            <ErrorMessage name="paket" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Nama Penerima</label>
            <Field class="form-control" type="text" name="penerima" v-model="pengiriman.penerima"
              placeholder="Masukkan Nama Penerima" />
            <ErrorMessage name="penerima" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Alamat</label>
            <Field class="form-control" type="text" name="alamat" v-model="pengiriman.alamat"
              placeholder="Masukkan Alamat" />
            <ErrorMessage name="alamat" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">ID Kurir</label>
            <Field class="form-control" type="text" name="kurir_id" v-model="pengiriman.kurir_id" placeholder="Masukan ID kurir"/>
            <ErrorMessage name="kurir_id" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Biaya (Rp)</label>
            <Field class="form-control" type="number" name="biaya" v-model="pengiriman.biaya" placeholder="Masukkan Biaya" />
            <ErrorMessage name="biaya" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Status</label>
            <Field as="select" class="form-select" name="status" v-model="pengiriman.status">
              <option value="dikemas">Dikemas</option>
              <option value="dikirim">Dikirim</option>
              <option value="diterima">Diterima</option>
            </Field>
            <ErrorMessage name="status" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Tanggal Pengiriman</label>
            <Field class="form-control" type="date" name="tanggal_pengiriman" v-model="pengiriman.tanggal_pengiriman" />
            <ErrorMessage name="tanggal_pengiriman" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6">Tanggal Penerimaan</label>
            <Field class="form-control" type="date" name="tanggal_penerimaan" v-model="pengiriman.tanggal_penerimaan" />
            <ErrorMessage name="tanggal_penerimaan" class="text-danger" />
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary btn-sm ms-auto">Simpan</button>
    </div>
  </VForm>
</template>
