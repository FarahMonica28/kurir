export interface transaksi {
    id: number;                // ID transaksi (primary key)
    no_transaksi: string;      // Nomor unik transaksi
    pengirim: string;          // Nama pengirim
    penerima: string;          // Nama penerima
    alamat_asal: string;       // Alamat asal barang
    alamat_tujuan: string;     // Alamat tujuan pengiriman
    no_hp_penerima: string;
    nama_barang: string;     // nama barang
    berat_barang: number;      // Berat barang dalam kg
    biaya: number;             // Biaya pengiriman
    waktu: string;             // Tanggal dan waktu transaksi
    status: "Belum Terkirim" | "Penjemputan Barang" | "Sedang Dikirim" | "Terkirim";  // Status pengiriman
    penilaian?: number | null; // Penilaian (1-5)
    komentar?: string | null;  // Komentar feedback
    // kurir_id: number;          // ID kurir yang mengantarkan
    kurir: {
        name: string;   
    };
    created_at?: string;       // Waktu dibuat
    updated_at?: string;       // Waktu diperbarui
}
