<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, computed, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import ApiService from "@/core/services/ApiService";
import { useAuthStore } from "@/stores/auth";
import { useForm } from "vee-validate";
import type { transaksii } from "@/types";

// Auth dan form reference
const authStore = useAuthStore();
const currentPengguna = computed(() => authStore.user);
const formRef = ref();

// State lokasi dan input form
const provinces = ref<Record<string, string>>({});
const citiesOrigin = ref<Record<string, string>>({});
const citiesDestination = ref<Record<string, string>>({});

const provinceOrigin = ref("0");
const cityOrigin = ref("");
const provinceDestination = ref("0");
const cityDestination = ref("");
const penerima = ref("");
const pengirim = ref("");
const alamat_tujuan = ref("");
const no_hp_penerima = ref("");
const nama_barang = ref("");

// Ekspedisi dan layanan
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

// Tambahan: status pembayaran dan metode
const sudahBayar = ref(false);
const metodePembayaran = ref(""); // 'cod' atau 'transfer'

// Props dan emit
const props = defineProps({ selected: { type: String, default: null } });
const emit = defineEmits(["close", "refresh"]);
const transaksi = ref<transaksii>({} as transaksii);

// Validasi form
const formSchema = Yup.object({
  nama_barang: Yup.string().required("Nama Barang harus diisi"),
  penerima: Yup.string().required("Nama Penerima harus diisi"),
  alamat_asal: Yup.string().required("Alamat Tujuan harus diisi"),
  alamat_tujuan: Yup.string().required("Alamat Tujuan harus diisi"),
  no_hp_penerima: Yup.string().required("No HP Penerima harus diisi"),
  pengirim: Yup.string().required("pengirim harus diisi"),
  provinceOrigin: Yup.string().required().notOneOf(["0"], "Provinsi asal harus dipilih"),
  cityOrigin: Yup.string().required("Kota asal harus dipilih"),
  provinceDestination: Yup.string().required().notOneOf(["0"], "Provinsi tujuan harus dipilih"),
  cityDestination: Yup.string().required("Kota tujuan harus dipilih"),
  kurir: Yup.string().required("Ekspedisi harus dipilih"),
  layanan: Yup.string().required("Layanan harus dipilih"),
  berat_barang: Yup.number().required("Berat barang harus diisi").min(0.1, "Berat minimal 0.1 kg"),
});

const { handleSubmit, errors, resetForm } = useForm({
  validationSchema: formSchema,
  initialValues: {
    nama_barang: "",
    penerima: "",
    alamat_tujuan: "",
    no_hp_penerima: "",
    provinceOrigin: "0",
    cityOrigin: "",
    provinceDestination: "0",
    cityDestination: "",
    kurir: "",
    layanan: "",
    berat_barang: 0,
    pengirim: "",
  },
});

// Fetch data dari API
const fetchProvinces = async () => {
  try {
    const res = await axios.get("/provinces");
    provinces.value = res.data;
  } catch {
    toast.error("Gagal mengambil data provinsi");
  }
};

const fetchCities = async (type: "origin" | "destination") => {
  const provId = type === "origin" ? provinceOrigin.value : provinceDestination.value;
  if (provId === "0") return;
  try {
    const res = await axios.get(`/cities/${provId}`);
    if (type === "origin") {
      citiesOrigin.value = res.data;
      cityOrigin.value = "";
    } else {
      citiesDestination.value = res.data;
      cityDestination.value = "";
    }
  } catch {
    toast.error("Gagal mengambil data kota");
  }
};

// Ongkir
const getSelectedCost = () => {
  const service = services.value.find(s => s.service === selectedService.value);
  const cost = service?.cost ?? 0;
  biaya.value = cost;
  return cost;
};

const fetchOngkir = async () => {
  if (
    provinceOrigin.value === "0" || !cityOrigin.value ||
    provinceDestination.value === "0" || !cityDestination.value ||
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
    toast.error("Gagal mengambil data ongkir");
    services.value = [];
    selectedService.value = "";
    biaya.value = 0;
  } finally {
    unblock(document.getElementById("form-transaksii"));
  }
};

watch([provinceOrigin, cityOrigin, provinceDestination, cityDestination, selectedCourier, berat_barang], fetchOngkir);
watch(selectedService, getSelectedCost);

// Pembayaran ongkir
// Invoice URL dari Xendit akan disimpan di sini
const invoiceUrl = ref("");

// ID transaksi yang akan dibayar (bisa juga dari props atau parameter dinamis)
const transaksiId = props.selected || 1; // fallback ke 1 jika props.selected kosong


const bayarOngkir = async () => {
  const draft = {
    penerima: penerima.value,
    no_hp_penerima: no_hp_penerima.value,
    provinceDestination: provinceDestination.value,
    cityDestination: cityDestination.value,
    alamat_tujuan: alamat_tujuan.value,

    pengirim: pengirim.value,
    nama_barang: nama_barang.value,
    provinceOrigin: provinceOrigin.value,
    cityOrigin: cityOrigin.value,
    alamat_asal: transaksi.value.alamat_asal || "",

    selectedCourier: selectedCourier.value,
    selectedService: selectedService.value,
    berat_barang: berat_barang.value,
    biaya: biaya.value,
  };

  // Simpan data transaksi ke sessionStorage untuk digunakan nanti
  sessionStorage.setItem("draftTransaksi", JSON.stringify(draft));

  try {
    const res = await axios.post("/payment", draft);

    const { redirect_url } = res.data;
    if (redirect_url) {
      window.location.href = redirect_url;
    } else {
      toast.error("URL pembayaran tidak ditemukan.");
    }
  } catch (err: any) {
    toast.error(err?.response?.data?.message || "Gagal membuat pembayaran.");
  }
};

// Submit transaksi
function onSubmit() {
  const formData = new FormData();

  formData.append("penerima", penerima.value);
  formData.append("tujuan_provinsi_id", provinceDestination.value);
  formData.append("tujuan_kota_id", cityDestination.value);
  formData.append("alamat_tujuan", alamat_tujuan.value);
  formData.append("no_hp_penerima", no_hp_penerima.value);

  formData.append("pengirim", pengirim.value);
  formData.append("nama_barang", nama_barang.value);
  formData.append("asal_provinsi_id", provinceOrigin.value);
  formData.append("asal_kota_id", cityOrigin.value);
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
    url: props.selected ? `/transaksii/${props.selected}` : "/transaksii/store",
    data: formData,
    headers: { "Content-Type": "multipart/form-data" },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      const message = err.response?.data?.message || "Terjadi kesalahan.";
      toast.error(message);
    })
    .finally(() => {
      unblock(document.getElementById("form-transaksii"));
    });
}

onMounted(fetchProvinces);
// PaymentSuccess.vue
onMounted(() => {
  const draft = JSON.parse(sessionStorage.getItem("draftTransaksi") || "{}");

  const formData = new FormData();
  for (const key in draft) {
    formData.append(key, draft[key]);
  }

  axios.post("/transaksii/store", formData, {
    headers: { "Content-Type": "multipart/form-data" },
  })
  .then(() => {
    toast.success("Order berhasil disimpan setelah pembayaran!");
    sessionStorage.removeItem("draftTransaksi");
  })
  .catch(() => {
    toast.error("Gagal menyimpan order setelah pembayaran.");
  });
});

// onMounted(() => {
//   const script = document.createElement('script');
//   script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
//   script.setAttribute('data-client-key', 'SB-Mid-client-xxxxxx');
//   script.async = true;
//   document.body.appendChild(script);
// });
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
            placeholder="Masukan no hp penerima" />
          <ErrorMessage name="no_hp_penerima" class="text-danger small" />
        </div>

        <!-- Provinsi Tujuan -->
        <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold">Provinsi Tujuan</label>
          <Field as="select" name="provinceDestination" v-model="provinceDestination" class="form-control"
            @change="fetchCities('destination')">
            <option value="0">-- Pilih Provinsi Tujuan --</option>
            <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
          </Field as="select">
          <ErrorMessage name="provinceDestination" class="text-danger small" />
          <!-- <div v-if="ErrorMessage" name="provinceDestination" class="text-danger">{{ errors.provinceDestination }}</div> -->
        </div>

        <!-- Kota Tujuan -->
        <div class="col-md-4 mb-7">
          <label class="form-label required fw-bold">Kota Tujuan</label>
          <Field as="select" name="cityDestination" v-model="cityDestination" class="form-control">
            <option value="">-- Pilih Kota Tujuan --</option>
            <option v-for="(name, id) in citiesDestination" :key="id" :value="id">{{ name }}</option>
          </Field as="select">
          <ErrorMessage name="cityDestination" class="text-danger small" />
          <!-- <div v-if="ErrorMessage" name="cityDestination" class="text-danger">{{ errors.cityDestination }}</div> -->
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
        <!-- <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold" for="pengguna">Nama Pengirim</label>
          <Field type="text" name="pengguna_id" class="form-control" :value="`${currentPengguna.name}`" readonly>
          </Field>
          <ErrorMessage name="pengguna_id" class="text-danger small" />
        </div> -->
        <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold" for="pengirim">Nama Pengirim</label>
          <Field type="text" name="pengirim" class="form-control" v-model="pengirim"
            placeholder="Masukan nama pengirim">
          </Field>
          <ErrorMessage name="pengirim" class="text-danger small" />
        </div>

        <!-- Nama Barang -->
        <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold">Nama Barang</label>
          <Field class="form-control" name="nama_barang" placeholder="Masukan nama barang" v-model="nama_barang" />
          <ErrorMessage name="nama_barang" class="text-danger small" />
        </div>

        <!-- Berat Barang -->
        <div class="col-md-4 mb-7 mt-4">
          <label class="form-label required fw-bold">Berat Barang (Kg)</label>
          <Field type="number" v-model="berat_barang" class="form-control" placeholder="Contoh: 0.5" min="0.1"
            step="0.1" name="berat_barang" />
          <ErrorMessage name="berat_barang" class="text-danger small" />
          <!-- <div v-if="errors.berat_barang" class="text-danger">{{ errors.berat_barang }}</div> -->
        </div>

        <!-- Provinsi Asal -->
        <div class="col-md-4 mb-7 ">
          <label class="form-label required fw-bold">Provinsi Asal</label>
          <Field as="select" name="provinceOrigin" v-model="provinceOrigin" class="form-control"
            @change="fetchCities('origin')">
            <option value="0">-- Pilih Provinsi Asal --</option>
            <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
          </Field as="select">
          <ErrorMessage name="provinceOrigin" class="text-danger small" />
        </div>

        <!-- Kota Asal -->
        <div class="col-md-4 mb-7">
          <label class="form-label required fw-bold">Kota Asal</label>
          <Field as="select" name="cityOrigin" v-model="cityOrigin" class="form-control">
            <option value="">-- Pilih Kota Asal --</option>
            <option v-for="(name, id) in citiesOrigin" :key="id" :value="id">{{ name }}</option>
          </Field as="select">
          <ErrorMessage name="cityOrigin" class="text-danger small" />
          <!-- <div v-if="ErrorMessage" name="cityOrigin" class="text-danger">{{ errors.cityOrigin }}</div> -->
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