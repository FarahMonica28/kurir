<template>
  <div class="container py-5">
    <h1 class="text-center mb-4">Kalkulator Ongkos Kirim (V2)</h1>

    <form @submit.prevent="checkShippingCost">
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">Provinsi Tujuan</label>
          <select v-model="form.province_id" class="form-select" @change="getCities">
            <option value="">-- Pilih Provinsi --</option>
            <option v-for="province in provinces" :key="province.id" :value="province.id">
              {{ province.name }}
            </option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Kota / Kabupaten Tujuan</label>
          <select v-model="form.city_id" class="form-select" @change="getDistricts">
            <option value="">-- Pilih Kota / Kabupaten --</option>
            <option v-for="city in cities" :key="city.id" :value="city.id">
              {{ city.name }}
            </option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Kecamatan Tujuan</label>
          <select v-model="form.district_id" class="form-select">
            <option value="">-- Pilih Kecamatan --</option>
            <option v-for="district in districts" :key="district.id" :value="district.id">
              {{ district.name }}
            </option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Berat Barang (gram)</label>
          <input v-model="form.weight" type="number" class="form-control" min="1000" placeholder="Masukkan berat barang dalam gram" />
        </div>

        <div class="col-md-6">
          <label class="form-label">Kurir</label>
          <div class="d-flex flex-wrap gap-2">
            <div v-for="(kurir, index) in couriers" :key="index" class="form-check">
              <input
                class="form-check-input"
                type="radio"
                :id="kurir"
                :value="kurir"
                v-model="form.courier"
              />
              <label class="form-check-label" :for="kurir">
                {{ kurir.toUpperCase() }}
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="text-center mb-4">
        <button :disabled="loading" class="btn btn-primary">
          <span v-if="loading">Memproses...</span>
          <span v-else>Hitung Ongkos Kirim</span>
        </button>
      </div>
    </form>

    <div v-if="results.length" class="alert alert-info">
      <h5 class="text-center mb-3">Hasil Perhitungan Ongkos Kirim</h5>
      <div v-for="(res, index) in results" :key="index" class="border rounded p-3 mb-2 d-flex justify-content-between">
        <span>{{ res.service }} - {{ res.description }} - ({{ res.etd }})</span>
        <strong>{{ formatCurrency(res.cost) }}</strong>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      provinces: [],
      cities: [],
      districts: [],
      couriers: ['sicepat', 'jnt', 'ninja', 'jne', 'anteraja', 'pos', 'tiki', 'wahana', 'lion'],
      form: {
        province_id: '',
        city_id: '',
        district_id: '',
        weight: '',
        courier: '',
      },
      results: [],
      loading: false,
    };
  },
  mounted() {
    this.getProvinces();
  },
  methods: {
    async getProvinces() {
      const res = await fetch('/provinces');
      this.provinces = await res.json();
    },
    async getCities() {
      this.form.city_id = '';
      this.form.district_id = '';
      this.districts = [];
      const res = await fetch(`/cities/${this.form.province_id}`);
      this.cities = await res.json();
    },
    async getDistricts() {
      this.form.district_id = '';
      const res = await fetch(`/districts/${this.form.city_id}`);
      this.districts = await res.json();
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
      }).format(amount);
    },
    async checkShippingCost() {
      if (!this.form.district_id || !this.form.courier || !this.form.weight) {
        alert('Harap lengkapi semua data terlebih dahulu!');
        return;
      }

      this.loading = true;
      this.results = [];
      try {
        const res = await fetch('/check-ongkir', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(this.form)
        });
        this.results = await res.json();
      } catch (error) {
        alert('Terjadi kesalahan saat menghitung ongkir.');
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.container {
  max-width: 900px;
}
</style>
