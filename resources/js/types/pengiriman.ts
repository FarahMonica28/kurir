export interface Pengiriman {
  id: number;
  no_resi: string;
  paket: string;
  penerima: string;
  alamat: string;
  kurir: {
      id: Number;
      name: string;
  };
  status: "dikemas" | "dikirim" | "diterima";
  tanggal_dibuat: string | null;
  tanggal_pengiriman: string | null;
  tanggal_penerimaan: string | null;
  biaya: number;
}
