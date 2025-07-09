export interface transaksii {
    id: number;                // ID transaksi
    no_resi: string;
    nama: string;             // Nama pengirim
    alamat_asal: string;      // Alamat asal
    alamat_tujuan: string;    // Alamat tujuan
    penerima: string;         // Nama penerima
    // pengirim: string;         // Nama penerima
    berat_barang: number;     // Berat barang
    ekspedisi: string;        // Nama ekspedisi (JNE, J&T, dll.)
    layanan: string;          // Jenis layanan (YES, REG, dll.)
    biaya: number;            // Biaya pengiriman
    waktu: string;            // Waktu transaksi (format datetime)
    status: "menunggu"|"diambil kurir"| "dikurir" |"digudang"| "diproses" | "tiba digudang" | "dikirim" | "selesai";  // Status internal sistem
    rating?: number | null;  // Penilaian opsional
    komentar?: string | null;   // Komentar opsional
    pernah_digudang: number;
    status_pembayaran: string;
    
    asal_provinsi_id: {
        id: number;
        name: string;
    }
    asal_kota_id: {
        id: number;
        name: string;
    }
    tujuan_provinsi_id: {
        id: number;
        name: string;
    }
    tujuan_kota_id: {
        id: number;
        name: string;v
    }

    pengguna: {
        name: string;
        // name: string;
    };
    kurir: {
        id: number;
        name: string;
        // name: string;
    };

    created_at?: string;
    updated_at?: string;
}
