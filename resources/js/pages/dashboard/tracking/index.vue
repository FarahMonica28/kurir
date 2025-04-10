<template>
    <div class="p-4">
      <h2 class="text-xl font-bold mb-4">Tracking Pengiriman</h2>
  
      <!-- Search Resi -->
      <div class="mb-4 flex items-center gap-2">
        <input
          v-model="searchResi"
          type="text"
          placeholder="Masukkan Nomor Resi"
          class="border rounded px-4 py-2 w-1/2"
        />
        <button @click="getTrackingData" class="bg-blue-600 text-white px-4 py-2 rounded">
          Cari
        </button>
      </div>
  
      <!-- Tracking Detail -->
      <div v-if="trackingData" class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-2">Status Pengiriman</h3>
        <ul class="space-y-2">
          <li v-for="(step, index) in trackingData.statuses" :key="index">
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
          <p><strong>Nama Penerima:</strong> {{ trackingData.recipient }}</p>
          <p><strong>Alamat:</strong> {{ trackingData.address }}</p>
          <p><strong>Kurir:</strong> {{ trackingData.kurir }}</p>
        </div>
      </div>
  
      <div v-else-if="searched">
        <p class="text-gray-500">Data tidak ditemukan atau resi salah.</p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  
  const searchResi = ref('')
  const trackingData = ref(null)
  const searched = ref(false)
  
  const getTrackingData = async () => {
    searched.value = true
    try {
      const res = await fetch(`/api/tracking/${searchResi.value}`)
      if (!res.ok) throw new Error()
      const data = await res.json()
      trackingData.value = data
    } catch (error) {
      trackingData.value = null
    }
  }
  </script>
  
  <style scoped>
  /* optional styling */
  </style>
  