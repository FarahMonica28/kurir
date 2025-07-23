<!-- src/views/OtpPage.vue -->
<template>
  <div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4">Verifikasi OTP</h2>
    <p class="text-sm text-gray-600 mb-4">
      Kode OTP telah dikirim ke: <strong>{{ email }}</strong>
    </p>
    <input
      v-model="otp"
      placeholder="Masukkan Kode OTP"
      class="w-full border rounded px-4 py-2 mb-4"
    />
    <button
      @click="verifyOtp"
      class="bg-blue-600 text-white w-full py-2 rounded mb-2"
    >
      Verifikasi
    </button>

    <div v-if="timer > 0" class="text-sm text-gray-500 text-center">
      Kirim ulang dalam {{ timer }} detik
    </div>
    <button
      v-else
      @click="resendOtp"
      class="text-blue-600 text-sm mt-2 w-full"
    >
      Kirim Ulang OTP
    </button>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import { toast } from "vue3-toastify";

const route = useRoute();
const router = useRouter();

const email = route.query.email as string;
const key = ref(route.query.key as string);
const otp = ref("");
const timer = ref(60);
let interval: any = null;

const startTimer = () => {
  interval = setInterval(() => {
    if (timer.value > 0) timer.value--;
    else clearInterval(interval);
  }, 1000);
};

const verifyOtp = async () => {
  try {
    await axios.post("/master/users/verify-otp", { key: key.value, otp: otp.value });
    toast.success("OTP berhasil diverifikasi");

    // Lanjut ke halaman sukses/redirect
    router.push("/login"); // atau halaman lain sesuai kebutuhan
  } catch (err: any) {
    toast.error(err.response?.data?.message || "OTP salah atau expired");
  }
};

const resendOtp = async () => {
  try {
    const res = await axios.post("/master/users/request-otp", { email });
    key.value = res.data.key;
    toast.success("OTP baru telah dikirim");
    timer.value = 60;
    startTimer();
  } catch (err: any) {
    toast.error("Gagal kirim ulang OTP");
  }
};

onMounted(() => {
  if (!email || !key.value) router.push("/register"); // fallback kalau query tidak valid
  startTimer();
});
</script>
