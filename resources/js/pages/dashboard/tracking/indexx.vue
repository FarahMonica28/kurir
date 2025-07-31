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

    <div v-if="error" class="error-message">
      <i class="error-icon">⚠</i>
      {{ error }}
    </div>

    <div v-if="data" class="tracking-result">
      <div class="status-cards">
        <div class="status-card">
          <div class="status-label">Status</div>
          <div class="status-value" :class="getStatusClass(data.status)">{{ data.status }}</div>
        </div>
        <div class="status-card">
          <div class="status-label">Ekspedisi</div>
          <div class="status-value ekspedisi">{{ data.ekspedisi }}</div>
        </div>
        <div class="status-card">
          <div class="status-label">Status Pembayaran</div>
          <div class="status-value pembayaran">{{ data.status_pembayaran }}</div>
        </div>
      </div>

      <div class="timeline-container">
        <h3 class="timeline-title">Riwayat Pengiriman</h3>
        
        <!-- Paket dibuat -->
        <div class="tracking-step">
          <div class="step-date">{{ formatDate(data.waktu) }}</div>
          <div class="step-content">
            <div class="step-dot active"></div>
            <div class="step-info">
              <div class="step-time">{{ data.waktu.slice(11, 16) }}</div>
              <div class="step-desc">
                <strong>Paket dibuat</strong><br>
                oleh {{ namaPengguna }}
              </div>
            </div>
          </div>
        </div>

        <!-- Kurir menuju ke rumah -->
        <div v-if="data.waktu_diambil" class="tracking-step">
          <div class="step-date">{{ formatDate(data.waktu_diambil) }}</div>
          <div class="step-content">
            <div class="step-dot active"></div>
            <div class="step-info">
              <div class="step-time">{{ data.waktu_diambil.slice(11, 16) }}</div>
              <div class="step-desc">
                <strong>Kurir dalam perjalanan</strong><br>
                Kurir <span class="highlight">{{ data?.ambil?.name || 'Kurir' }}</span> sedang menuju ke {{ data.alamat_asal }}
              </div>
            </div>
          </div>
        </div>

        <!-- Diambil -->
        <div v-if="data.waktu_dikurir" class="tracking-step">
          <div class="step-date">{{ formatDate(data.waktu_dikurir) }}</div>
          <div class="step-content">
            <div class="step-dot active"></div>
            <div class="step-info">
              <div class="step-time">{{ data.waktu_dikurir.slice(11, 16) }}</div>
              <div class="step-desc">
                <strong>Paket diambil</strong><br>
                Kurir <span class="highlight">{{ data?.ambil?.name || 'Kurir' }}</span> menuju gudang penempatan paket
              </div>
            </div>
          </div>
        </div>

        <!-- Di Gudang -->
        <div v-if="data.waktu_digudang" class="tracking-step">
          <div class="step-date">{{ formatDate(data.waktu_digudang) }}</div>
          <div class="step-content">
            <div class="step-dot active"></div>
            <div class="step-info">
              <div class="step-time">{{ data.waktu_digudang.slice(11, 16) }}</div>
              <div class="step-desc">
                <strong>Tiba di gudang</strong><br>
                Paket telah sampai di gudang
              </div>
            </div>
          </div>
        </div>

        <!-- Proses -->
        <div v-if="data.waktu_proses" class="tracking-step">
          <div class="step-date">{{ formatDate(data.waktu_proses) }}</div>
          <div class="step-content">
            <div class="step-dot active"></div>
            <div class="step-info">
              <div class="step-time">{{ data.waktu_proses.slice(11, 16) }}</div>
              <div class="step-desc">
                <strong>Dalam proses pengiriman</strong><br>
                Paket akan dikirim ke {{ data.tujuan_provinsi.name }}, {{ data.asal_kota.name }}
              </div>
            </div>
          </div>
        </div>

        <!-- Tiba digudang -->
        <div v-if="data.waktu_tiba" class="tracking-step">
          <div class="step-date">{{ formatDate(data.waktu_tiba) }}</div>
          <div class="step-content">
            <div class="step-dot active"></div>
            <div class="step-info">
              <div class="step-time">{{ data.waktu_tiba.slice(11, 16) }}</div>
              <div class="step-desc">
                <strong>Tiba di kota tujuan</strong><br>
                Paket telah tiba di gudang {{ data.asal_kota.name }}
              </div>
            </div>
          </div>
        </div>

        <!-- Dikirim -->
        <div v-if="data.waktu_kirim" class="tracking-step">
          <div class="step-date">{{ formatDate(data.waktu_kirim) }}</div>
          <div class="step-content">
            <div class="step-dot active"></div>
            <div class="step-info">
              <div class="step-time">{{ data.waktu_kirim.slice(11, 16) }}</div>
              <div class="step-desc">
                <strong>Dalam pengiriman</strong><br>
                Paket menuju ke alamat tujuan {{ data.alamat_tujuan }}
              </div>
            </div>
          </div>
        </div>

        <!-- Selesai -->
        <div v-if="data.status.toLowerCase() === 'selesai'" class="tracking-step completed">
          <div class="step-date">{{ formatDate(data.waktu_selesai) }}</div>
          <div class="step-content">
            <div class="step-dot completed"></div>
            <div class="step-info">
              <div class="step-time">{{ data.waktu_selesai.slice(11, 16) }}</div>
              <div class="step-desc">
                <strong>Paket telah diterima</strong><br>
                Pengiriman berhasil diselesaikan
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
// Fungsi untuk mendapatkan class status
// ===============================
function getStatusClass(status: string): string {
  const statusLower = status.toLowerCase()
  if (statusLower === 'selesai') return 'status-completed'
  if (statusLower === 'dalam pengiriman' || statusLower === 'dikirim') return 'status-shipping'
  if (statusLower === 'diproses') return 'status-processing'
  return 'status-pending'
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
/* Container */
.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
  /* background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); */
  min-height: 100vh;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Header */
#la {
  text-align: center;
  color: #1e293b;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 2rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Input Section */
#no {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  margin-bottom: 2rem;
  border: 1px solid #e2e8f0;
}

#no label {
  color: #475569;
  font-weight: 600;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  display: block;
}

#noResi {
  width: 100%;
  padding: 1rem 1.5rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #f8fafc;
}

#noResi:focus {
  outline: none;
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
  background: white;
}

#noResi::placeholder {
  color: #94a3b8;
}

/* Button */
.but {
  text-align: center;
  margin-bottom: 2rem;
}

#lacak {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  color: white;
  padding: 1rem 3rem;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 14px 0 rgba(139, 92, 246, 0.39);
  transform: translateY(0);
}

#lacak:hover:not(:disabled) {
  background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px 0 rgba(139, 92, 246, 0.5);
}

#lacak:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Error Message */
.error-message {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #dc2626;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  margin-bottom: 2rem;
  border-left: 4px solid #dc2626;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.error-icon {
  font-size: 1.2rem;
}

/* Tracking Result */
.tracking-result {
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

/* Status Cards */
.status-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  padding: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.status-card {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 1.5rem;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.status-label {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-value {
  color: white;
  font-size: 1.1rem;
  font-weight: 700;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.1);
}

.status-completed {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
}

.status-shipping {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
}

.status-processing {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
}

.status-pending {
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
}

/* Timeline */
.timeline-container {
  padding: 2rem;
}

.timeline-title {
  color: #1e293b;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 2rem;
  text-align: center;
}

.tracking-step {
  position: relative;
  margin-bottom: 2rem;
  display: flex;
  gap: 2rem;
  height: auto;
  align-items: flex-start;
}

/* garis */
.tracking-step:not(:last-child)::after {
  content: '';
  position: absolute;
  left: 152px;
  top: 60px;
  width: 2px;
  margin-top: -50px;
  height: calc(100% + 1rem);
  background: #8b5cf6;
  /* background: linear-gradient(to bottom, #8b5cf6, #e2e8f0); */
}

.step-date {
  font-weight: 600;
  color: #64748b;
  font-size: 0.9rem;
  min-width: 120px;
  padding-top: 0.5rem;
}

.step-content {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  flex: 1;
}

.step-dot {
  width: 16px;
  height: 16px;
  margin-right: 10%;
  border-radius: 50%;
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2);
  flex-shrink: 0;
  margin-top: 0.25rem;
  position: relative;
}

.step-dot.completed {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
}

.step-dot.completed::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 0.7rem;
  font-weight: bold;
}

/* kotak  */
.step-info {
  flex: 1;
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 12px;
  border-left: 4px solid #8b5cf6;
}

.tracking-step.completed .step-info {
  border-left-color: #10b981;
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.step-time {
  font-weight: 700;
  color: #8b5cf6;
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.tracking-step.completed .step-time {
  color: #10b981;
}

.step-desc {
  color: #475569;
  line-height: 1.6;
}

.step-desc strong {
  color: #1e293b;
  display: block;
  margin-bottom: 0.25rem;
}

.highlight {
  color: #8b5cf6;
  font-weight: 600;
  padding: 0.2rem 0.5rem;
  background: rgba(139, 92, 246, 0.1);
  border-radius: 6px;
}

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }

  #la {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }

  #no {
    padding: 1.5rem;
  }

  .status-cards {
    grid-template-columns: 1fr;
    gap: 1rem;
    padding: 1.5rem;
  }

  .tracking-step {
    flex-direction: column;
    gap: 1rem;
  }

  .tracking-step:not(:last-child)::after {
    left: 8px;
    top: 80px;
    height: calc(100% - 1rem);
  }

  .step-date {
    min-width: auto;
    padding-top: 0;
  }

  .step-content {
    margin-left: 0;
  }

  .timeline-container {
    padding: 1.5rem;
  }

  #lacak {
    width: 100%;
    padding: 1rem 2rem;
  }
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.tracking-result {
  animation: fadeInUp 0.6s ease-out;
}

.tracking-step {
  animation: fadeInUp 0.4s ease-out;
  animation-fill-mode: both;
}

.tracking-step:nth-child(1) { animation-delay: 0.1s; }
.tracking-step:nth-child(2) { animation-delay: 0.2s; }
.tracking-step:nth-child(3) { animation-delay: 0.3s; }
.tracking-step:nth-child(4) { animation-delay: 0.4s; }
.tracking-step:nth-child(5) { animation-delay: 0.5s; }
.tracking-step:nth-child(6) { animation-delay: 0.6s; }
.tracking-step:nth-child(7) { animation-delay: 0.7s; }
.tracking-step:nth-child(8) { animation-delay: 0.8s; }
</style>