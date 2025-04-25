export interface Pengirimans {
    id: string;
    no_resi: string;
    penerima: string;
    alamat_tujuan: string;
    status: "belum_diambil" | "diambil" | "sedang_dikirim" | "terkirim" | "gagal";
    waktu_ambil?: string;
    waktu_kirim?: string;
  }
  