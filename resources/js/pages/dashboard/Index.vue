<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from '@/libs/axios';
import { useAuthStore } from "@/stores/auth";
import { watch } from 'vue';
import Swal from 'sweetalert2';

const User = ref({ name: "", role: "kurir" });
const showTransaksii = ref(false);
const transaksiList = ref([]);
const todayCount = ref(0);
const yesterdayCount = ref(0);
const monthCount = ref(0);
const transaksiiCount = ref(0);
// const transaksiCount = ref({
//   todayCount: 0,
//   yesterdayCount: 0,
//   monthCount: 0,
//   custom: 0,
// });


const store = useAuthStore();

// const getProfile = async () => {
//   User.value.name = store.user.name;
//   // User.value.role = store.user.role;

//   if (User.value.role === 'kurir') {
//     try {
//       const res = await axios.get("/kurir/transaksi-count");
//       transaksiCount.value = res.data.count;
//     } catch (error) {
//       console.error("Gagal mengambil jumlah transaksi", error);
//     }
//   }
// };
const getProfile = async () => {
  User.value.name = store.user.name;

  if (User.value.role === 'kurir') {
    try {
      const res = await axios.get("/kurir/transaksii-count");
      transaksiiCount.value.today = res.data.today;
      transaksiiCount.value.yesterday = res.data.yesterday;
      transaksiiCount.value.month = res.data.month;
      transaksiiCount.value.custom = res.data.custom; // opsional jika digunakan
    } catch (error) {
      console.error("Gagal mengambil jumlah transaksi", error);
    }
  }
};



// const getTransaksiList = async () => {
//   try {
//     const res = await axios.get('/kurir/transaksi-list');
//     transaksiList.value = res.data.data;
//     showTransaksi.value = true;
//   } catch (error) {
//     console.error("Gagal mengambil daftar transaksi", error);
//   }
// };

const getTransaksiList = async (filter = null) => {
  // if (User.value.role === 'kurir') {
    try {
      const res = await axios.get('/kurir/transaksii-list', {
        params: { filter }  // kirim filter ke backend
      });
      transaksiList.value = res.data.data;
      yesterdayCount.value = res.data.yesterdayCount;
      todayCount.value = res.data.todayCount;
      monthCount.value = res.data.monthCount;
      showTransaksii.value = true;
    }  catch (error) {
      if (error.response && error.response.status === 403) {
        Swal.fire({
          icon: 'error',
          title: 'Akses Ditolak',
          text: error.response.data.message || 'Anda tidak memiliki izin untuk melihat data ini.'
        });
      } else {
        console.error("Gagal mengambil daftar transaksi", error);
        Swal.fire({
          icon: 'error',
          title: 'Terjadi Kesalahan',
          text: 'Gagal mengambil data transaksi.'
        });
      }
    // }
  }
};


const closeTransaksiList = () => {
  showTransaksii.value = false;
};



onMounted(() => {
  getProfile();
});

watch(() => store.user, (newUser) => {
  if (newUser.role === 'kurir') {
    getProfile();
  }
}, { immediate: true });

</script>




<template>
  <main v-if="User.role === 'kurir'">
    <h1>Selamat datang, {{ User.name }} 👋🏻</h1>

    <!-- Box Transaksi -->
    <div class="box-wrapper mt-5">
      <!-- Box Kemarin -->
      <div class="box">
        <div>
          <h5>Total Orderan Kemarin</h5>
          <h1 class="mt-3">{{ yesterdayCount }}</h1>
          <h3 @click="getTransaksiList('kemarin')">Lihat Detail</h3>
        </div>
      </div>

      <!-- Box Hari Ini -->
      <div class="box">
        <div>
          <h5>Total Orderan Hari Ini</h5>
          <h1 class="mt-3">{{ todayCount }}</h1>
          <h3 @click="getTransaksiList('hari_ini')">Lihat Detail</h3>
        </div>
      </div>

      <!-- Box Bulan Ini -->
      <div class="box">
        <div>
          <h5>Total Orderan Bulan Ini</h5>
          <h1 class="mt-3">{{ monthCount }}</h1>
          <h3 @click="getTransaksiList('bulan_ini')">Lihat Detail</h3>
        </div>
      </div>
    </div>

    <!-- Tabel Transaksi -->
    <div v-if="showTransaksii" class="mt-5">
      <div class="flex justify-between items-center mb-3">
        <h4>Riwayat Order</h4>
      </div>
      <table class="riwayat-table">
        <thead>
          <tr>
            <th>#</th>
            <th>No Resi</th>
            <th>Nama Barang</th>
            <th>Alamat Tujuan</th>
            <th>Pengirim</th>
            <th>Penerima</th>
            <th>Rating</th>
            <th>Status</th>
            <th>Waktu</th>
          </tr> 
        </thead>
        <tbody>
          <tr v-for="(item, index) in transaksiList" :key="item.no_resi">
            <td>{{ index + 1 }}.</td>
            <td>{{ item.no_resi }}</td>
            <td>{{ item.nama_barang }}</td>
            <td>{{ item.alamat_tujuan }}</td>
            <td>{{ item.pengirim }}</td>
            <td>{{ item.penerima }}</td>
            <td>
              <template v-if="item.rating">
                <span class="rating-stars">
                  <span
                    v-for="i in 5"
                    :key="i"
                    :class="i <= item.rating ? 'text-warning' : 'text-muted'"
                    class="star"
                  >
                    ★
                  </span>
                </span>
              </template>
              <template v-else>
                <span class="badge yellow">Belum ada rating</span>
              </template>
            </td>
            <td>{{ item.status }}</td>
            <td>{{ item.waktu }}</td>
          </tr>
        </tbody>
      </table>
      <button class="btn-tutup mt-5" @click="closeTransaksiList">Tutup</button>
    </div>
  </main>

  <!-- Jika bukan kurir -->
  <main v-else>
    <h2>Akses hanya tersedia untuk Kurir.</h2>
  </main>
</template>



<style scoped>
.box-wrapper {
  display: flex;
  gap: 20px; /* jarak antar box */
  flex-wrap: wrap; /* biar responsif kalau layar kecil */
}

.box {
  flex: 1 1 30%;
  min-width: 200px;
  text-align: center;
  border: 3px solid;
  border-radius: 10px;
  cursor: pointer;
  padding: 20px;
}
h3:hover {
  color: gray;
}

.riwayat-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.riwayat-table th,
.riwayat-table td {
  border: 1px solid #ddd;
  padding: 8px;
}

.riwayat-table th {
  background-color: #f3f4f6;
  text-align: left;
}

.badge {
  padding: 2px 8px;
  border-radius: 5px;
  font-size: 12px;
}

.badge.green {
  background-color: green;
  color: white;
}

.badge.yellow {
  background-color: gold;
  color: black;
}

.btn-lihat {
  background-color: #7c3aed;
  color: white;
  padding: 5px 10px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.btn-tutup {
  background-color: #ef4444;
  color: white;
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}

.btn-tutup:hover {
  background-color: gray;
}
.rating-stars .star {
  font-size: 18px;
  margin-right: 2px;
}

.text-warning {
  color: gold;
}

.text-muted {
  color: #ccc;
}

</style>
