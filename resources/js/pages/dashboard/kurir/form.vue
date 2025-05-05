<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { kurir } from "@/types";
import ApiService from "@/core/services/ApiService";

const props = defineProps({
  selected: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["close", "refresh"]);
// // const photo = ref<any>([]);
const photo = ref<any[]>([]);
const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);
const kurir = ref({
  status: "",
  // rating: null,
  user: {
    name: "",
    email: "",
    phone: "",
    photo: "",
  },
} as any); // atau define tipe kurir baru
const formRef = ref();
const avgRating = ref(0);


const formSchema = Yup.object().shape({
  // kurir_id: Yup.string().required("ID Kurir harus diisi"),
  name: Yup.string().required("Nama kurir harus diisi"),
  email: Yup.string()
    .email("Email harus valid")
    .required("Email harus diisi"),
  phone: Yup.string().required("Nomor Telepon harus diisi"),
  password: Yup.string()
    .min(6, "Password minimal 8 karakter")
    .optional(), // opsional saat edit
  status: Yup.string().required("Pilih status kurir"),
  // rating: Yup.number().min(1).max(5).required("Pilih rating"),
});


function getEdit() {
  block(document.getElementById("form-kurir"));
  ApiService.get("kurir", props.selected)
    .then(({ data }) => {
      console.log(data);
      // Pastikan struktur sesuai response dar i backend
      kurir.value = {
        status: data.user.status || "nonaktif", // Tambahkan status,
        // status: data.user?.status || "Nonaktif", // Tambahkan status,
        user: {
          name: data.user?.name || "",
          email: data.user?.email || "",
          phone: data.user?.phone || "",
          photo: data.user?.photo || "",
        },
      };
      console.log(kurir.value);
      photo.value = data.user.photo
        ? ["/storage/" + data.user.photo]
        : [];

      kurir.value.password = ""; // kosongkan password saat edit
    })
    .catch((err: any) => {
      toast.error(err.response?.data?.message || "Gagal mengambil data");
    })
    .finally(() => {
      unblock(document.getElementById("form-kurir"));
    });
}

function submit() {
  const formData = new FormData();
  // formData.append("kurir_id", kurir.value.kurir_id);
  formData.append("name", kurir.value.user.name);
  formData.append("email", kurir.value.user.email);
  formData.append("phone", kurir.value.user.phone);
  formData.append("photo", kurir.value.user.photo);
  formData.append("status", kurir.value.status);
  // formData.append("rating", kurir.value.rating.toString());

  if (photo.value.length && photo.value[0].file) {
    formData.append("photo", photo.value[0].file);
  }


  if (props.selected) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-kurir"));
  axios({
    method: "post",
    url: props.selected ? `/kurir/${props.selected}` : "/kurir/store",
    data: formData,
    headers: {
      "Content-Type": "multipart/form-data",
    },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data kurir berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      formRef.value.setErrors(err.response.data.errors);
      toast.error("Gagal menyimpan data: " + err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-kurir"));
    });
}

onMounted(async () => {
  const { data } = await axios.get('/kurir/ringkasan');
  avgRating.value = data.avg_rating;
});
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
  <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-kurir" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Kurir</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="row">

        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Nama Kurir</label>
            <Field class="form-control form-control-lg form-control-solid" type="text" name="name"
              v-model="kurir.user.name" placeholder="Masukkan Nama" />
            <ErrorMessage name="name" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Email</label>
            <Field class="form-control form-control-lg form-control-solid" type="email" name="email"
              v-model="kurir.user.email" placeholder="Masukkan Email" />
            <ErrorMessage name="email" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Nomor Telepon</label>
            <Field class="form-control form-control-lg form-control-solid" type="text" name="phone"
              v-model="kurir.user.phone" placeholder="Masukkan Nomor Telepon" />
            <ErrorMessage name="phone" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6">Password</label>
            <Field class="form-control form-control-lg form-control-solid" type="password" name="password"
              v-model="kurir.user.password" placeholder="Masukkan Password (opsional saat edit)" />
            <ErrorMessage name="password" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6">Foto Profil</label>
            <file-upload :files="photo" :accepted-file-types="fileTypes"
              v-on:updatefiles="(file) => (photo = file)"></file-upload>
            <ErrorMessage name="photo" class="text-danger" />
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary btn-sm ms-auto">Simpan</button>
    </div>
  </VForm>
</template>
