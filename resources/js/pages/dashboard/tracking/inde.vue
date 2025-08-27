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


    <!-- Timeline -->
    <!-- <transition-group name="fade" tag="div" class="relative border-l-4 border-blue-500 pl-6 space-y-10">
      <div v-for="(item, index) in timeline" :key="index" class="relative flex items-start gap-4">
        <div
          class="absolute -left-9 flex items-center justify-center w-10 h-10 rounded-full bg-blue-500 text-white shadow-lg">
          <img :src="item.icon" alt="step" class="w-6 h-6" />
        </div>
        <div class="bg-white shadow-md rounded-xl p-4 w-full hover:shadow-lg transition transform hover:scale-[1.02]">
          <p class="text-sm text-gray-500">{{ item.date }}</p>
          <p class="font-semibold text-lg">{{ item.title }}</p>
          <p class="text-gray-600 mt-1">{{ item.desc }}</p>
        </div>
      </div>
    </transition-group> -->