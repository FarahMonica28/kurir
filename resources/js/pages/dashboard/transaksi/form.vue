<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import ApiService from "@/core/services/ApiService";
// import { useStatusPengiriman } from "@/services/useStatusPengiriman";
import type { transaksi } from "@/types";

const props = defineProps({
  selected: { type: String, default: null },
});
const emit = defineEmits(["close", "refresh"]);
const transaksi = ref<transaksi>({} as transaksi);
const formRef = ref();

const formSchema = Yup.object().shape({
  // no_transaksi: Yup.string().required("Nomor Transaksi harus diisi"),
  nama_barang: Yup.string().required("Nama Barang harus diisi"),
  // pengirim: Yup.string().required("Nama Pengirim harus diisi"),
  penerima: Yup.string().required("Nama Penerima harus diisi"),
  pengirim: Yup.string().required("Nama Pengirim harus diisi"),
  alamat_asal: Yup.string().required("Alamat Asal harus diisi"),
  alamat_tujuan: Yup.string().required("Alamat Tujuan harus diisi"),
  no_hp_penerima: Yup.string().required("No HP Penerima harus diisi"),
});

function getEdit() {
  block(document.getElementById("form-transaksi"));
  ApiService.get("transaksi", props.selected)
    .then(({ data }) => {
      // order.value = data.order;
      console.log(data);
      transaksi.value = {
        nama_barang: data.nama_barang || "",
        penerima: data.penerima || "",
        pengirim: data.pengirim || "",
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
// function antar() {
//   const formData = new FormData();
//   formData.append("id", transaksi.value.id);
//   formData.append("status", "belum dikirim", "sedang dikirim", "terkirim"); // atau sesuaikan nilai status yang kamu pakai
//   formData.append("berat_barang", transaksi.value.berat_barang?.toString() || "0");
//   formData.append("biaya", transaksi.value.biaya?.toString() || "0");

//   block(document.getElementById("form-transaksi"));
//   axios({
//     method: "get",
//     url: `/transaksi/${props.selected}`,
//     data: formData,
//     headers: {
//       "Content-Type": "multipart/form-data",
//     },
//   })
//     // .then(() => {
//     //   emit("close");
//     //   emit("refresh");
//     //   toast.success("Status berhasil diubah menjadi dikirim");
//     // })
//     .catch((err: any) => {
//       toast.error(err.response.data.message || "Gagal mengubah status");
//     })
//     .finally(() => {
//       unblock(document.getElementById("form-transaksi"));
//     });
// }


// const kurir = useKurir();
// const status = useStatusPengiriman();

// const listKurir = computed(() =>
//   kurir.data.value?.map((item: Kurir) => ({
//     id: item.id,
//     text: item.user?.name ?? "-",
//   }))
// );

// const listStatus = computed(() =>
//   status.data.value?.map((item: PengirimanStatus) => ({
//     id: item.id,
//     text: item.nama_status,
//   }))
// );

function submit() {
  const formData = new FormData();

  formData.append("id", transaksi.value.id);
  formData.append("nama_barang", transaksi.value.nama_barang);
  formData.append("penerima", transaksi.value.penerima);
  formData.append("pengirim", transaksi.value.pengirim);
  formData.append("alamat_asal", transaksi.value.alamat_asal);
  formData.append("alamat_tujuan", transaksi.value.alamat_tujuan);
  formData.append("no_hp_penerima", transaksi.value.no_hp_penerima);
  // formData.append("berat_barang", transaksi.value.berat_barang);
  // formData.append("biaya", transaksi.value.biaya);
  // formData.append("waktu", transaksi.value.waktu);
  // formData.append("status", transaksi.value.status);
  // formData.append("penilaian", transaksi.value.penilaian || ''); // opsional
  // formData.append("komentar", transaksi.value.komentar || '');   // opsional
  // formData.append("kurir_id", transaksi.value.kurir_id);

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
          <Field class="form-control" name="nama_barang" v-model="transaksi.nama_barang" placeholder="Masukan nama barang"/>
          <ErrorMessage name="nama_barang" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Alamat Asal</label>
          <Field class="form-control" name="alamat_asal" v-model="transaksi.alamat_asal" placeholder="Jl. kebun jeruk" />
          <ErrorMessage name="alamat_asal" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Alamat Tujuan</label>
          <Field class="form-control" name="alamat_tujuan" v-model="transaksi.alamat_tujuan"  placeholder=" Jl. Bringin Baru"/>
          <ErrorMessage name="alamat_tujuan" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Pengirim</label>
          <Field class="form-control" name="pengirim" v-model="transaksi.pengirim"  placeholder="Masukan nama pengirim"/>
          <ErrorMessage name="pengirim" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Penerima</label>
          <Field class="form-control" name="penerima" v-model="transaksi.penerima"  placeholder="Masukan nama penerima"/>
          <ErrorMessage name="penerima" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">No Hp Penerima</label>
          <Field class="form-control" name="no_hp_penerima" v-model="transaksi.no_hp_penerima"  placeholder="Masukan na hp penrima" />
          <ErrorMessage name="no_hp_penerima" class="text-danger small" />
        </div>

        <!-- <div class="col-md-3 mb-7">
          <label class="form-label required fw-bold">Berat Barang (kg)</label>
          <Field type="number" class="form-control" name="berat_barang" v-model="order.berat_barang" />
          <ErrorMessage name="berat_barang" class="text-danger small" />
        </div> -->

        <!-- <div class="col-md-3 mb-7">
          <label class="form-label required fw-bold">Biaya (Rp)</label>
          <Field type="number" class="form-control" name="biaya" v-model="order.biaya" />
          <ErrorMessage name="biaya" class="text-danger small" />
        </div> -->

        <!-- <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Waktu</label>
          <Field type="datetime-local" class="form-control" name="waktu" v-model="order.waktu" />
          <ErrorMessage name="waktu" class="text-danger small" />
        </div> -->

        <!-- <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Kurir</label>
          <Field name="kurir_id" v-model="order.kurir_id">
            <select2 class="form-select-solid" :options="listKurir" placeholder="Pilih Kurir" v-model="order.kurir_id" />
          </Field>
          <ErrorMessage name="kurir_id" class="text-danger small" />
        </div> -->

        <!-- <div class="col-md-6 mb-7">
          <label class="form-label required fw-bold">Status</label>
          <Field name="status" v-model="order.status">
            <select2 class="form-select-solid" :options="listStatus" placeholder="Pilih Status" v-model="order.status" />
          </Field>
          <ErrorMessage name="status" class="text-danger small" />
        </div> -->

        <!-- <div class="col-md-6 mb-7">
          <label class="form-label fw-bold">Penilaian</label>
          <Field type="number" class="form-control" name="penilaian" v-model="order.penilaian" />
          <ErrorMessage name="penilaian" class="text-danger small" />
        </div> -->

        <!-- <div class="col-md-12 mb-7">
          <label class="form-label fw-bold">Komentar</label>
          <Field as="textarea" rows="2" class="form-control" name="komentar" v-model="order.komentar" />
          <ErrorMessage name="komentar" class="text-danger small" />
        </div> -->
      </div>
    </div>

    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary ms-auto">Buat Order</button>
    </div>
  </VForm>
</template>
