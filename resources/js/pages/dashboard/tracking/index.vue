<template>
  <div class="container">
    <h2 class="text-xl font-semibold mb-4 mt-" id="la">Lacak Pengiriman</h2>
    <div class="mb-4" id="no">
      <h3>
        <label for="noResi" class="block mb-1 mt-6" id="">Nomor Resi : </label>
        <input v-model="noResi" id="noResi" type="text" class="form-input border rounded"
          placeholder="Contoh: ABC-123456" />
      </h3>
    </div>
    <div class="but">
      <button class="px-4 py-2 mt-5 rounded" id="lacak" @click="trackResi" :disabled="loading">
        {{ loading ? 'Memuat...' : 'Lacak' }}
      </button>
    </div>

    <div v-if="error" class="text-red-600 mt-8">{{ error }}</div>

    <div v-if="data" class="mt-6 border rounded p-4 bg-gray-50">
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
        <!-- Paket dibuat -->
        <div class="tracking-header">
          <p class="tracking-date">{{ formatDate(data.waktu) }}</p>
          <div class="tracking-timeline mt-6">
            <div class="tracking-item">
              <div class="dot"></div>
              <div class="content">
                <div class="time">{{ data.waktu.slice(11, 16) }}</div>
                <!-- <div class="desc">Paket dibuat oleh {{ data.pengguna?.name || 'pengirim' }}</div> -->
                <div class="desc">Paket dibuat oleh {{ namaPengguna }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Kurir menuju ke rumah -->
        <div v-if="data.waktu_diambil" class="tracking-header">
          <p class="tracking-date">{{ formatDate(data.waktu_diambil) }}</p>
          <div class="tracking-timeline mt-6">
            <div class="tracking-item">
              <div class="dot"></div>
              <div class="content">
                <div class="time">{{ data.waktu_diambil.slice(11, 16) }}</div>
                <div class="desc">
                  Kurir <strong>{{ data?.ambil?.name || 'Kurir' }}</strong> sedang menuju ke rumahmu {{
                    data.alamat_asal }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Diambil -->
        <div v-if="data.waktu_dikurir" class="tracking-header">
          <p class="tracking-date">{{ formatDate(data.waktu_dikurir) }}</p>
          <div class="tracking-timeline mt-6">
            <div class="tracking-item">
              <div class="dot"></div>
              <div class="content">
                <div class="time">{{ data.waktu_dikurir.slice(11, 16) }}</div>
                <div class="desc">Kurir <strong>{{ data?.ambil?.name || 'Kurir' }}</strong> menuju gudang penempatan
                  paket
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Di Gudang -->
        <div v-if="data.waktu_digudang" class="tracking-header">
          <p class="tracking-date">{{ formatDate(data.waktu_digudang) }}</p>
          <div class="tracking-timeline mt-6">
            <div class="tracking-item">
              <div class="dot"></div>
              <div class="content">
                <div class="time">{{ data.waktu_digudang.slice(11, 16) }}</div>
                <div class="desc">Paket telah sampai di gudang</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Proses -->
        <div v-if="data.waktu_proses" class="tracking-header">
          <p class="tracking-date">{{ formatDate(data.waktu_proses) }}</p>
          <div class="tracking-timeline mt-6">
            <div class="tracking-item">
              <div class="dot"></div>
              <div class="content">
                <div class="time">{{ data.waktu_proses.slice(11, 16) }}</div>
                <div class="desc">
                  Paket akan dikirim ke provinsi {{ data.tujuan_provinsi.name }} dan ke kota
                  {{ data.asal_kota.name }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- tiba digudang -->
        <div v-if="data.waktu_tiba" class="tracking-header">
          <p class="tracking-date">{{ formatDate(data.waktu_tiba) }}</p>
          <div class="tracking-timeline mt-6">
            <div class="tracking-item">
              <div class="dot"></div>
              <div class="content">
                <div class="time">{{ data.waktu_tiba.slice(11, 16) }}</div>
                <div class="desc">Paket telah tiba digudang kota {{ data.asal_kota.name }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dikirim -->
        <div v-if="data.waktu_kirim" class="tracking-header">
          <p class="tracking-date">{{ formatDate(data.waktu_kirim) }}</p>
          <div class="tracking-timeline mt-6">
            <div class="tracking-item">
              <div class="dot"></div>
              <div class="content">
                <div class="time">{{ data.waktu_kirim.slice(11, 16) }}</div>
                <div class="desc">Paket menuju ke alamat tujuan {{ data.alamat_tujuan }}</div>
                <!-- <div class="desc">Kurir <strong>{{ data?.antar?.name || 'Kurir' }}</strong> menuju ke alamat tujuan {{ data.alamat_tujuan }}</div> -->
              </div>
            </div>
          </div>
        </div>

        <!-- Selesai -->
        <div v-if="data.status.toLowerCase() === 'selesai'" class="tracking-header">
          <p class="tracking-date">{{ formatDate(data.waktu_selesai) }}</p>
          <div class="tracking-timeline mt-6">
            <div class="tracking-item">
              <div class="dot"></div>
              <div class="content">
                <div class="time">{{ data.waktu_selesai.slice(11, 16) }}</div>
                <div class="desc">Paket telah sampai ke tujuan</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth' // Import store autentikasi (misal pakai Pinia)

// ===============================
// State & Reference
// ===============================

// Input no resi dari pengguna
const noResi = ref('')

// Data hasil tracking
const data = ref<any>(null)

// Error dan loading state
const error = ref('')
const loading = ref(false)

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

// ===============================
// Fungsi utama untuk tracking no resi
// ===============================
const trackResi = async () => {
  // Reset data dan error
  error.value = ''
  data.value = null
  loading.value = true

  try {
    // Ambil data transaksi berdasarkan no resi
    const response = await axios.get(`/tracking/${noResi.value}`)
    data.value = response.data.data
    console.log("Sudah dapat Data :", data.value)

    // Jika ada kurir_id, ambil data kurir-nya juga
    if (data.value.kurir_id) {
      try {
        const resKurir = await axios.get(`/kurir/${data.value.kurir_id}`)
        data.value.kurir = resKurir.data
      } catch (e) {
        console.warn('Gagal memuat data kurir', e)
      }
    }
  } catch (err: any) {
    // Tampilkan error dari server jika ada
    error.value = err.response?.data?.message || 'Terjadi kesalahan saat melacak resi.'
  } finally {
    loading.value = false
  }
}

// ===============================
// Kurir yang mengambil barang (status: "ambil")
// ===============================
const kurirAmbil = computed(() =>
  data.value?.pengiriman?.find(p => p.status?.toLowerCase() === 'ambil')?.kurir
)

// ===============================
// Kurir yang mengantar barang (status: "antar")
// ===============================
const kurirKirim = computed(() =>
  data.value?.pengiriman?.find(p => p.status?.toLowerCase() === 'antar')?.kurir
)

// ===============================
// Data pengguna login (dari store)
// ===============================
const auth = useAuthStore()
const user = computed(() => auth.user)

// ===============================
// Cek apakah user login adalah pengirim
// ===============================
const isPenggunaLogin = computed(() => {
  return user.value?.id === data.value?.pengirim?.id
})

// ===============================
// Nama pengguna (fallback ke data pengirim jika belum login)
// ===============================
const namaPengguna = computed(() => {
  return user.value?.name || data.value?.pengirim?.name || 'pengirim'
})
</script>



<style scoped>
input {
  border-radius: 5%;
}

/* .container {
  /* text-align: center; */
/* border: 1px solid; *
  width: 60%;
}

#noResi {
  width: 50%;
  margin-left: 1%;
  border-radius: 1px solid;

} */
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
