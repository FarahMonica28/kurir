<script setup>
import { onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

onMounted(async () => {
  const draftStr = sessionStorage.getItem("draftTransaksi");
  if (!draftStr) {
    alert("Data transaksi tidak ditemukan.");
    return router.push("/transaksii/order");
  }

  const draft = JSON.parse(draftStr);
  const formData = new FormData();

  formData.append("penerima", draft.penerima);
  formData.append("tujuan_provinsi_id", draft.provinceDestination);
  formData.append("tujuan_kota_id", draft.cityDestination);
  formData.append("alamat_tujuan", draft.alamat_tujuan);
  formData.append("no_hp_penerima", draft.no_hp_penerima);

  formData.append("pengirim", draft.pengirim);
  formData.append("nama_barang", draft.nama_barang);
  formData.append("asal_provinsi_id", draft.provinceOrigin);
  formData.append("asal_kota_id", draft.cityOrigin);
  formData.append("alamat_asal", draft.alamat_asal);

  formData.append("ekspedisi", draft.selectedCourier);
  formData.append("layanan", draft.selectedService);
  formData.append("berat_barang", draft.berat_barang?.toString() || "0");
  formData.append("biaya", draft.biaya.toString());
  formData.append("waktu", new Date().toISOString());
  formData.append("status", "menunggu");

  try {
    await axios.post("/transaksii/store", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    sessionStorage.removeItem("draftTransaksi");
    router.push("/transaksii/order"); // Arahkan ke halaman order selesai
  } catch (error) {
    console.error(error);
    alert("Gagal menyimpan transaksi.");
  }
});
</script>

<template>
  <div class="text-center mt-10">
    <h2>Menyimpan data transaksi...</h2>
  </div>
</template>
