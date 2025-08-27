<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { ref, watch, onMounted } from "vue";
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

const emit = defineEmits(["close", "refresh"]);

const kategori = ref<{ nama: string }>({ nama: "" });
const formRef = ref();

const formSchema = Yup.object().shape({
  nama: Yup.string().required("Nama kategori harus diisi"),
});

function getEdit() {
  block(document.getElementById("form-kategori"));
  ApiService
    .get(`tambah/kategori/${props.selected}`)
    .then(({ data }) => {
      kategori.value = {
        nama: data.nama,
      };
    })
    .catch((err: any) => {
      toast.error(err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-kategori"));
    });
}

function submit() {
  const formData = new FormData();
  formData.append("nama", kategori.value.nama);

  if (props.selected) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-kategori"));
  axios({
    method: "post",
    url: props.selected
      ? `/tambah/kategori/${props.selected}`
      : "/tambah/kategori/store",
    data: formData,
    headers: {
      "Content-Type": "multipart/form-data",
    },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Kategori berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      formRef.value.setErrors(err.response.data.errors);
      toast.error(err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-kategori"));
    });
}

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
  <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-kategori" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Kategori</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal
        <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="fv-row mb-7">
        <label class="form-label fw-bold fs-6 required">Nama Kategori</label>
        <Field class="form-control form-control-lg form-control-solid" type="text" name="nama" autocomplete="off"
          v-model="kategori.nama" placeholder="Contoh: Laptop, Kamera" />
        <div class="fv-plugins-message-container">
          <div class="fv-help-block">
            <ErrorMessage name="nama" />
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary btn-sm ms-auto">
        Simpan
      </button>
    </div>
  </VForm>
</template>
