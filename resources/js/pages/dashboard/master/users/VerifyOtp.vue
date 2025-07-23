<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'

const email = ref('')
const otp = ref('')
const message = ref('')
const loading = ref(false)

const verifyOtp = async () => {
  loading.value = true
  message.value = ''
  try {
    const response = await axios.post('/api/verify-email', {
      email: email.value,
      otp: otp.value
    })
    message.value = response.data.message
  } catch (error: any) {
    message.value = error.response?.data?.message || 'Gagal verifikasi OTP'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-md mx-auto p-4 bg-white shadow rounded">
    <h2 class="text-xl font-bold mb-4">Verifikasi OTP</h2>

    <div class="mb-3">
      <label>Email</label>
      <input v-model="email" type="email" class="form-input w-full" />
    </div>

    <div class="mb-3">
      <label>Kode OTP</label>
      <input v-model="otp" type="text" class="form-input w-full" maxlength="6" />
    </div>

    <button @click="verifyOtp" :disabled="loading" class="btn btn-primary w-full">
      {{ loading ? 'Memverifikasi...' : 'Verifikasi' }}
    </button>

    <p class="mt-3 text-center text-green-600" v-if="message">{{ message }}</p>
  </div>
</template>

<style scoped>
.form-input {
  border: 1px solid #ccc;
  border-radius: 6px;
  padding: 0.5rem;
}
.btn {
  background-color: #2563eb;
  color: white;
  padding: 0.5rem;
  border-radius: 6px;
}
</style>
