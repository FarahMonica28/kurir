export interface transaksii {
    id: number;                // ID transaksi
    no_resi: string;
    nama: string;             // Nama pengirim
    penerima: string;         // Nama penerima
    alamat_asal: string;      // Alamat asal
    alamat_tujuan: string;    // Alamat tujuan
    berat_barang: number;     // Berat barang
    ekspedisi: string;        // Nama ekspedisi (JNE, J&T, dll.)
    layanan: string;          // Jenis layanan (YES, REG, dll.)
    biaya: number;            // Biaya pengiriman
    waktu: string;            // Waktu transaksi (format datetime)
    status: "menunggu" | "diproses" | "dikirim" | "selesai";  // Status internal sistem
    penilaian?: number | null;  // Penilaian opsional
    komentar?: string | null;   // Komentar opsional

    asal_provinsi_id: number;
    asal_kota_id: number;
    tujuan_provinsi_id: number;
    tujuan_kota_id: number;

    pengguna: {
        name: string;
    };

    created_at?: string;
    updated_at?: string;
}
