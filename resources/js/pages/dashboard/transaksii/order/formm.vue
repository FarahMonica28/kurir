<script setup lang="ts">
import { onMounted, ref, computed, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import { block, unblock } from "@/libs/utils";
import { useAuthStore } from "@/stores/auth";
import { useForm } from "vee-validate";
import type { transaksii } from "@/types";

// ==========================
// Auth Store
// ==========================
const authStore = useAuthStore();
const currentPengguna = computed(() => authStore.user);

// ==========================
// Props dan Emits
// ==========================
const props = defineProps({ selected: { type: String, default: null } });
const emit = defineEmits(["close", "refresh"]);

// ==========================
// Form State
// ==========================
const formRef = ref();
const transaksi = ref<transaksii>({} as transaksii);

// ==========================
// Lokasi dan Alamat
// ==========================
const provinces = ref<Record<string, string>>({});
const provinceOrigin = ref("0");
const citiesOrigin = ref<Record<string, string>>({});
const citiesDestination = ref<Record<string, string>>({});
const cityOrigin = ref("");
// const provinceDestination = ref("0");
const cityDestination = ref("");


const districtsOrigin = ref<Record<string, string>>({});
const districtsDestination = ref<{ id: string; name: string }[]>([]);
const districtOrigin = ref("");
const districtDestination = ref("");


// ==========================
// Input Pengguna
// ==========================
const nama_barang = ref("");
const penerima = ref("");
const pengirim = ref(""); // Optional
const alamat_tujuan = ref("");
const no_hp_penerima = ref("");
const no_hp_pengirim = ref("");

// ==========================
// Ekspedisi dan Layanan
// ==========================
const couriers = ref([
  { code: "jne", name: "JNE" },
  { code: "tiki", name: "TIKI" },
  { code: "pos", name: "POS Indonesia" },
]);
const selectedCourier = ref("");
const services = ref<{ service: string; description: string; cost: number; etd: string }[]>([]);
const selectedService = ref("");
const berat_barang = ref<number | null>(null);
const biaya = ref<number>(0);

// ==========================
// Status dan Pembayaran
// ==========================
const sudahBayar = ref(false);
const metodePembayaran = ref("");
const invoiceUrl = ref("");

// ==========================
// Validasi dengan Yup
// ==========================
const formSchema = Yup.object({
  nama_barang: Yup.string().required(),
  penerima: Yup.string().required(),
  alamat_tujuan: Yup.string().required(),
  no_hp_penerima: Yup.string().required(),
  no_hp_pengirim: Yup.string().required(),
  provinceOrigin: Yup.string().required().notOneOf(["0"]),
  citiesOrigin: Yup.string().required(),
  districtOrigin: Yup.string().required(),
  provinceDestination: Yup.string().required().notOneOf(["0"]),
  citiesDestination: Yup.string().required(),
  districtDestination: Yup.string().required(),
  kurir: Yup.string().required(),
  layanan: Yup.string().required(),
  berat_barang: Yup.number().required().min(0.1),
});

const { handleSubmit, errors, resetForm, setFieldValue } = useForm({
  validationSchema: formSchema,
  initialValues: {
    nama_barang: "",
    penerima: "",
    alamat_tujuan: "",
    no_hp_penerima: "",
    no_hp_pengirim: "",
    provinceOrigin: "0",
    citiesOrigin: "",
    districtOrigin: "",
    provinceDestination: "0",
    citiesDestination: "",
    districtDestination: "",
    kurir: "",
    layanan: "",
    berat_barang: 0,
  },
});




// provinsi untuk tujuan
const searchProvinceDestination = ref("");
// const provinceDestination = ref("");
const showDropdownDestination = ref(false);

const filteredProvincesDestination = computed(() => {
  return provinceDestination.value.filter((prov) =>
    prov.name.toLowerCase().includes(searchProvinceDestination.value.toLowerCase())
  );
});

const hideDropdownWithDelay = () => {
  setTimeout(() => {
    showDropdownDestination.value = false;
  }, 200);
};
import { useField, } from "vee-validate";

// const { setFieldValue } = useForm();

const selectProvinceDestination = (prov: { id: string; name: string }) => {
  searchProvinceDestination.value = prov.name;
  provinceDestination.value = prov.id;
  setFieldValue("provinceDestination", prov.id); // agar tersimpan di form
  showDropdownDestination.value = false;
  fetchCities("destination");
};

// ==========================
// API Lokasi
// ==========================
// const fetchProvinces = async () => {
//   try {
//     const res = await axios.get("/provinces");
//     provinces.value = res.data;
//   } catch {
//     toast.error("Gagal mengambil provinsi");
//   }
// };
// kota untuk tujuan
const searchCityDestination = ref("");
// const cityDestination = ref("");
const showCityDropdownDestination = ref(false);

const filteredCitiesDestination = computed(() => {
  return citiesDestination.value.filter((city) =>
    city.name.toLowerCase().includes(searchCityDestination.value.toLowerCase())
  );
});

const selectCityDestination = (city: { id: string; name: string }) => {
  searchCityDestination.value = city.name;
  cityDestination.value = city.id;
  setFieldValue("cityDestination", city.id); // sync ke VeeValidate
  showCityDropdownDestination.value = false;
  fetchDistricts("destination");
};

const hideCityDropdownWithDelay = () => {
  setTimeout(() => {
    showCityDropdownDestination.value = false;
  }, 200);
};

// ==========================
// Kecamatan untuk tujuan
// ==========================
const searchDistrictDestination = ref("");
const showDistrictDropdownDestination = ref(false);

const filteredDistrictsDestination = computed(() => {
  return districtsDestination.value.filter((d) =>
    d.name.toLowerCase().includes(searchDistrictDestination.value.toLowerCase())
  );
});

const selectDistrictDestination = (d: { id: string; name: string }) => {
  searchDistrictDestination.value = d.name;
  districtDestination.value = d.id;
  setFieldValue("districtDestination", d.id);
  showDistrictDropdownDestination.value = false;
};

const hideDistrictDropdownDestinationWithDelay = () => {
  setTimeout(() => {
    showDistrictDropdownDestination.value = false;
  }, 200);
};


// ini untuk provinsi asal
// const provinceOrigin = ref("");
const searchProvinceOrigin = ref("");
const showProvinceDropdownOrigin = ref(false);

const filteredProvincesOrigin = computed(() => {
  return provinceOrigin.value.filter((prov) =>
    prov.name.toLowerCase().includes(searchProvinceOrigin.value.toLowerCase())
  );
});

const selectProvinceOrigin = (prov: { id: string; name: string }) => {
  searchProvinceOrigin.value = prov.name;
  provinceOrigin.value = prov.id;
  setFieldValue("provinceOrigin", prov.id); // sync ke VeeValidate
  fetchCities("origin");
  showProvinceDropdownOrigin.value = false;
};

const hideProvinceDropdownOriginWithDelay = () => {
  setTimeout(() => {
    showProvinceDropdownOrigin.value = false;
  }, 200);
};

// kota untuk asal
const searchCityOrigin = ref("");
// const cityOrigin = ref("");
const showCityDropdownOrigin = ref(false);

const filteredCitiesOrigin = computed(() => {
  return citiesOrigin.value.filter((city) =>
    city.name.toLowerCase().includes(searchCityOrigin.value.toLowerCase())
  );
});

const selectCityOrigin = (city: { id: string; name: string }) => {
  searchCityOrigin.value = city.name;
  cityOrigin.value = city.id;
  setFieldValue("cityOrigin", city.id); // sync ke VeeValidate
  showCityDropdownOrigin.value = false;
  fetchDistricts("origin");
};

const hideCityDropdownOriginWithDelay = () => {
  setTimeout(() => {
    showCityDropdownOrigin.value = false;
  }, 200);
};

const provinceDestination = ref<{ id: string; name: string }[]>([])

const fetchProvinces = async () => {
  try {
    const res = await axios.get("/provinces");

    // Jika hasilnya { "11": "Aceh", "12": "Bali", ... }
    provinceDestination.value = Object.entries(res.data).map(([id, name]) => ({
      id,
      name,
    }));
    provinceOrigin.value = Object.entries(res.data).map(([id, name]) => ({
      id,
      name,
    }));

  } catch {
    toast.error("Gagal mengambil provinsi");
  }
};
const fetchCities = async (type: "origin" | "destination") => {
  const provId = type === "origin" ? provinceOrigin.value : provinceDestination.value;
  if (provId === "0") return;
  try {
    const res = await axios.get(`/cities/${provId}`);
    // if (type === "origin") {
    //   citiesOrigin.value = res.data;
    //   cityOrigin.value = "";
    // } else {
    //   citiesDestination.value = res.data;
    //   cityDestination.value = "";
    // }
    // Di fetchCities
    if (type === "origin") {
      citiesOrigin.value = Object.entries(res.data).map(([id, name]) => ({
        id,
        name,
      }));

    } else {
      citiesDestination.value = Object.entries(res.data).map(([id, name]) => ({
        id,
        name,
      }));

    }

  } catch {
    toast.error("Gagal mengambil kota");
  }
};
const fetchDistricts = async (type: "origin" | "destination") => {
  const cityId = type === "origin" ? cityOrigin.value : cityDestination.value;
  if (!cityId) return;

  try {
    const res = await axios.get(`/districts/${cityId}`);
    const formatted = Object.entries(res.data).map(([id, name]) => ({ id, name }));

    if (type === "origin") {
      districtsOrigin.value = formatted;
    } else {
      districtsDestination.value = formatted;
    }
  } catch {
    toast.error("Gagal mengambil kecamatan");
  }
};

// ==========================
// Kecamatan untuk asal
// ==========================
const searchDistrictOrigin = ref("");
const showDistrictDropdownOrigin = ref(false);

const filteredDistrictsOrigin = computed(() => {
  return districtsOrigin.value.filter((d) =>
    d.name.toLowerCase().includes(searchDistrictOrigin.value.toLowerCase())
  );
});

const selectDistrictOrigin = (d: { id: string; name: string }) => {
  searchDistrictOrigin.value = d.name;
  districtOrigin.value = d.id;
  setFieldValue("districtOrigin", d.id);
  showDistrictDropdownOrigin.value = false;
};

const hideDistrictDropdownOriginWithDelay = () => {
  setTimeout(() => {
    showDistrictDropdownOrigin.value = false;
  }, 200);
};



const fetchOngkir = async () => {
  // Validasi input ongkir
  if (
    provinceOrigin.value === "0" || !cityOrigin.value || !districtOrigin.value ||
    provinceDestination.value === "0" || !cityDestination.value || !districtDestination.value ||
    !selectedCourier.value || !berat_barang.value || berat_barang.value <= 0
  ) {
    services.value = [];
    selectedService.value = "";
    biaya.value = 0;
    return;
  }

  try {
    block(document.getElementById("form-transaksii"));
    const res = await axios.post("/cost", {
      origin: cityOrigin.value,
      destination: cityDestination.value,
      origin: districtOrigin.value,
      destination: districtDestination.value,
      weight: Math.round(berat_barang.value * 1000),
      courier: selectedCourier.value,
    });

    services.value = res.data.map((s: any) => ({
      service: s.service,
      description: s.description,
      cost: s.cost[0].value,
      etd: s.cost[0].etd,
    }));

    selectedService.value = "";
    biaya.value = 0;
  } catch {
    toast.error("Gagal mengambil ongkir");
  } finally {
    unblock(document.getElementById("form-transaksii"));
  }
};

// ==========================
// Ongkir & Layanan
// ==========================
const getSelectedCost = () => {
  const service = services.value.find(s => s.service === selectedService.value);
  biaya.value = service?.cost ?? 0;
};
// ==========================
// Watchers
// ==========================
watch([provinceOrigin, cityOrigin, provinceDestination, cityDestination, districtDestination, districtOrigin, selectedCourier, berat_barang], fetchOngkir);
watch(selectedService, getSelectedCost);

// ==========================
// Label Status Pembayaran
// ==========================
const statusLabel = (status: string) => {
  if (status === 'lunas') return 'Lunas';
  if (status === 'pending') return 'Menunggu Pembayaran';
  if (status === 'gagal') return 'Gagal';
  return status;
};

// ==========================
// Submit Form
// ==========================
const onSubmit = () => {
  const formData = new FormData();

  formData.append("id", currentPengguna.value.id);
  formData.append("penerima", penerima.value);
  formData.append("tujuan_provinsi_id", provinceDestination.value);
  formData.append("tujuan_kota_id", cityDestination.value);
  formData.append("tujuan_kecamatan_id", districtDestination.value);
  formData.append("alamat_tujuan", alamat_tujuan.value);
  formData.append("no_hp_penerima", no_hp_penerima.value);
  formData.append("no_hp_pengirim", no_hp_pengirim.value);
  formData.append("nama_barang", nama_barang.value);
  formData.append("asal_provinsi_id", provinceOrigin.value);
  formData.append("asal_kota_id", cityOrigin.value);
  formData.append("asal_kecamatan_id", districtOrigin.value);
  formData.append("alamat_asal", transaksi.value.alamat_asal || "");
  formData.append("ekspedisi", selectedCourier.value);
  formData.append("layanan", selectedService.value);
  formData.append("berat_barang", berat_barang.value?.toString() || "0");
  formData.append("biaya", biaya.value.toString());

  if (props.selected) {
    formData.append("_method", "PUT");
  } else {
    formData.append("waktu", new Date().toISOString());
    formData.append("status", "menunggu");
  }

  block(document.getElementById("form-transaksii"));
  axios({
    method: "post",
    url: props.selected ? `/transaksii/${props.selected}` : "transaksii/store",
    data: formData,
    headers: { "Content-Type": "multipart/form-data" },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Transaksi berhasil disimpan");
      formRef.value?.resetForm();
    })
    .catch((err: any) => {
      const message = err.response?.data?.message || "Terjadi kesalahan.";
      toast.error(message);
    })
    .finally(() => {
      unblock(document.getElementById("form-transaksii"));
    });
};

// ==========================
// Lifecycle
// ==========================
onMounted(fetchProvinces);
</script>




<template>

  <VForm id="form-transaksii" class="form card mb-10" @submit="onSubmit" :validation-schema="formSchema" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ props.selected ? "Edit" : "Tambah" }} Order</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>

    <div class="card-body">
      <div class="row">

        <!-- Informasi Penerima -->
        <h2 class="text-warning">Informasi Penerima</h2>

        <!-- nama penerima -->
        <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold">Nama Penerima</label>
          <Field class="form-control" name="penerima" v-model="penerima" placeholder="Masukan nama penerima" />
          <ErrorMessage name="penerima" class="text-danger small" />
        </div>

        <!-- No Hp Penerima -->
        <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold">No Hp Penerima</label>
          <Field class="form-control" name="no_hp_penerima" v-model="no_hp_penerima"
            placeholder="Masukan no hp pengirim" />
          <ErrorMessage name="no_hp_penerima" class="text-danger small" />
        </div>

        <!-- Provinsi Tujuan -->
        <div class="col-md-4 mb-7 mt-4 position-relative">
          <label class="form-label required fw-bold">Provinsi Tujuan</label>

          <Field name="provinceDestination" v-model="searchProvinceDestination">
            <input type="text" class="form-control" v-model="searchProvinceDestination"
              placeholder="Ketik Provinsi Tujuan" @focus="showDropdownDestination = true" @blur="hideDropdownWithDelay"
              autocomplete="off" />
          </Field>
          <ErrorMessage name="provinceDestination" class="text-danger small" />

          <ul v-if="showDropdownDestination && filteredProvincesDestination.length"
            class="list-group position-absolute w-100" style="z-index: 1000;">
            <li v-for="prov in filteredProvincesDestination" :key="prov.id"
              class="list-group-item list-group-item-action" @mousedown.prevent="selectProvinceDestination(prov)">
              {{ prov.name }}
            </li>
          </ul>
        </div>

        <!-- Kota Tujuan -->
        <div class="col-md-4 mb-7 position-relative">
          <label class="form-label required fw-bold">Kota Tujuan</label>

          <Field name="citiesDestination" v-model="searchCityDestination">
            <input type="text" class="form-control" v-model="searchCityDestination" placeholder="Ketik Kota Tujuan"
              @focus="showCityDropdownDestination = true" @blur="hideCityDropdownWithDelay" autocomplete="off" />
          </Field>
          <ErrorMessage name="citiesDestination" class="text-danger small" />

          <ul v-if="showCityDropdownDestination && filteredCitiesDestination.length"
            class="list-group position-absolute w-100" style="z-index: 1000;">
            <li v-for="city in filteredCitiesDestination" :key="city.id" class="list-group-item list-group-item-action"
              @mousedown.prevent="selectCityDestination(city)">
              {{ city.name }}
            </li>
          </ul>
        </div>

        <!-- Kecamatan Tujuan -->
        <div class="col-md-4 mb-7 position-relative">
          <label class="form-label required fw-bold">Kecamatan Tujuan</label>

          <Field name="districtDestination" v-model="searchDistrictDestination">
            <input type="text" class="form-control" v-model="searchDistrictDestination"
              placeholder="Ketik Kecamatan Tujuan" @focus="showDistrictDropdownDestination = true"
              @blur="hideDistrictDropdownDestinationWithDelay" autocomplete="off" />
          </Field>
          <ErrorMessage name="districtDestination" class="text-danger small" />

          <ul v-if="showDistrictDropdownDestination && filteredDistrictsDestination.length"
            class="list-group position-absolute w-100" style="z-index: 1000;">
            <li v-for="d in filteredDistrictsDestination" :key="d.id" class="list-group-item list-group-item-action"
              @mousedown.prevent="selectDistrictDestination(d)">
              {{ d.name }}
            </li>
          </ul>
        </div>



        <!-- Alamat Tujuan -->
        <div class="col-md-4 mb-7">
          <label class="form-label required fw-bold">Alamat Lengkap Penerima</label>
          <Field type="text" name="alamat_tujuan" v-model="alamat_tujuan" class="form-control"
            placeholder="Masukan Alamat Lengkap" />
          <ErrorMessage name="alamat_tujuan" class="text-danger small" />
        </div>


        <!-- Infromasi Pengguna -->
        <h2 class="mt-7 text-purple">Informasi Pengirim</h2>
        <!-- Pengirim -->
        <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold" for="pengguna_id">Nama Pengirim</label>
          <Field type="text" name="pengguna_id" class="form-control" :value="`${currentPengguna.name}`" readonly>
          </Field>
          <ErrorMessage name="pengguna_id" class="text-danger small" />
        </div>
        <!-- <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold" for="pengirim">Nama Pengirim</label>
          <Field type="text" name="pengirim" class="form-control" v-model="pengirim"
            placeholder="Masukan nama pengirim">
          </Field>
          <ErrorMessage name="pengirim" class="text-danger small" />
        </div> -->
        <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold">No Hp Pengirim</label>
          <Field class="form-control" name="no_hp_pengirim" v-model="no_hp_pengirim"
            placeholder="Masukan no hp penerima" />
          <ErrorMessage name="no_hp_pengirim" class="text-danger small" />
        </div>

        <!-- Nama Barang -->
        <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold">Nama Barang</label>
          <Field class="form-control" name="nama_barang" placeholder="Masukan nama barang" v-model="nama_barang" />
          <ErrorMessage name="nama_barang" class="text-danger small" />
        </div>

        <!-- Berat Barang -->
        <div class="col-md-4 mb-7 ">
          <label class="form-label required fw-bold">Berat Barang (Kg)</label>
          <Field type="number" v-model="berat_barang" class="form-control" placeholder="Contoh: 0.5" min="0.1"
            step="0.1" name="berat_barang" />
          <ErrorMessage name="berat_barang" class="text-danger small" />
          <!-- <div v-if="errors.berat_barang" class="text-danger">{{ errors.berat_barang }}</div> -->
        </div>

        <!-- Provinsi Asal -->
        <!-- <div class="col-md-4 mb-7 ">
          <label class="form-label required fw-bold">Provinsi Asal</label>
          <Field as="select" name="provinceOrigin" v-model="provinceOrigin" class="form-control"
            @change="fetchCities('origin')">
            <option value="0">-- Pilih Provinsi Asal --</option>
            <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
          </Field as="select">
          <ErrorMessage name="provinceOrigin" class="text-danger small" />
        </div> -->
        <!-- Provinsi Asal -->
        <div class="col-md-4 mb-7 position-relative">
          <label class="form-label required fw-bold">Provinsi Asal</label>

          <Field name="provinceOrigin" v-model="searchProvinceOrigin">
            <input type="text" class="form-control" v-model="searchProvinceOrigin" placeholder="Ketik Provinsi Asal"
              @focus="showProvinceDropdownOrigin = true" @blur="hideProvinceDropdownOriginWithDelay"
              autocomplete="off" />
          </Field>
          <ErrorMessage name="provinceOrigin" class="text-danger small" />

          <ul v-if="showProvinceDropdownOrigin && filteredProvincesOrigin.length"
            class="list-group position-absolute w-100" style="z-index: 1000;">
            <li v-for="prov in filteredProvincesOrigin" :key="prov.id" class="list-group-item list-group-item-action"
              @mousedown.prevent="selectProvinceOrigin(prov)">
              {{ prov.name }}
            </li>
          </ul>
        </div>


        <!-- Kota Asal -->
        <!-- <div class="col-md-4 mb-7">
          <label class="form-label required fw-bold">Kota Asal</label>
          <Field as="select" name="cityOrigin" v-model="cityOrigin" class="form-control">
            <option value="">-- Pilih Kota Asal --</option>
            <option v-for="(name, id) in citiesOrigin" :key="id" :value="id">{{ name }}</option>
          </Field as="select">
          <ErrorMessage name="cityOrigin" class="text-danger small" />
          <!-- <div v-if="ErrorMessage" name="cityOrigin" class="text-danger">{{ errors.cityOrigin }}</div> 
        </div> -->
        <!-- Kota Asal -->
        <div class="col-md-4 mb-7 position-relative">
          <label class="form-label required fw-bold">Kota Asal</label>

          <Field name="citiesOrigin" v-model="searchCityOrigin">
            <input type="text" class="form-control" v-model="searchCityOrigin" placeholder="Ketik Kota Asal"
              @focus="showCityDropdownOrigin = true" @blur="hideCityDropdownOriginWithDelay" autocomplete="off" />
          </Field>
          <ErrorMessage name="citiesOrigin" class="text-danger small" />

          <ul v-if="showCityDropdownOrigin && filteredCitiesOrigin.length" class="list-group position-absolute w-100"
            style="z-index: 1000;">
            <li v-for="city in filteredCitiesOrigin" :key="city.id" class="list-group-item list-group-item-action"
              @mousedown.prevent="selectCityOrigin(city)">
              {{ city.name }}
            </li>
          </ul>
        </div>


        <!-- Kecamatan Asal -->
        <div class="col-md-4 mb-7 position-relative">
          <label class="form-label required fw-bold">Kecamatan Asal</label>

          <Field name="districtOrigin" v-model="searchDistrictOrigin">
            <input type="text" class="form-control" v-model="searchDistrictOrigin" placeholder="Ketik Kecamatan Asal"
              @focus="showDistrictDropdownOrigin = true" @blur="hideDistrictDropdownOriginWithDelay"
              autocomplete="off" />
          </Field>
          <ErrorMessage name="districtOrigin" class="text-danger small" />

          <ul v-if="showDistrictDropdownOrigin && filteredDistrictsOrigin.length"
            class="list-group position-absolute w-100" style="z-index: 1000;">
            <li v-for="d in filteredDistrictsOrigin" :key="d.id" class="list-group-item list-group-item-action"
              @mousedown.prevent="selectDistrictOrigin(d)">
              {{ d.name }}
            </li>
          </ul>
        </div>


        <!-- Alamat Asal -->
        <div class="col-md-4 mb-7">
          <label class="form-label required fw-bold">Alamat Pengambilan Barang</label>
          <Field type="text" name="alamat_asal" v-model="transaksi.alamat_asal" class="form-control"
            placeholder="Masukan Alamat Lengkap" />
          <ErrorMessage name="alamat_asal" class="text-danger small" />
        </div>

        <!-- Ekspedisi -->
        <div class="col-md-4 mb-7">
          <label class="form-label required fw-bold">Ekspedisi</label>
          <Field as="select" v-model="selectedCourier" class="form-control" name="kurir">
            <option value="">-- Pilih Ekspedisi --</option>
            <option v-for="c in couriers" :key="c.code" :value="c.code">{{ c.name }}</option>
          </Field as="select">
          <ErrorMessage name="kurir" class="text-danger small" />
          <!-- <div v-if="errors.kurir" class="text-danger">{{ errors.kurir }}</div> -->
        </div>

        <!-- Layanan -->
        <div class="col-md-4 mb-7" v-if="services.length > 0">
          <label for="layanan" class="form-label required fw-bold">Layanan</label>
          <Field as="select" id="layanan" name="layanan" class="form-select" v-model="selectedService"
            @change="getSelectedCost">
            <option value="">Pilih layanan</option>
            <option v-for="service in services" :key="service.service" :value="service.service">
              {{ service.service }} - Rp{{ Number(service.cost).toLocaleString() }} { {{ service.etd }} Hari }
            </option>
          </Field as="select">
          <ErrorMessage name="layanan" class="text-danger small" />
          <!-- <div v-if="errors.layanan" class="text-danger">{{ errors.layanan }}</div> -->
        </div>

        <!-- Biaya Otomatis (Tampilkan hanya jika layanan dipilih) -->
        <!-- <div class="col-md-4 mb-7" v-if="services.length > 0">
          <label class="form-label fw-bold">Biaya (Rp)</label>
          <input type="text" name="biaya" class="form-control" :value="biaya ? biaya.toLocaleString('id-ID') : '-'"
            readonly />
        </div>
        <button @click="bayarOngkir">Bayar Ongkir</button>

      </div>

      <div class="card-footer d-flex">
        <button type="submit" class="btn btn-primary ms-auto">
          <i class="la la-save"></i> Order Kurir
        </button>
      </div> -->

        <!-- Biaya Otomatis (Tampilkan hanya jika layanan dipilih) -->
        <div class="col-md-4 mb-3" v-if="services.length > 0">
          <label class="form-label fw-bold">Biaya (Rp)</label>
          <input type="text" name="biaya" class="form-control mb-2" :value="biaya ? biaya.toLocaleString('id-ID') : '-'"
            readonly />

          <!-- Tombol Bayar Ongkir -->
          <!-- <button type="button" class="btn btn-success w-100" @click="bayarOngkir"
            :disabled="sudahBayar || metodePembayaran === 'cod'">
            <i class="la la-money-bill"></i> Bayar Ongkir
          </button> -->
        </div>
      </div>
      <ErrorMessage name="nama_barang" class="text-danger" />
      <ErrorMessage name="penerima" class="text-danger" />
      <ErrorMessage name="alamat_tujuan" class="text-danger" />
      <ErrorMessage name="no_hp_penerima" class="text-danger" />
      <ErrorMessage name="no_hp_pengirim" class="text-danger" />
      <ErrorMessage name="provinceOrigin" class="text-danger" />
      <ErrorMessage name="citiesOrigin" class="text-danger" />
      <ErrorMessage name="provinceDestination" class="text-danger" />
      <ErrorMessage name="citiesDestination" class="text-danger" />
      <ErrorMessage name="kurir" class="text-danger" />
      <ErrorMessage name="layanan" class="text-danger" />
      <ErrorMessage name="berat_barang" class="text-danger" />


      <!-- Footer -->
      <div class="card-footer d-flex">
        <button type="submit" class="btn btn-primary ms-auto" @click="bayarOngkir">
          <i class="la la-save"></i> Order Kurir
        </button>
      </div>


    </div>
  </VForm>
</template>

<style>
.text-purple {
  color: #6f42c1 !important;
}
</style>