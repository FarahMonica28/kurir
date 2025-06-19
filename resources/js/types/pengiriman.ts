export interface Pengiriman {
  id: number;
  deskripsi: string;
  kurir: {
      id: Number;
      name: string;
  };
  transaksii_id: Number;
}
