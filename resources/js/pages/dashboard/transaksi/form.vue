<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import ApiService from "@/core/services/ApiService";
// import { useStatusPengiriman } from "@/services/useStatusPengiriman";
import type { transaksi } from "@/types";
import { useAuthStore } from "@/stores/auth";
// import { GMapAutocomplete } from '@fawmi/vue-google-maps'

const authStore = useAuthStore();
const currentPengguna = computed(() => authStore.user);
const props = defineProps({
  selected: { type: String, default: null },
});
const emit = defineEmits(["close", "refresh"]);
const transaksi = ref<transaksi>({} as transaksi);
const formRef = ref();

const formSchema = Yup.object().shape({
  nama_barang: Yup.string().required("Nama Barang harus diisi"),
  // pengirim: Yup.string().required("Nama Pengirim harus diisi"),
  penerima: Yup.string().required("Nama Penerima harus diisi"),
  alamat_asal: Yup.string().required("Alamat Asal harus diisi"),
  alamat_tujuan: Yup.string().required("Alamat Tujuan harus diisi"),
  no_hp_penerima: Yup.string().required("No HP Penerima harus diisi"),
});
function setAlamatAsal(place: any) {
  transaksi.value.alamat_asal = place.formatted_address
}

function setAlamatTujuan(place: any) {
  transaksi.value.alamat_tujuan = place.formatted_address
}

function getEdit() {
  block(document.getElementById("form-transaksi"));
  ApiService.get("transaksi", props.selected)
    .then(({ data }) => {
      // order.value = data.order;
      console.log(data);
      transaksi.value = {
        nama_barang: data.nama_barang || "",
        penerima: data.penerima || "",
        // pengirim: data.pengirim || "",
        // pengguna_id: data.pengguna_id || "",
        alamat_asal: data.alamat_asal || "",
        alamat_tujuan: data.alamat_tujuan || "",
        no_hp_penerima: data.no_hp_penerima || "",
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

  console.log(currentPengguna.value.id);
  formData.append("id", currentPengguna.value.id);
  formData.append("nama_barang", transaksi.value.nama_barang);
  formData.append("penerima", transaksi.value.penerima);
  // formData.append("pengirim", transaksi.value.pengirim);
  // formData.append("pengguna_id", currentPengguna.value.pengguna.pengguna_id);
  formData.append("alamat_asal", transaksi.value.alamat_asal);
  formData.append("alamat_tujuan", transaksi.value.alamat_tujuan);
  formData.append("no_hp_penerima", transaksi.value.no_hp_penerima);

  if (props.selected) {
    formData.append("_method", "PUT");
  } else {
    formData.append("waktu", new Date().toISOString());
    formData.append("status", "belum terkirim");
  }


  block(document.getElementById("form-transaksi"));
  axios({
    method: "post",
    url: props.selected ? `/transaksi/${props.selected}` : "/transaksi/store",
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

onMounted(() => {
  if (props.selected) getEdit();
});


watch(
  () => props.selected,
  () => {
    if (props.selected) getEdit();
  }
);
// onMounted(() => { & watch
//   if (props.selected) antar();
// });

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
          <Field class="form-control" name="nama_barang" v-model="transaksi.nama_barang"
            placeholder="Masukan nama barang" />
          <ErrorMessage name="nama_barang" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Alamat Asal</label>
          <Field class="form-control" name="alamat_asal" v-model="transaksi.alamat_asal"
            placeholder="Jl. kebun jeruk" />
          <ErrorMessage name="alamat_asal" class="text-danger small" />
        </div>
        <!-- <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Alamat Asal</label>
          <GMapAutocomplete class="form-control" placeholder="Cari alamat asal" @place_changed="setAlamatAsal" />
          <ErrorMessage name="alamat_asal" class="text-danger small" />
        </div> -->

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Alamat Tujuan</label>
          <Field class="form-control" name="alamat_tujuan" v-model="transaksi.alamat_tujuan"
            placeholder=" Jl. Bringin Baru" />
          <ErrorMessage name="alamat_tujuan" class="text-danger small" />
        </div>
        <!-- <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Alamat Tujuan</label>
          <GMapAutocomplete class="form-control" placeholder="Cari alamat tujuan" @place_changed="setAlamatTujuan" />
          <ErrorMessage name="alamat_tujuan" class="text-danger small" />
        </div> -->

        <!-- <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Pengirim</label>
          <Field class="form-control" name="pengirim" v-model="transaksi.pengirim"  placeholder="Masukan nama pengirim"/>
          <ErrorMessage name="pengirim" class="text-danger small" />
        </div> -->
        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold" for="pengguna">Pengirim</label>
          <Field type="text" name="pengguna_id" class="form-control" :value="`${currentPengguna.name}`" readonly>
          </Field>
          <ErrorMessage name="pengguna_id" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Penerima</label>
          <Field class="form-control" name="penerima" v-model="transaksi.penerima"
            placeholder="Masukan nama penerima" />
          <ErrorMessage name="penerima" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">No Hp Penerima</label>
          <Field class="form-control" name="no_hp_penerima" v-model="transaksi.no_hp_penerima"
            placeholder="Masukan na hp penrima" />
          <ErrorMessage name="no_hp_penerima" class="text-danger small" />
        </div>

      </div>
    </div>

    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary ms-auto">Buat Order</button>
    </div>
  </VForm>
</template>
