<template>
    <div class="container mt-4">
      <h4>Rute Pengiriman</h4>
      <div ref="map" class="rounded" style="height: 400px;"></div>
      <div class="mt-3">
        <p><strong>Estimasi:</strong> {{ duration }} ({{ distance }})</p>
        <a
          :href="googleMapsLink"
          class="btn btn-primary"
          target="_blank"
        >Buka di Google Maps</a>
      </div>
    </div>
  </template>
  
  <script setup>
  import { onMounted, ref } from 'vue';
  import axios from 'axios';
  import 'leaflet/dist/leaflet.css';
    import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';

  
  const map = ref(null)
  const duration = ref('')
  const distance = ref('')
  const googleMapsLink = ref('#')
  
  // Ganti dengan data nyata dari posisi kurir dan alamat tujuan
  const kurirPosisi = { lat: -6.200000, lng: 106.816666 } // Jakarta
  const tujuan = { lat: -6.914744, lng: 107.609810 } // Bandung
  
  onMounted(() => {
    loadMap()
  })
  
  function loadMap() {
    const directionsService = new google.maps.DirectionsService()
    const directionsRenderer = new google.maps.DirectionsRenderer()
  
    const peta = new google.maps.Map(map.value, {
      zoom: 7,
      center: kurirPosisi,
    })
  
    directionsRenderer.setMap(peta)
  
    directionsService.route(
      {
        origin: kurirPosisi,
        destination: tujuan,
        travelMode: google.maps.TravelMode.DRIVING,
      },
      (result, status) => {
        if (status === 'OK') {
          directionsRenderer.setDirections(result)
          const leg = result.routes[0].legs[0]
          duration.value = leg.duration.text
          distance.value = leg.distance.text
  
          // Link ke Google Maps
          googleMapsLink.value = `https://www.google.com/maps/dir/?api=1&origin=${kurirPosisi.lat},${kurirPosisi.lng}&destination=${tujuan.lat},${tujuan.lng}`
        } else {
          alert('Gagal memuat rute: ' + status)
        }
      }
    )
  }
  </script>
  
  <!-- Tambahkan ini di public/index.html -->
  <!-- 
  <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY">
  </script> 
  -->
  