<template>
  <div class="container mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-center mb-8">📦 Lacak Pengiriman</h1>

    <!-- Input Resi -->
    <div class="flex justify-center mb-6 gap-2">
      <input v-model="noResi" type="text" placeholder="Masukkan Nomor Resi" class="border rounded-lg px-4 py-2 w-72" />
      <button @click="askLast4" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
        Cari
      </button>
    </div>

    <div class="mt-">
      <div class="tracking-container ">

        <div class="tracking-step">
          <div class="circle">
            <img src="/storage/photo/pakett.png" alt="Pick Up" />
          </div>
          <br>
          <p>Packing</p>
        </div>

        <div class="line"></div>
        <div class="tracking-step">
          <div class="circle">
            <img src="/storage/photo/pickupp.png" alt="On Transit" />
          </div>
          <br>
          <p>Pick Up</p>
        </div>

        <div class="line"></div>
        <div class="tracking-step">
          <div class="circle">
            <img src="/storage/photo/transitt.png" alt="On Transit" />
          </div>
          <br>
          <p>On Transit</p>
        </div>

        <div class="line"></div>
        <div class="tracking-step">
          <div class="circle">
            <img src="/storage/photo/ondeliveryy.png" alt="On Delivery" />
          </div>
          <br>
          <p>On Delivery</p>
        </div>

        <div class="line"></div>
        <div class="tracking-step">
          <div class="circle">
            <img src="/storage/photo/deliveredd.png" alt="Delivered" />
          </div>
          <br>
          <p>Delivered</p>
        </div>

      </div>
    </div>
    <!-- Error -->
    <div v-if="error" class="text-red-500 text-center mb-6">
      {{ error }}
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center">
      <img src="/img/loading.gif" alt="Loading..." class="w-12 h-12 animate-spin" />
    </div>


    <div v-if="error" class="text-red-600 mt-8">{{ error }}</div>

    <h1 class="mt-5">Detail</h1>
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
        <!-- Paket dibuat (packing) -->
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

        <!-- Kurir menuju rumah (pickup) -->
        <div v-if="data.waktu_diambil" class="tracking-header">
          <p class="tracking-date">{{ formatDate(data.waktu_diambil) }}</p>
          <div class="tracking-timeline mt-6">
            <div class="tracking-item">
              <div class="dot"></div>
              <!-- <img class="dot-img" src="/public/storage/photo/gudang.jpgyyyy" alt="Kurir Ambil" /> -->
              <div class="content">
                <div class="time">{{ data.waktu_diambil.slice(11, 16) }}</div>
                <div class="desc">
                  Kurir sedang menuju ke rumahmu {{ data.alamat_asal }}
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

        <!-- Di Gudang (transit) -->
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
              <!-- <div class="dot"></div> -->
              <img class="dot-img" :src="data?.ambil?.photo || '/img/default-user.png'" alt="Kurir" />
              <div class="content">
                <div class="time">{{ data.waktu_tiba.slice(11, 16) }}</div>
                <div class="desc">Paket telah tiba digudang kota {{ data.asal_kota.name }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dikirim (delivery)-->
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

        <!-- Selesai delivered -->
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
import { ref, computed } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

const noResi = ref("");
const last4 = ref("");
const loading = ref(false);
const error = ref("");
const data = ref<any>(null);

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
    error.value = "Nomor resi harus diisi!";
    return;
  }

  const { value: phone } = await Swal.fire({
    title: "Verifikasi Nomor HP",
    input: "text",
    inputPlaceholder: "Masukkan 4 digit terakhir nomor HP",
    inputAttributes: {
      maxlength: "4",
    },
    confirmButtonText: "Lanjut",
    showCancelButton: true,
    cancelButtonText: "Batal",
    inputValidator: (value) => {
      if (!value) return "Harus diisi!";
      if (!/^[0-9]{4}$/.test(value)) return "Harus 4 digit angka!";
      return null;
    },
  });

  if (phone) {
    last4.value = phone;
    fetchTracking();
  }
};

// 🔹 Step 2: Fetch tracking
const fetchTracking = async () => {
  loading.value = true;
  error.value = "";
  data.value = null;

  try {
    const response = await axios.get(`/tracking/${noResi.value}`, {
      params: { last4: last4.value },
    });
    data.value = response.data.data;
  } catch (err: any) {
    error.value = err.response?.data?.message || "Data tidak ditemukan";
  } finally {
    loading.value = false;
  }
};

// 🔹 Step 3: Timeline
const timeline = computed(() => {
  if (!data.value) return [];

  return [
    {
      date: data.value.waktu,
      title: "Paket Dibuat",
      desc: "Paket telah dibuat oleh pengirim",
      icon: "/storage/photo/made.png",
    },
    {
      date: data.value.waktu_diambil,
      title: "Kurir Ambil",
      desc: `Kurir menuju alamat asal ${data.value.alamat_asal}`,
      icon: "/storage/photo/kurir.png",
    },
    {
      date: data.value.waktu_digudang,
      title: "Tiba di Gudang",
      desc: "Paket sudah sampai di gudang",
      icon: "/storage/photo/gudang.png",
    },
    {
      date: data.value.waktu_selesai,
      title: "Selesai",
      desc: "Paket berhasil sampai ke penerima 🎉",
      icon: "/storage/photo/finish.png",
    },
  ].filter((step) => step.date);
});


</script>

<style>
h1{
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
  justify-content: center;
  align-items: center;
  margin: 40px 0;
}

/* .tracking-step {
  border-radius: 60% dashed pink;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
} */
.tracking-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}


/* dashed border di luar lingkaran */
/* .circle::after {
  content: "";
  position: absolute;
  top: -px;
  left: -px;
  width: 90px;
  height: 90px;
  border: 2px dashed red;
  border-radius: 50%;
} */
 .circle::after {
  content: "";
  position: absolute;
  top: -10px;
  /* left: -px; */
  width: 90px;
  height: 90px;
  border-radius: 50%;
  border: 2px dashed red;
  box-sizing: border-box;

  /* animasi gerakan */
  /* animation: spin 2s linear infinite; animasi putar */
    animation: spinStep 2s steps(15) infinite;
}

@keyframes spinStep {
  from {
    transform: rotate(deg);
  }
  to {
    transform: rotate(60deg); /* putar searah jarum jam */
    /* transform: rotate(60deg); putar searah jarum jam */
  }
}

.tracking-step p {
  margin-top: 5px;
  font-size: 14px;
  color: red;
  font-weight: 200;
}


.circle {
  width: 70px;
  height: 70px;
  border: px dashed pink;
  border-radius: 60%;
  display: flex;
  justify-content: center;
  align-items: center;
  background: pink;
  /* margin-bottom: 10px; */
}

.circle img {
  width: 70px;
  height: 70px;
  object-fit: contain;
}

/* .circle img {
  width: 50px;
  height: 50px;
  object-fit: contain;
} */

/* .tracking-step p {
  margin-top: 5px;
  font-size: 14px;
  color: red;
  font-weight: 500;
} */

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

/* .dot-img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  position: absolute;
  left: -4.3rem;
  top: 0.25rem;
  z-index: 1;
  border: 2px solid white;
  background-color: #f3f4f6;
} */

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
  background-color: #f57ce2;
  /* background-color: #a855f7; */
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
  background-color: #ff44dd;
  /* background-color: #a855f7; */
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
