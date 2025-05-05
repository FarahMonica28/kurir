<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import ApiService from "@/core/services/ApiService";
import type { transaksi } from "@/types";
import Swal from "sweetalert2";

const props = defineProps({
  selected: { type: String, default: null },
});
const emit = defineEmits(["close", "refresh"]);
const transaksi = ref<transaksi>({} as transaksi);
const formRef = ref();

const formSchema = Yup.object().shape({
  penilaian: Yup.string().required("Penilaian harus diisi"),
  komentar: Yup.string().required("komentar harus diisi"),
});

function getEdit() {
  block(document.getElementById("form-transaksi"));
  ApiService.get("transaksi", props.selected)
    .then(({ data }) => {
      // order.value = data.order;
      console.log(data);
      transaksi.value = {
        id: data.id, // ← Tambahkan id
        penilaian: data.penilaian || "",
        komentar: data.komentar || "",
      };

      console.log(transaksi);
    })
    .catch((err: any) => {
      toast.error(err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-transaksi"));
    });
}



function submit() {
  const formData = new FormData();
  formData.append("id", props.selected || "");
  formData.append("penilaian", transaksi.value.penilaian || '');
  formData.append("komentar", transaksi.value.komentar || '');

  block(document.getElementById("form-transaksi"));

  axios({
    method: "post",
    url: "/transaksi/storer", // <- pastikan endpoint sesuai
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
        text: "Terima kasih atas penilaiannya!",
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
        text: err.response?.data?.message || "Gagal menyimpan penilaian",
        customClass: {
          popup: "swal-fire"
        }
      });
    })
    .finally(() => {
      unblock(document.getElementById("form-transaksi"));
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
  <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-transaksi" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Order</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>

    <div class="card-body">
      <div class="row">

        <div class="col-md-12 mb-7">
          <label class="form-label fw-bold">Penilaian</label>
          <Field type="number" class="form-control" name="penilaian" v-model="transaksi.penilaian" placeholder="Contoh: 1-100" />
          <ErrorMessage name="penilaian" class="text-danger small" />
        </div><br>

        <div class="col-md-12 mb-7">
          <label class="form-label fw-bold">Komentar</label>
          <Field as="textarea" rows="2" class="form-control" name="komentar" v-model="transaksi.komentar" placeholder="Tulis komentar..." />
          <ErrorMessage name="komentar" class="text-danger small" />
        </div>
      </div>
    </div>

    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary ms-auto">Simpan</button>
    </div>
  </VForm>
</template>
