<script setup lang="ts">
import { block, unblock } from "@/libs/utils"; // Import utilitas block/unblock untuk memunculkan loading overlay saat proses async
import { onMounted, ref, watch, computed } from "vue"; // Import fitur Vue yang digunakan
import * as Yup from "yup"; // Yup digunakan untuk validasi form
import axios from "@/libs/axios"; // Axios instance untuk melakukan HTTP request
import { toast } from "vue3-toastify"; // Notifikasi toast
import ApiService from "@/core/services/ApiService"; // ApiService custom untuk GET/POST/PUT/DELETE API
import type { transaksi } from "@/types"; // Tipe TypeScript untuk objek transaksi
import { useAuthStore } from "@/stores/auth";// Store autentikasi untuk mendapatkan data pengguna login
import Swal from 'sweetalert2';

// import komponen Google Maps Autocomplete jika dibutuhkan nanti
// import { GMapAutocomplete } from '@fawmi/vue-google-maps'

// Ambil data user login dari auth store
const authStore = useAuthStore();
const currentPengguna = computed(() => authStore.user); // Pengguna yang sedang login

// Props untuk menentukan apakah sedang dalam mode edit (selected != null)
const props = defineProps({
  selected: { type: String, default: null },
});
// Emit untuk mengirim event keluar dari komponen
const emit = defineEmits(["close", "refresh"]);

// State utama untuk menyimpan data transaksi yang sedang diedit atau dibuat
const transaksi = ref<transaksi>({} as transaksi);

// Referensi ke form untuk validasi dan reset
const formRef = ref();

// Skema validasi form menggunakan Yup
const formSchema = Yup.object().shape({
  nama_barang: Yup.string().required("Nama Barang harus diisi"),
  penerima: Yup.string().required("Nama Penerima harus diisi"),
  alamat_asal: Yup.string().required("Alamat Asal harus diisi"),
  alamat_tujuan: Yup.string().required("Alamat Tujuan harus diisi"),
  no_hp_penerima: Yup.string().required("No HP Penerima harus diisi"),
});

// Fungsi untuk mengatur alamat asal dari komponen Google Maps Autocomplete
function setAlamatAsal(place: any) {
  transaksi.value.alamat_asal = place.formatted_address;
}

// Fungsi untuk mengatur alamat tujuan dari komponen Google Maps Autocomplete
function setAlamatTujuan(place: any) {
  transaksi.value.alamat_tujuan = place.formatted_address;
}

// Jika dalam mode edit, ambil data transaksi berdasarkan ID (props.selected)
function getEdit() {
  block(document.getElementById("form-transaksi"));
  ApiService.get("transaksi", props.selected)
    .then(({ data }) => {
      // Map data dari response ke dalam form transaksi
      transaksi.value = {
        nama_barang: data.nama_barang || "",
        penerima: data.penerima || "",
        alamat_asal: data.alamat_asal || "",
        alamat_tujuan: data.alamat_tujuan || "",
        no_hp_penerima: data.no_hp_penerima || "",
      };
    })
    .catch((err: any) => {
      toast.error(err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-transaksi"));
    });
}

// Fungsi untuk submit data form, baik untuk create maupun update
function submit() {
  const formData = new FormData();

  // Set field untuk dikirim ke backend
  formData.append("id", currentPengguna.value.id);
  formData.append("nama_barang", transaksi.value.nama_barang);
  formData.append("penerima", transaksi.value.penerima);
  formData.append("alamat_asal", transaksi.value.alamat_asal);
  formData.append("alamat_tujuan", transaksi.value.alamat_tujuan);
  formData.append("no_hp_penerima", transaksi.value.no_hp_penerima);

  // Jika mode edit, tambahkan _method PUT
  if (props.selected) {
    formData.append("_method", "PUT");
  } else {
    // Jika tambah baru, isi waktu & status default
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
      emit("close");    // Tutup form/modal
      emit("refresh");  // Refresh data list
      toast.success("Data berhasil disimpan");
      formRef.value.resetForm(); // Reset form setelah submit
    })
    .catch((err: any) => {
      // Tampilkan error validasi ke form
      if (err.response?.data?.errors) {
        formRef.value.setErrors(err.response.data.errors);
      }

      const message = err.response?.data?.message || 'Terjadi kesalahan.';

      // Cek apakah error 422 dan pesan khusus tentang kurir tidak tersedia
      if (err.response?.status === 422) {
        // Tampilkan SweetAlert jika tidak ada kurir tersedia
        Swal.fire({
          icon: 'warning',
          title: 'Tidak Ada Kurir Aktif yang Tersedia',
          // text: message,
        });
      } else {
        // Jika error lain, gunakan toast biasa
        // toast.error(message);
      }
    })
    .finally(() => {
      unblock(document.getElementById("form-transaksi"));
    });
}

// Saat komponen pertama kali dimount, jika mode edit, panggil getEdit()
onMounted(() => {
  if (props.selected) getEdit();
});

// Jika props.selected berubah (misalnya user pilih transaksi lain), ambil ulang data
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
      <button type="submit" class="btn btn-primary ms-auto">
        <i class="bi bi-cloud-check-fill"></i> Buat Order
      </button>
    </div>

  </VForm>
</template>
