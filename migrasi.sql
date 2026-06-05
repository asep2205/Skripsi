-- Migrasi: tambah kolom nama_perilaku, hapus id_aturan_tercocok
-- Jalankan satu per satu. Lewati yang error (artinya sudah dihapus sebelumnya).

-- 1. Cek foreign key yang ada
SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_NAME = 'laporan_perilaku' AND COLUMN_NAME = 'id_aturan_tercocok' AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 2. Hapus foreign key (ganti nama sesuai hasil query 1)
ALTER TABLE `laporan_perilaku` DROP FOREIGN KEY `laporan_perilaku_ibfk_3`;

-- 3. Hapus index
ALTER TABLE `laporan_perilaku` DROP INDEX `id_aturan_tercocok`;

-- 4. Hapus kolom
ALTER TABLE `laporan_perilaku` DROP COLUMN `id_aturan_tercocok`;

-- 5. Tambah kolom baru
ALTER TABLE `laporan_perilaku` ADD COLUMN `nama_perilaku` varchar(255) DEFAULT NULL AFTER `label_prediksi`;
