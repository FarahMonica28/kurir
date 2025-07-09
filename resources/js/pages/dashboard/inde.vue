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
    <div v-if="showTransaksii" class="mt-5">
      <div class="flex justify-between items-center mb-3">
        <!-- <h4>Riwayat Order</h4> -->
        <h4>Riwayat Order {{ filterTypeLabel }}</h4>
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
            <td>{{ index + 1. }}</td>
            <td>{{ item.no_resi }}</td>
            <td>{{ item.nama_barang }}</td>
            <td>{{ item.alamat_tujuan }}</td>
            <td>{{ item.pengirim }}</td>
            <td>{{ item.penerima }}</td>
            <!-- <td>
              <span v-if="item.rating" class="badge green">{{ item.rating }}</span>
              <span v-else class="badge yellow">belum ada rating</span>
            </td> -->
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
</template>