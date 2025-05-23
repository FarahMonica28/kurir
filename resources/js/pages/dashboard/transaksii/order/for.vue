<script setup lang="ts">
import { ref, onMounted } from "vue";
import axios from "axios";
import { useForm, Field, ErrorMessage } from "vee-validate";
import * as Yup from "yup";

// State provinsi & kota
const provinces = ref<Record<string, string>>({});
const citiesOrigin = ref<Record<string, string>>({});
const citiesDestination = ref<Record<string, string>>({});
const provinceOrigin = ref("0");
const cityOrigin = ref("");
const provinceDestination = ref("0");
const cityDestination = ref("");

// Form validation schema
const formSchema = Yup.object().shape({
  nama_barang: Yup.string().required("Nama Barang harus diisi"),
  penerima: Yup.string().required("Nama Penerima harus diisi"),
  // alamat_asal: Yup.string().required("Alamat Asal harus diisi"),
  // alamat_tujuan: Yup.string().required("Alamat Tujuan harus diisi"),
  no_hp_penerima: Yup.string().required("No HP Penerima harus diisi"),
});

const { handleSubmit, errors, resetForm, values } = useForm({
  validationSchema: formSchema,
});

const fetchProvinces = async () => {
  const res = await axios.get("/provinces"); // backend Anda harus sediakan endpoint ini
  provinces.value = res.data;
};

const fetchCities = async (type: "origin" | "destination") => {
  const provId = type === "origin" ? provinceOrigin.value : provinceDestination.value;
  if (provId !== "0") {
    const res = await axios.get(`/cities/${provId}`);
    if (type === "origin") {
      citiesOrigin.value = res.data;
      cityOrigin.value = "";
    } else {
      citiesDestination.value = res.data;
      cityDestination.value = "";
    }
  }
};

// Submit form
const handleSubmitForm = handleSubmit(async (formValues) => {
  const payload = {
    ...formValues,
    province_origin: provinceOrigin.value,
    city_origin: cityOrigin.value,
    province_destination: provinceDestination.value,
    city_destination: cityDestination.value,
  };

  try {
    await axios.post("/api/transaksi", payload);
    alert("Transaksi berhasil disimpan");
    resetForm();
    provinceOrigin.value = "0";
    provinceDestination.value = "0";
    cityOrigin.value = "";
    cityDestination.value = "";
  } catch (err) {
    alert("Terjadi kesalahan saat menyimpan data");
  }
});

// Inisialisasi saat mount
onMounted(() => {
  fetchProvinces();
});
</script>

<template>
  <form @submit.prevent="handleSubmitForm">
    <div class="card-body">
      <div class="row">
        
        <!-- NAMA BARANG -->
        <div class="col-md-6 mb-7">
          <label class="form-label">Nama Barang</label>
          <Field name="nama_barang" class="form-control" />
          <ErrorMessage name="nama_barang" class="text-danger" />
        </div>
    
        <!-- PENERIMA -->
        <div class="col-md-6 mb-7">
          <label class="form-label">Nama Penerima</label>
          <Field name="penerima" class="form-control" />
          <ErrorMessage name="penerima" class="text-danger" />
        </div>
    
        <!-- ALAMAT ASAL -->
        <!-- <div class="col-md-6 mb-7">
          <label class="form-label">Alamat Asal</label>
          <Field name="alamat_asal" as="textarea" class="form-control" />
          <ErrorMessage name="alamat_asal" class="text-danger" />
        </div> -->
    
        <!-- PROVINSI ASAL -->
        <div class="col-md-6 mb-7">
          <label class="form-label">Provinsi Asal</label>
          <select v-model="provinceOrigin" class="form-control" @change="fetchCities('origin')">
            <option value="0">-- Pilih Provinsi Asal --</option>
            <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
          </select>
          <div v-if="errors.provinceOrigin" class="text-danger">{{ errors.provinceOrigin }}</div>
        </div>
    
        <!-- KOTA ASAL -->
        <div class="col-md-6 mb-7">
          <label class="form-label">Kota Asal</label>
          <select v-model="cityOrigin" class="form-control">
            <option value="">-- Pilih Kota Asal --</option>
            <option v-for="(name, id) in citiesOrigin" :key="id" :value="id">{{ name }}</option>
          </select>
          <div v-if="errors.cityOrigin" class="text-danger">{{ errors.cityOrigin }}</div>
        </div>
    
        
        <!-- PROVINSI TUJUAN -->
        <div class="col-md-6 mb-7">
          <label class="form-label">Provinsi Tujuan</label>
          <select v-model="provinceDestination" class="form-control" @change="fetchCities('destination')">
            <option value="0">-- Pilih Provinsi Tujuan --</option>
            <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
          </select>
          <div v-if="errors.provinceDestination" class="text-danger">{{ errors.provinceDestination }}</div>
        </div>
        
        <!-- KOTA TUJUAN -->
        <div class="col-md-6 mb-7">
          <label class="form-label">Kota Tujuan</label>
          <select v-model="cityDestination" class="form-control">
            <option value="">-- Pilih Kota Tujuan --</option>
            <option v-for="(name, id) in citiesDestination" :key="id" :value="id">{{ name }}</option>
          </select>
          <div v-if="errors.cityDestination" class="text-danger">{{ errors.cityDestination }}</div>
        </div>
        
        <!-- ALAMAT TUJUAN -->
        <div class="col-md-6 mb-7">
          <label class="form-label">Alamat Lengkap Tujuan</label>
          <Field name="alamat_tujuan" class="form-control" />
          <ErrorMessage name="alamat_tujuan" class="text-danger" />
        </div>

        <!-- NOMOR HP PENERIMA -->
        <div class="col-md-6 mb-7">
          <label class="form-label">Nomor HP Penerima</label>
          <Field name="no_hp_penerima" class="form-control" />
          <ErrorMessage name="no_hp_penerima" class="text-danger" />
        </div>


      </div>
    </div>

    <!-- SUBMIT -->
    <div class="text-end">
      <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
  </form>
</template>