<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import ApiService from "@/core/services/ApiService";
// import { useKurir } from "@/services/useKurir";
// import { useStatusPengiriman } from "@/services/useStatusPengiriman";
import type { transaksi } from "@/types";

import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const currentKurir = computed(() => authStore.user); // misalnya di sini tersimpan data kurir yang login
// Cek jika kurir sudah memiliki orderan yang sedang diproses
const kurirOrder = ref(null);


const props = defineProps({
  selected: { type: String, default: null },
});
const emit = defineEmits(["close", "refresh"]);
const transaksi = ref<transaksi>({} as transaksi);
const formRef = ref();
const biayaOtomatis = computed(() => {
  const berat = parseFloat(transaksi.value.jarak);
  return isNaN(berat) ? 0 : berat * 10000;
});
const statuses = [
  { label: "Penjemputan Barang", value: "Penjemputan Barang" },
  { label: "Sedang Dikirim", value: "Sedang Dikirim" },
  { label: "Terkirim", value: "Terkirim" },
];



const formSchema = Yup.object().shape({
  // no_transaksi: Yup.string().required("Nomor Transaksi harus diisi"),
  nama_barang: Yup.string().required("Nama Barang harus diisi"),
  // pengirim: Yup.string().required("Nama Pengirim harus diisi"),
  penerima: Yup.string().required("Nama Penerima harus diisi"),
  // pengirim: Yup.string().required("Nama Pengirim harus diisi"),
  alamat_asal: Yup.string().required("Alamat Asal harus diisi"),
  alamat_tujuan: Yup.string().required("Alamat Tujuan harus diisi"),
  no_hp_penerima: Yup.string().required("No HP Penerima harus diisi"),
  // jarak: Yup.number().required("Berat Barang harus diisi"),
  // biaya: Yup.number().required("Biaya harus diisi"),
  // waktu: Yup.string().required("Waktu harus diisi"),
  // kurir_id: Yup.string().required("Kurir harus dipilih"),
  // status: Yup.string().required("Status harus dipilih"),
});

function getEdit() {
  block(document.getElementById("form-transaksi"));
  ApiService.get("trans", props.selected)
    .then(({ data }) => {
      // order.value = data.order;
      console.log(data);
      transaksi.value = {
        nama_barang: data.nama_barang || "",
        penerima: data.penerima || "",
        pengirim: data.pengguna?.id || "",
        // pengguna_id: data.pengguna_id || "",
        // pengguna_id: data.pengguna?.pengguna_id || '',
        pengguna: data.pengguna,
        alamat_asal: data.alamat_asal || "",
        alamat_tujuan: data.alamat_tujuan || "",
        no_hp_penerima: data.no_hp_penerima || "",
        jarak: data.jarak || "",
        biaya: data.biaya || "",
        status: data.status || "belum_dikirim",
        // kurir_id: data.kurir.user.kurir_id || "",        // status: data.status || "belum dikirim" || "Sedang Dikirim" || "Terkirim",
        // status: data.status || "belum_dikirim",
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

  console.log("id", transaksi.value.id);
  formData.append("nama_barang", transaksi.value.nama_barang);
  formData.append("penerima", transaksi.value.penerima);
  formData.append("pengirim", transaksi.value.pengirim);
  formData.append("alamat_asal", transaksi.value.alamat_asal);
  formData.append("alamat_tujuan", transaksi.value.alamat_tujuan);
  formData.append("no_hp_penerima", transaksi.value.no_hp_penerima);
  formData.append("jarak", transaksi.value.jarak);
  formData.append("biaya", biayaOtomatis.value.toString());
  formData.append("status", transaksi.value.status);
  formData.append("kurir_id", currentKurir.value.kurir.kurir_id);

  if (props.selected) {
    formData.append("_method", "PUT");
  } else {
    //   formData.append("waktu", new Date().toISOString());
    formData.append("status", transaksi.value.status || "belum_dikirim");

  }


  block(document.getElementById("form-transaksi"));
  axios({
    method: "post",
    url: props.selected ? `/trans/${props.selected}` : "/trans/store",
    data: formData,
    headers: {
      "Content-Type": "multipart/form-data",
    },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      formRef.value.setErrors(err.response.data.errors);
      toast.error(err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-transaksi"));
    });
}

watch(
  () => transaksi.value.jarak,
  (newBerat) => {
    const berat = parseFloat(newBerat);
    if (!isNaN(berat)) {
      transaksi.value.biaya = (berat * 10000).toString(); // harga per kg: 10.000
    } else {
      transaksi.value.biaya = "0";
    }
  }
);


onMounted(() => {

  // jika form baru (bukan edit), isi otomatis kurir dari yang login
  transaksi.value.kurir_id = currentKurir.value?.kurir.kurir_id || "";
  console.log(transaksi.value.kurir_id)
  if (props.selected) getEdit();
});
// onMounted(() => {
//   if (props.selected) getEdit();
// });

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
        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Nama Barang</label>
          <Field class="form-control" name="nama_barang" v-model="transaksi.nama_barang" disabled />
          <ErrorMessage name="nama_barang" class="text-danger small" />
        </div>


        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Alamat Asal</label>
          <Field class="form-control" name="alamat_asal" v-model="transaksi.alamat_asal" disabled />
          <ErrorMessage name="alamat_asal" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Alamat Tujuan</label>
          <Field class="form-control" name="alamat_tujuan" v-model="transaksi.alamat_tujuan" disabled />
          <ErrorMessage name="alamat_tujuan" class="text-danger small" />
        </div>

        <!-- <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Pengirim</label>
          <Field class="form-control" name="pengguna_id" v-model="transaksi.pengirim" disabled />
          <ErrorMessage name="pengguna_id" class="text-danger small" />
        </div> -->

        <!-- Tampilkan nama pengguna -->
        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Pengirim</label>
          <input type="text" class="form-control" :value="transaksi.pengguna?.user?.name" disabled />
        </div>

        <!-- Hidden field supaya id pengguna tetap dikirim -->
        <Field name="pengguna_id" type="hidden" :value="transaksi.pengguna?.pengguna_id" />
        <ErrorMessage name="pengguna_id" class="text-danger small" />


        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Penerima</label>
          <Field class="form-control" name="penerima" v-model="transaksi.penerima" disabled />
          <ErrorMessage name="penerima" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">No Hp Penerima</label>
          <Field class="form-control" name="no_hp_penerima" v-model="transaksi.no_hp_penerima" disabled />
          <ErrorMessage name="no_hp_penerima" class="text-danger small" />
        </div>

        <div class="col-md-3 mb-7">
          <label class="form-label fw-bold">jarak (km)</label>
          <Field type="number" class="form-control" name="jarak" v-model="transaksi.jarak"
            placeholder="0" />
          <ErrorMessage name="jarak" class="text-danger small" />
        </div>

        <div class="col-md-3 mb-7">
          <label class="form-label fw-bold">Biaya (Rp)</label>
          <Field type="number" class="form-control" name="biaya" :modelValue="biayaOtomatis" disabled />
          <!-- <Field type="number" class="form-control" name="biaya" v-model="transaksi.biaya" /> -->
          <ErrorMessage name="biaya" class="text-danger small" />
        </div>

        <div class="col-md-3 mb-7">
          <label class="form-label fw-bold">Status </label>
          <Field as="select" name="status" class="form-select" v-model="transaksi.status">
            <option value="" disabled>Pilih Status</option>
            <option value="Belum Terkirim" disabled>Belum Dikirim</option>
            <option value="Penjemputan Barang" :disabled="['Sedang Dikirim', 'Terkirim'].includes(transaksi.status)">
              Penjemputan Barang
            </option>
            <option value="Sedang Dikirim" :disabled="transaksi.status === 'Terkirim'">
              Sedang Dikirim
            </option>
            <option value="Terkirim">
              Terkirim
            </option>
          </Field>
          <ErrorMessage name="status" class="text-danger small" />
        </div>

        <div class="col-md-3 mb-7">
          <label class="form-label required fw-bold" for="kurir">Kurir</label>
          <!-- <Field type="text" name="kurir_id" class="form-control" v-model="transaksi.kurir_id"> -->
          <Field type="text" name="kurir_id" class="form-control" :value="`${currentKurir.name}`" readonly>
          </Field>
          <ErrorMessage name="kurir_id" class="text-danger small" />
        </div>

      </div>
    </div>

    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary ms-auto">simpan</button>
    </div>
  </VForm>
</template>
