-- Jalankan sekali pada database yang sudah ada sebelum memakai fitur verifikasi.
ALTER TABLE laporan_prilaku
    ADD COLUMN status_verifikasi ENUM('pending', 'disetujui', 'ditolak') NOT NULL DEFAULT 'pending' AFTER foto,
    ADD COLUMN id_verifikator INT(11) NULL AFTER status_verifikasi,
    ADD COLUMN tgl_verifikasi TIMESTAMP NULL DEFAULT NULL AFTER id_verifikator,
    ADD COLUMN catatan_verifikasi TEXT NULL AFTER tgl_verifikasi;

-- Laporan lama sudah pernah masuk ke total poin, sehingga ditandai disetujui.
UPDATE laporan_prilaku
SET status_verifikasi = 'disetujui'
WHERE status_verifikasi = 'pending';
