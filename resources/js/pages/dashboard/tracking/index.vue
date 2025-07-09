<template>
  <div class="container">
    <h2 class="text-xl font-semibold mb-4 mt-8">Lacak Pengiriman</h2>
    <div class="mb-4" id="no">
      <h3>
        <label for="noResi" class="block mb-1 mt-6">Nomor Resi :</label>
        <input v-model="noResi" id="noResi" type="text" class="form-input w-full border rounded"
          placeholder="Contoh: ABC-123456" />
      </h3>
    </div>

    <button class="px-4 py-2 rounded" id="lacak" @click="trackResi" :disabled="loading">
      {{ loading ? 'Memuat...' : 'Lacak' }}
    </button>

    <div v-if="error" class="text-red-600 mt-4">{{ error }}</div>

    <div v-if="data" class="mt-6 border rounded p-4 bg-gray-50">
      <p class="mt-5"><strong>Status : </strong> {{ data.status }}</p>
      <p class="mt-5">
        <strong>Ekspedisi : </strong> <span class="text-info">{{ data.ekspedisi }}</span>
      </p>
      <p class="mt-5">
        <strong>Status Pembayaran : </strong> <span class="text-primary">{{ data.status_pembayaran }}</span>
      </p>

      <!-- Paket dibuat -->
      <div class="tracking-header">
        <p class="tracking-date">{{ formatDate(data.waktu) }}</p>
        <div class="tracking-timeline mt-6">
          <div class="tracking-item">
            <div class="dot"></div>
            <div class="content">
              <div class="time">{{ data.waktu.slice(11, 16) }}</div>
              <div class="desc">Paket dibuat oleh {{ data.pengirim?.name || 'pengirim' }}</div>
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
              <div class="desc">Kurir <strong>{{ data?.ambil?.name || 'Kurir' }}</strong> menuju gudang penempatan paket
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
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import axios from 'axios'

const noResi = ref('')
const data = ref<any>(null)
const error = ref('')
const loading = ref(false)

function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}

const trackResi = async () => {
  error.value = ''
  data.value = null
  loading.value = true

  try {
    const response = await axios.get(`/tracking/${noResi.value}`)
    data.value = response.data.data
    console.log("Sudah dapat Data :", data.value)

    // Ambil data kurir jika ada
    if (data.value.kurir_id) {
      try {
        const resKurir = await axios.get(`/kurir/${data.value.kurir_id}`)
        data.value.kurir = resKurir.data
      } catch (e) {
        console.warn('Gagal memuat data kurir', e)
      }
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Terjadi kesalahan saat melacak resi.'
  } finally {
    loading.value = false
  }
}
const kurirAmbil = computed(() =>
  data.value?.pengiriman?.find(p => p.status?.toLowerCase() === 'ambil')?.kurir
)


const kurirKirim = computed(() =>
  data.value?.pengiriman?.find(p => p.status?.toLowerCase() === 'antar')?.kurir
)


</script>



<style scoped>
input {
  border-radius: 5%;
}

.container {
  /* text-align: center; */
  /* border: 1px solid; */
  width: 60%;
}

#noResi {
  width: 50%;
  margin-left: 1%;
  border-radius: 1px solid;

}

#lacak:hover {
  background-color: #a855f7;
  color: white;
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
</style>
