<template>
  <div class="container mx-auto px-4 py-10 bg-">
    <h1 class="text-xl font-semibold mb-4 mt-" id="la">📦 Lacak Pengiriman</h1>
    <div class="mb-4" id="no">
      <h3>
        <label for="noResi" class="block mb-1 mt-6" id="">Nomor Resi : </label>
        <input v-model="noResi" id="noResi" type="text" class="form-input border rounded"
          placeholder="Contoh: ABC-123456" />
      </h3>
    </div>
    <div class="button d-flex justify-content-center" id="but">
      <button class="px-4 py-2 mt-5 rounded d-flex " id="lacak" @click="askLast4" :disabled="loading">
        {{ loading ? 'Memuat...' : 'Lacak' }}
      </button>
    </div>

    <!-- <div v-if="!data" class="img text-center">
      <img src="/storage/photo/trackingg.png" alt="Foto contoh" class="w-40 h-40 object-cover rounded inline-block" />
    </div>

    <!-- Error --
    <div v-if="error" class="text-red-500 text-center mb-6">
      {{ error }}
    </div>

    <!-- Loading --
    <div v-if="loading" class="flex justify-center">
      <img src="/img/loading.gif" alt="Loading..." class="w-12 h-12 animate-spin" />
    </div> -->
    <!-- Error -->
    <div v-if="error" class="text-red-500 text-center mb-6">
      {{ error }}
    </div>

    <!-- Loading -->
    <div v-else-if="loading" class="fixed inset-0 d-flex align-items-center justify-content-center ">
      <img src="/storage/photo/loo.gif" alt="Loading..." class="w-6 h-6 animate-spin items-center " />
    </div>


    <!-- Belum ada data -->
    <div v-else-if="!data" class="img text-center mb-6">
      <img src="/storage/photo/trackingg.png" alt="Tracking" class="w-40 h-40 object-cover rounded inline-block" />
    </div>


    <div v-if="error" class="text-red-600 mt-8">{{ error }}</div>

    <div v-if="data">
      <div class="mt-">
        <div class="tracking-container">
          <template v-for="(step, index) in steps" :key="index">
            <div class="tracking-step">
              <div class="circle" :class="{
                'active': index <= currentStep,
                'inactive': index > currentStep
              }">
                <img :src="step.icon" :alt="step.label" class="w-full max-w-xs h-auto object-contain mx-auto" />
              </div>
              <br>
              <p>{{ step.label }}</p> 
            </div>

            <!-- garis -->
            <div v-if="index < steps.length - 1" class="line" :class="{ active: index < currentStep }"></div>
          </template>
        </div>
      </div>


      <div class="mt-6 border rounded p-4 bg-gray-50">

        <h1 class="mt-5">Detail</h1>
        <div class="des">
          <p class="mt-5"><strong>Status : </strong> {{ data.status }}</p>
          <p class="mt-5">
            <strong>Ekspedisi : </strong> <span class="text-info">{{ data.ekspedisi }}</span>
          </p>
          <p class="mt-5">
            <strong>Status Pembayaran : </strong> <span class="text-primary">{{ data.status_pembayaran }}</span>
          </p>
        </div>

        <div class="box">
          <!-- Paket dibuat (packing) -->
          <div class="tracking-header">
            <div class="datetime">
              <p class="tracking-date">{{ formatDate(data.waktu) }}</p>
              <div class="time">{{ data.waktu.slice(11, 16) }}</div>
            </div>
            <div class="tracking-timeline mt-6">
              <div class="tracking-item">
                <div class="dot"></div>
                <div class="content">
                  <!-- <div class="desc">Paket dibuat oleh {{ data.pengguna?.name || 'pengirim' }}</div> -->
                  <div class="judul">Packing</div>
                  <div class="desc">Paket dibuat oleh {{ namaPengguna }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Kurir menuju rumah (pickup) -->
          <div v-if="data.waktu_diambil" class="tracking-header">
            <div class="datetime">
              <p class="tracking-date">{{ formatDate(data.waktu_diambil) }}</p>
              <div class="time">{{ data.waktu_diambil.slice(11, 16) }}</div>
            </div>
            <div class="tracking-timeline mt-6">
              <div class="tracking-item">
                <div class="dot"></div>
                <!-- <img class="dot-img" src="/public/storage/photo/gudang.jpgyyyy" alt="Kurir Ambil" /> -->
                <div class="content">
                  <div class="judul">Pickup</div>
                  <div class="desc">
                    Kurir sedang menuju ke rumahmu {{ data.alamat_asal }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Diambil -->
          <div v-if="data.waktu_dikurir" class="tracking-header">
            <div class="datetime">
              <p class="tracking-date">{{ formatDate(data.waktu_dikurir) }}</p>
              <div class="time">{{ data.waktu_dikurir.slice(11, 16) }}</div>
            </div>
            <div class="tracking-timeline mt-6">
              <div class="tracking-item">
                <div class="dot"></div>
                <div class="content">
                  <div class="judul">Pickup</div>
                  <div class="desc">Kurir <strong>{{ data?.ambil?.name || 'Kurir' }}</strong> menuju gudang penempatan
                    paket
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Di Gudang (transit) -->
          <div v-if="data.waktu_digudang" class="tracking-header">
            <div class="datetime">
              <p class="tracking-date">{{ formatDate(data.waktu_digudang) }}</p>
              <div class="time">{{ data.waktu_digudang.slice(11, 16) }}</div>
            </div>
            <div class="tracking-timeline mt-6">
              <div class="tracking-item">
                <div class="dot"></div>
                <div class="content">
                  <div class="judul">On Transit</div>

                  <div class="desc">Paket telah sampai di gudang</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Proses -->
          <div v-if="data.waktu_proses" class="tracking-header">
            <div class="datetime">
              <p class="tracking-date">{{ formatDate(data.waktu_proses) }}</p>
              <div class="time">{{ data.waktu_proses.slice(11, 16) }}</div>
            </div>
            <div class="tracking-timeline mt-6">
              <div class="tracking-item">
                <div class="dot"></div>
                <div class="content">
                  <div class="judul">On Transit</div>

                  <div class="desc">
                    Paket akan dikirim ke provinsi {{ data.tujuan_provinsi_name }} dan ke kota
                    {{ data.tujuan_kota_name }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- tiba digudang -->
          <div v-if="data.waktu_tiba" class="tracking-header">
            <div class="datetime">
              <p class="tracking-date">{{ formatDate(data.waktu_tiba) }}</p>
              <div class="time">{{ data.waktu_tiba.slice(11, 16) }}</div>
            </div>
            <div class="tracking-timeline mt-6">
              <div class="tracking-item">
                <div class="dot"></div>
                <!-- <img class="dot-img" :src="data?.ambil?.photo || '/img/default-user.png'" alt="Kurir" /> -->
                <div class="content">
                  <div class="judul">On Transit</div>
                  <div class="desc">Paket telah tiba digudang kota {{ data.asal_kota_name }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Dikirim (delivery)-->
          <div v-if="data.waktu_kirim" class="tracking-header">
            <div class="datetime">
              <p class="tracking-date">{{ formatDate(data.waktu_kirim) }}</p>
              <div class="time">{{ data.waktu_kirim.slice(11, 16) }}</div>
            </div>
            <div class="tracking-timeline mt-6">
              <div class="tracking-item">
                <div class="dot"></div>
                <div class="content">
                  <div class="judul">On Delivery</div>

                  <div class="desc">Paket menuju ke alamat tujuan {{ data.alamat_tujuan }}</div>
                  <!-- <div class="desc">Kurir <strong>{{ data?.antar?.name || 'Kurir' }}</strong> menuju ke alamat tujuan {{ data.alamat_tujuan }}</div> -->
                </div>
              </div>
            </div>
          </div>

          <!-- Selesai delivered -->
          <div v-if="data.status.toLowerCase() === 'selesai'" class="tracking-header">
            <div class="datetime">
              <p class="tracking-date">{{ formatDate(data.waktu_selesai) }}</p>
              <div class="time">{{ data.waktu_selesai.slice(11, 16) }}</div>
            </div>
            <div class="tracking-timeline mt-6">
              <div class="tracking-item">
                <div class="dot"></div>
                <div class="content">
                  <div class="judul">Delivered</div>
                  <div class="desc">Paket telah sampai ke tujuan</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

const noResi = ref("");
const last4 = ref("");
const loading = ref(false);
const error = ref("");
const data = ref<any>(null);

watch(loading, (newVal) => {
  if (newVal) {
    console.log("Loading data : ", newVal);
  }
});

// ===============================
// Fungsi utilitas untuk format tanggal
// ===============================
function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}
// 🔹 Step 1: Popup minta last4
const askLast4 = async () => {
  if (!noResi.value) {
    Swal.fire('Oops!', 'Nomor resi harus diisi terlebih dahulu.', 'warning')
    return
  }

  const { value: last4Digits } = await Swal.fire({
    title: 'harap masukkan empat digit terakhir nomor telepon',
    html: `
      <div style="display: flex; justify-content: center; gap: 8px;">
        <input id="digit1" type="text" maxlength="1" pattern="[0-9]*" style="width: 40px; text-align: center; font-size: 20px;">
        <input id="digit2" type="text" maxlength="1" pattern="[0-9]*" style="width: 40px; text-align: center; font-size: 20px;">
        <input id="digit3" type="text" maxlength="1" pattern="[0-9]*" style="width: 40px; text-align: center; font-size: 20px;">
        <input id="digit4" type="text" maxlength="1" pattern="[0-9]*" style="width: 40px; text-align: center; font-size: 20px;">
      </div>
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Lanjutkan',
    cancelButtonText: 'Batal',
    preConfirm: () => {
      const d1 = (document.getElementById('digit1') as HTMLInputElement)?.value || ''
      const d2 = (document.getElementById('digit2') as HTMLInputElement)?.value || ''
      const d3 = (document.getElementById('digit3') as HTMLInputElement)?.value || ''
      const d4 = (document.getElementById('digit4') as HTMLInputElement)?.value || ''
      const full = d1 + d2 + d3 + d4

      if (!/^\d{4}$/.test(full)) {
        Swal.showValidationMessage('Masukkan tepat 4 digit angka')
        return false
      }
      return full
    },
    didOpen: () => {
      const inputs = document.querySelectorAll<HTMLInputElement>(
        '#digit1, #digit2, #digit3, #digit4'
      )

      inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
          if (input.value.length === 1 && index < inputs.length - 1) {
            (inputs[index + 1] as HTMLInputElement).focus()
          }
        })

        input.addEventListener('keydown', (e) => {
          if (e.key === 'Backspace' && index > 0 && !input.value) {
            (inputs[index - 1] as HTMLInputElement).focus()
          }
        })
      })

        // Fokus ke input pertama
        (document.getElementById('digit1') as HTMLInputElement)?.focus()
    },
  })

  if (last4Digits) {
    await trackResi(last4Digits)
  }
};
const trackResi = async (last4: string) => {

  try {
    loading.value = true
    error.value = ''
    data.value = null
    // loading.value = false
    const response = await axios.get(`/tracking/${noResi.value}`, {
      params: { last4 },
    })
    data.value = response.data.data
    console.log("Data tracking:", data.value)
    loading.value = false
    console.log("Loading selesai:", loading.value)
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Terjadi kesalahan.'
    error.value = msg
    Swal.fire('Gagal!', msg, 'error')
  } finally {
    loading.value = false
  }
}

const steps = [
  { label: "Packing", icon: "/storage/photo/pakett.png" },
  { label: "Pick Up", icon: "/storage/photo/pickup.png" },
  { label: "On Transit", icon: "/storage/photo/transit.png" },
  { label: "On Delivery", icon: "/storage/photo/ondelivery.png" },
  { label: "Delivered", icon: "/storage/photo/delivered.png" },
];

const currentStep = computed(() => {
  if (!data.value) return -1;
  switch (data.value.status.toLowerCase()) {
    case "menunggu": return 0;
    case "packing": return 0;
    case "pickup": return 1;
    case "transit": return 2;
    case "digudang": return 2;
    case "on delivery": return 3;
    case "dikirim": return 3;
    case "selesai": return 4;
    case "delivered": return 4;
    default: return -1;
  }
});

const namaPengguna = computed(() => {
  return data.value?.pengguna?.name || 'Pengirim';
});



</script>

<style>
h1 {
  text-align: center;
  margin-top: 20px;
  font-size: 24px;
  /* color: #4a5568;  */
}

.fade-enter-active,
.fade-leave-active {
  transition: all 0.5s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateX(-20px);
}

.fade-enter-to {
  opacity: 1;
  transform: translateX(0);
}

.tracking-container {
  display: flex;
  justify-content: space-between;
  /* otomatis bagi rata */
  align-items: center;
  margin: 40px 0;
  width: 100%;
}

.tracking-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  /* flex: 1.5; biar tiap step punya ruang sama */
}

.circle {
  width: 70px;
  height: 70px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 50%;
  position: relative;
}

.circle.active {
  background-color: rgb(233, 154, 233);
  /* background-color: #f3f4f6; */
  /* background-color: #ffe5e5; */
}

.circle.active::after {
  content: "";
  position: absolute;
  width: 90px;
  height: 90px;
  border-radius: 50%;
  border: 2px dashed #bd10d1;
  animation: spinStep 2s linear infinite;
}

/* .circle.inactive {
  background-color: #7c3aed;
  /* background-color: #ebebebe6; *
} */

.circle.inactive::after {
  content: "";
  position: absolute;
  width: 90px;
  height: 90px;
  border-radius: 50%;
  border: 2px dashed #a9b4a4;
  /* animation: spinStep 2s linear infinite; */
  /* border: 2px dashed #9a1414; */
}

.circle img {
  width: 50px;
  height: 50px;
  object-fit: contain;
}

.line {
  border-top: 2px dashed white;
  /* border-top: 2px dashed transparent; */
  margin: 0;
  /* buang margin default */
  height: 0;
  /* position: relative; */
  top: -10px;
  /* naikkan biar sejajar lingkaran luar */
  /* flex: 1; */
  align-self: center;
  /* pastikan selalu center di tengah circle luar */
}

.line.active {
  border-top-color: rgb(135, 23, 120);
}

@keyframes spinStep {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(60deg);
  }
}


.tracking-step p {
  margin-top: 5px;
  font-size: 14px;
  color: red;
  font-weight: 200;
}


.line {
  flex: 1;
  margin-top: -2.5%;
  margin-left: -1%;
  border-top: 2px dashed black;
  /* margin: 10px; */
}

input {
  border-radius: 5%;
}



.container {
  width: 60%;
  margin: 0 auto;
  padding: 1rem;
}

#la {
  text-align: center;
}

#no {
  margin-left: 20%;
}

#noResi {
  width: 50%;
  margin: 0;
  padding: 0.5rem;
  border: 1px solid #ccc;
  margin-left: 3%;
}

.but {
  text-align: center;
}

#lacak:hover {
  background-color: #a855f7;
  color: white;
}

.box,
.des {
  margin-left: 15%;
}

.tracking-header {
  display: flex;
  align-items: center;
  margin-left: 0.25rem;
  margin-bottom: 1rem;
}

.tracking-date {
  font-weight: 600;
  color: #6b7280;
  font-size: 0.9rem;
  margin-left: 0.25rem;
  white-space: nowrap;
}

.tracking-timeline {
  position: relative;
  margin-left: 6.5rem;
  /* agar sejajar dengan konten timeline */
  padding-left: 1.5rem;
}

.tracking-timeline::before {
  content: "";
  position: absolute;
  top: 0.75rem;
  left: -1.5rem;
  width: 3px;
  height: 100%;
  /* background-color: #f57ce2; */
  background-color: #a855f7;
}

.tracking-item {
  position: relative;
  display: flex;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.tracking-item .dot {
  width: 12px;
  height: 12px;
  /* background-color: #ff44dd; */
  background-color: #a855f7;
  border-radius: 50%;
  position: absolute;
  left: -3.31rem;
  top: 0.25rem;
  z-index: 1;
}

.tracking-item .content {
  margin-left: 0.5rem;
}

.tracking-item .time {
  font-weight: 600;
  color: #a855f7;
  font-size: 0.9rem;
}

.tracking-item .desc {
  margin-top: 0.25rem;
  color: #4b5563;
}

.datetime .time {
  color: purple;
  /* warna jam */
  display: block;
  /* supaya di bawah */
  /* margin-top: 1px; */
  /* jarak kecil */
  text-align: left;
}


@media (max-width: 768px) {
  .container {
    width: 100%;
    padding: 1rem;
  }

  #noResi {
    width: 100%;
    margin: 0;
  }

  .tracking-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .tracking-date {
    margin-bottom: 0.5rem;
    margin-left: 0;
  }

  .tracking-timeline {
    margin-left: 1.5rem;
    padding-left: 1rem;
  }

  .tracking-item .dot {
    left: -1.5rem;
  }

  #lacak {
    width: 100%;
    margin-top: 1rem;
  }
}
</style>
