<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from '@/libs/axios';
import { useAuthStore } from "@/stores/auth";
import { watch } from 'vue';
import Swal from 'sweetalert2';

const User = ref({ name: "", role: "kurir" });
const showTransaksi = ref(false);
const transaksiList = ref([]);
const todayCount = ref(0);
const yesterdayCount = ref(0);
const monthCount = ref(0);
const transaksiCount = ref(0);
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
      const res = await axios.get("/kurir/transaksi-count");
      transaksiCount.value.today = res.data.today;
      transaksiCount.value.yesterday = res.data.yesterday;
      transaksiCount.value.month = res.data.month;
      transaksiCount.value.custom = res.data.custom; // opsional jika digunakan
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
      const res = await axios.get('/kurir/transaksi-list', {
        params: { filter }  // kirim filter ke backend
      });
      transaksiList.value = res.data.data;
      yesterdayCount.value = res.data.yesterdayCount;
      todayCount.value = res.data.todayCount;
      monthCount.value = res.data.monthCount;
      showTransaksi.value = true;
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
  showTransaksi.value = false;
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
  <main>
    <h1>Selamat datang, {{ User.name }} 👋🏻</h1>

    <!-- Hanya tampil jika role adalah kurir -->
    <!-- <div v-if="User.role === 'kurir'" class="box mt-5" @click="getTransaksiList">
      <div class="mt-5">
        <h5>Total Transaksi Hari Ini</h5>
        <h3 class="mt-3">{{ transaksiCount }}</h3>
      </div>
    </div> -->
    <!-- Box Total Transaksi -->
    <!-- Box Total Orderan Kemarin -->
    <!-- Wrapper untuk flex container -->
    <div class="box-wrapper mt-5">
      <!-- Box Kemarin -->
      <div class="box">
        <div>
           <!-- <h5>{{ yesterdayCount }} Total Orderan Kemarin</h5> -->
           <h5> Total Orderan Kemarin</h5>
          <h1 class="mt-3">{{ yesterdayCount }}</h1>
          <h3 v-if="User.role === 'kurir'" @click="getTransaksiList('kemarin')">Lihat Detail</h3>
          <!-- <h3 v-if="User.role === 'kurir'" @click="getTransaksiList('kemarin')">Lihat Detail</h3> -->
        </div>
      </div>

      <!-- Box Hari Ini -->
      <div class="box">
        <div>
          <h5> Total Orderan Hari Ini</h5>
          <h1 class="mt-3">{{ todayCount }}</h1>
          <h3 v-if="User.role === 'kurir'" @click="getTransaksiList('hari_ini')">Lihat Detail</h3>
          <!-- <h3 v-if="User.role === 'kurir'" @click="getTransaksiList('hari_ini')">Lihat Detail</h3> -->
        </div>
      </div>

      <!-- Box Bulan Ini -->
      <div class="box">
        <div>
          <h5> Total Orderan Bulan Ini</h5>
          <h1 class="mt-3">{{ monthCount }}</h1>
          <h3 v-if="User.role === 'kurir'" @click="getTransaksiList('bulan_ini')">Lihat Detail</h3>
          <!-- <h3 v-if="User.role === 'kurir'" @click="getTransaksiList('bulan_ini')">Lihat Detail</h3> -->
        </div>
      </div>
    </div>



    <!-- Tabel Transaksi -->
    <div v-if="showTransaksi" class="mt-5">
      <div class="flex justify-between items-center mb-3">
        <!-- <h4>Riwayat Order</h4> -->
        <h4>Riwayat Order {{ filterTypeLabel }}</h4>
      </div>
      <table class="riwayat-table">
        <thead>
          <tr>
            <th>#</th>
            <th>No Order</th>
            <th>Nama Barang</th>
            <th>Alamat Tujuan</th>
            <th>Pengirim</th>
            <th>Penerima</th>
            <th>Penilaian</th>
            <th>Status</th>
            <th>Waktu</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in transaksiList" :key="item.id">
            <td>{{ index + 1. }}</td>
            <td>{{ item.id }}</td>
            <td>{{ item.nama_barang }}</td>
            <td>{{ item.alamat_tujuan }}</td>
            <td>{{ item.pengguna.user.name }}</td>
            <td>{{ item.penerima }}</td>
            <td>
              <span v-if="item.penilaian" class="badge green">{{ item.penilaian }}</span>
              <span v-else class="badge yellow">belum ada penilaian</span>
            </td>
            <td>{{ item.status }}</td>
            <td>{{ item.waktu }}</td>
          </tr>
        </tbody>
      </table>
      <button class="btn-tutup mt-5" @click="closeTransaksiList">Tutup</button>
    </div>
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
</style>
