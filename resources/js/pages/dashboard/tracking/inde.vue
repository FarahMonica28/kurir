<!-- <script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const search = ref(route.query.resi || '');
const trackingData = ref(null);
const searched = ref(false);

const getTrackingData = async () => {
  axios.get(`/tracking?no_resi=${no_resi}`)
  if (!search.value) return;
  searched.value = true;

  try {
    const res = await fetch(`/tracking/${search.value}`);
    if (!res.ok) throw new Error();
    const data = await res.json();
    trackingData.value = data;
  } catch (error) {
    trackingData.value = null;
  }
};

onMounted(() => {
  if (search.value) getTrackingData();
});
</script> -->
<script setup>
import { ref } from 'vue'
import axios from 'axios'

const resi = ref('')
const trackingData = ref(null)
const notFound = ref(false)

const getTrackingData = async () => {
  try {
    const response = await axios.get(`/tracking/${resi.value}`)
    trackingData.value = response.data
    notFound.value = false
  } catch (error) {
    trackingData.value = null
    notFound.value = true
    console.error(error)
  }
}
</script>


<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-4">Tracking Pengiriman</h2>

    <!-- Search Resi -->
    <div class="mb-4 flex items-center gap-2">
      <input v-model="resi" type="text" placeholder="Masukkan Nomor Resi" />
      <button @click="getTrackingData"><i class="bi bi-search"></i></button>
      <p v-if="trackingData">{{ trackingData.paket }}</p>
      <p v-else-if="notFound">Data tidak ditemukan</p>
    </div>

    <!-- Tracking Detail -->
    <div v-if="trackingData" class="bg-white p-4 rounded shadow">
      <h3 class="font-semibold mb-2">Status Pengiriman</h3>
      <ul class="space-y-2">
        <li v-for="(step, index) in trackingData.status" :key="index">
          <div class="flex items-start gap-2">
            <div class="h-4 w-4 rounded-full" :class="step.completed ? 'bg-green-500' : 'bg-gray-300'"></div>
            <div>
              <p class="font-medium">{{ step.label }}</p>
              <p class="text-sm text-gray-500">{{ step.timestamp }}</p>
            </div>
          </div>
        </li>
      </ul>

      <div class="mt-4">
        <p><strong>Nama Penerima:</strong> {{ trackingData.penerima }}</p>
        <p><strong>Alamat:</strong> {{ trackingData.alamat }}</p>
        <p><strong>Kurir:</strong> {{ trackingData.kurir }}</p>
      </div>
    </div>


    <div v-else-if="searched">
      <p class="text-gray-500">Data tidak ditemukan atau resi salah.</p>
    </div>
  </div>
</template>

<style scoped>
/* optional styling */
</style>
