<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

const userName = ref<string | null>(null)

onMounted(async () => {
  try {
    const response = await axios.get('/login', {
      withCredentials: true // jika pakai sanctum/cookie auth
    })
    userName.value = response.data.name
  } catch (error) {
    console.error('Gagal mengambil data user:', error)
  }
})

// Setelah login berhasil
const res = await axios.get('/api/master/me')

</script>

<template>
  <main>
    <h1 v-if="userName">Selamat datang, {{ userName }}!</h1>
    <h1 v-else>Memuat data user...</h1>
  </main>
</template>
