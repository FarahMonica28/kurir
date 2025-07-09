<template>
  <form @submit.prevent="submitForm">
    <div>
      <label>Asal Kota:</label>
      <select v-model="form.origin">
        <option v-for="kota in kotaList" :value="kota.id">{{ kota.name }}</option>
      </select>
    </div>

    <div>
      <label>Tujuan Kota:</label>
      <select v-model="form.destination">
        <option v-for="kota in kotaList" :value="kota.id">{{ kota.name }}</option>
      </select>
    </div>

    <div>
      <label>Kurir:</label>
      <select v-model="form.courier">
        <option value="jne">JNE</option>
        <option value="jnt">J&T</option>
        <option value="sicepat">SiCepat</option>
      </select>
    </div>

    <div>
      <label>Berat (gram):</label>
      <input type="number" v-model="form.weight" />
    </div>

    <button type="button" @click="cekOngkir">Cek Ongkir</button>

    <div v-if="ongkir">
      <p>Biaya: Rp {{ ongkir }}</p>
    </div>

    <button type="submit">Submit</button>
  </form>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const form = ref({
  origin: '',
  destination: '',
  courier: 'jne',
  weight: 1000,
})

const ongkir = ref<number | null>(null)
const kotaList = ref<{ id: string, name: string }[]>([])

// Fungsi ambil data kota (dummy atau dari API sendiri)
const fetchKota = async () => {
  // Bisa pakai dari database sendiri atau langsung dari Binderbyte
  kotaList.value = [
    { id: '501', name: 'Surabaya' },
    { id: '114', name: 'Jakarta' },
    // tambahkan sesuai kebutuhan
  ]
}

onMounted(() => {
  fetchKota()
})

// Fungsi hitung ongkir
const cekOngkir = async () => {
  try {
    const res = await axios.post('/ongkir', form.value)
    const data = res.data

    if (data.status === 200 && data.results.length) {
      // Ambil harga ongkir termurah
      ongkir.value = data.results[0].cost[0].value
    } else {
      alert('Ongkir tidak ditemukan')
    }
  } catch (err) {
    console.error(err)
    alert('Gagal cek ongkir')
  }
}

const submitForm = () => {
  // kirim form + ongkir ke backend
  console.log(form.value, 'Ongkir:', ongkir.value)
}
</script>
