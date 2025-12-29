-- Update database untuk menambah kolom satuan
-- Jalankan query ini di phpMyAdmin atau MySQL

-- Tambah kolom satuan di tabel barang
ALTER TABLE barang 
ADD COLUMN satuan VARCHAR(20) DEFAULT 'buah' AFTER stok;

-- Tambah kolom satuan di tabel transaksi
ALTER TABLE transaksi 
ADD COLUMN satuan VARCHAR(20) DEFAULT 'buah' AFTER jumlah;

-- Update data existing dengan satuan default
UPDATE barang SET satuan = 'buah' WHERE satuan IS NULL;
UPDATE transaksi SET satuan = 'buah' WHERE satuan IS NULL;
