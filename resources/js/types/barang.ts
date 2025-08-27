import { Kategori } from './kategori';

export interface Barang {
  id: number;
  nama: string;
  stok: number;
  kategori_id: number;
  harga: number;
  photo: string; // URL foto
  kategori?: Kategori; // Optional, kalau di-load dengan with()
  created_at?: string;
  updated_at?: string;
}
