<script setup lang="ts">
  // Import statement untuk menggunakan berbagai fitur dari Vue, axios, dan Toastify
  import { block, unblock } from "@/libs/utils"; // Fungsi untuk menampilkan/menghilangkan loading block
  import { onMounted, ref, watch, computed } from "vue"; // Vue lifecycle hooks dan reactivity API
  import * as Yup from "yup"; // Untuk validasi schema
  import axios from "@/libs/axios"; // Axios instance untuk request HTTP
  import { toast } from "vue3-toastify"; // Untuk menampilkan pesan toast
  import ApiService from "@/core/services/ApiService"; // Untuk request API umum
  import type { transaksi } from "@/types"; // Tipe data untuk transaksi
  import Swal from "sweetalert2";

  import { useAuthStore } from "@/stores/auth"; // Mengambil data dari store auth (pengguna yang sedang login)

  // Mengambil store auth untuk mendapatkan data kurir yang sedang login
  const authStore = useAuthStore();
  const currentKurir = computed(() => authStore.user); // Mengambil data pengguna yang sedang login

  // Cek jika kurir sudah memiliki orderan yang sedang diproses
  const kurirOrder = ref(null);

  // Mendefinisikan props untuk menerima data dari komponen induk, khususnya 'selected'
  const props = defineProps({
    selected: { type: String, default: null },
  });

  // Emit untuk mengirimkan event ke komponen induk
  const emit = defineEmits(["close", "refresh"]);

  // Variabel ref untuk menyimpan data transaksi yang sedang diproses
  const transaksi = ref<transaksi>({} as transaksi);

  // Ref untuk form (untuk reset form)
  const formRef = ref();

  // Menghitung biaya secara otomatis berdasarkan jarak
  // const biayaOtomatis = computed(() => {
  //   const jarak = parseFloat(transaksi.value?.jarak || '0');
  //   return isNaN(jarak) ? 0 : jarak * 10000; // Jika jarak tidak valid, biaya = 0
  // });

const biayaOtomatis = computed(() => {
  const jarak = parseFloat(transaksi.value?.jarak || '0');

  if (isNaN(jarak) || jarak <= 0) {
    return 0; // Jika jarak tidak valid atau nol
  }

  if (jarak <= 3) {
    return 10000; // Biaya minimum untuk jarak 1–3 km
  }

  const tambahanKm = Math.ceil(jarak - 3); // Bulatkan ke atas
  return 10000 + (tambahanKm * 4000);
});


  // Fungsi untuk mengambil data transaksi yang akan diedit
  function getEdit() {
    block(document.getElementById("form-transaksi")); // Menampilkan loading indicator
    ApiService.get("trans", props.selected) // Mengambil data transaksi berdasarkan ID (props.selected)
      .then(({ data }) => {
        console.log(data);
        transaksi.value = {
          // Menyimpan data transaksi yang diterima dari API ke ref transaksi
          nama_barang: data.nama_barang || "",
          penerima: data.penerima || "",
          pengirim: data.pengguna?.id || "",
          pengguna: data.pengguna,
          alamat_asal: data.alamat_asal || "",
          alamat_tujuan: data.alamat_tujuan || "",
          no_hp_penerima: data.no_hp_penerima || "",
          jarak: data.jarak || "",
          biaya: data.biaya || "",
          // status: data.status || "belum_dikirim", // Status default jika tidak ada
        };
        console.log(transaksi);
      })
      .catch((err: any) => {
        toast.error(err.response.data.message); // Menampilkan pesan error jika gagal
      })
      .finally(() => {
        unblock(document.getElementById("form-transaksi")); // Menghilangkan loading indicator
      });
  }

  // Fungsi untuk submit form (tambah/ubah data transaksi)
  function submit() {
    const formData = new FormData();

    console.log("id", transaksi.value.id); // Menampilkan ID transaksi yang akan diupdate
    formData.append("jarak", transaksi.value.jarak); // Menambahkan data jarak ke form
    formData.append("biaya", biayaOtomatis.value.toString()); // Menambahkan biaya yang dihitung secara otomatis
    // formData.append("status", transaksi.value.status); // Menambahkan status
    formData.append("kurir_id", currentKurir.value.kurir.kurir_id); // Menambahkan ID kurir yang login

    // Mengecek apakah form untuk edit atau create
    if (props.selected) {
      formData.append("_method", "PUT"); // Jika edit, menambahkan _method untuk PUT
    } else {
      // formData.append("waktu", new Date().toISOString()); // Bisa ditambahkan jika perlu waktu transaksi
      formData.append("status", transaksi.value.status || "belum_dikirim"); // Status default
    }

    block(document.getElementById("form-transaksi")); // Menampilkan loading indicator
    axios({
      method: "post", // Mengirim request POST atau PUT tergantung kondisi
      url: props.selected ? `/trans/${props.selected}` : "/trans/store", // URL yang digunakan untuk request
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data", // Mengirim data dalam format form-data
      },
    })
      .then(() => {
        emit("close"); // Emit event untuk menutup modal
        emit("refresh"); // Emit event untuk menyegarkan data di komponen induk
        toast.success("Data berhasil disimpan"); // Menampilkan pesan sukses
        formRef.value.resetForm(); // Mereset form setelah submit
      })
      .catch((err: any) => {
        formRef.value.setErrors(err.response.data.errors); // Menampilkan error jika ada
        toast.error(err.response.data.message); // Menampilkan pesan error
      })
      .finally(() => {
        unblock(document.getElementById("form-transaksi")); // Menghilangkan loading indicator
      });
  }

  // Watcher untuk mendeteksi perubahan pada 'jarak' dan menghitung biaya otomatis
  // watch(
  //   () => transaksi.value.jarak,
  //   (newJarak) => {
  //     const jarak = parseFloat(newJarak);
  //     if (!isNaN(jarak)) {
  //       transaksi.value.biaya = (jarak * 5000).toString(); // Menghitung biaya berdasarkan jarak
  //     } else {
  //       transaksi.value.biaya = "0"; // Jika jarak tidak valid, biaya = 0
  //     }
  //   }
  // );
  watch(
  () => transaksi.value.jarak,
  (newJarak) => {
    const jarak = parseFloat(newJarak);
    if (!isNaN(jarak)) {
      if (jarak <= 3) {
        transaksi.value.biaya = "10000";
      } else {
        const tambahanKm = Math.ceil(jarak - 3); // Bulatkan ke atas
        const biaya = 10000 + (tambahanKm * 4000);
        transaksi.value.biaya = biaya.toString();
      }
    } else {
      transaksi.value.biaya = "0"; // Jika jarak tidak valid
    }
  }
);


  // Lifecycle hook onMounted untuk inisialisasi data saat komponen dipasang
  onMounted(() => {
    // Jika form baru (bukan edit), otomatis mengisi data kurir yang login
    transaksi.value.kurir_id = currentKurir.value?.kurir.kurir_id || "";
    console.log(transaksi.value.kurir_id);
    if (props.selected) getEdit(); // Jika ada selected, ambil data transaksi untuk diedit
  });

  // Watcher untuk mendeteksi perubahan pada props.selected
  watch(
    () => props.selected,
    () => {
      if (props.selected) getEdit(); // Jika props.selected berubah, ambil data untuk edit
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

        <!-- <div class="col-md-3 mb-7">
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
        </div> -->

        <div class="col-md-3 mb-7">
          <label class="form-label required fw-bold" for="kurir">Kurir</label>
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
