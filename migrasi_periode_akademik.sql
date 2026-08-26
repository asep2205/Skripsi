-- Jalankan sekali pada database aktif sebelum menggunakan menu Periode Akademik.
-- Data lama dipindahkan ke periode arsip agar riwayat tidak hilang.

CREATE TABLE IF NOT EXISTS `periode_akademik` (
  `id_periode` INT NOT NULL AUTO_INCREMENT,
  `tahun_ajaran` VARCHAR(9) NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `status` ENUM('aktif','arsip') NOT NULL DEFAULT 'arsip',
  `dibuat_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_periode`),
  UNIQUE KEY `uniq_tahun_ajaran` (`tahun_ajaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `periode_akademik`
  (`id_periode`, `tahun_ajaran`, `tanggal_mulai`, `tanggal_selesai`, `status`)
VALUES (1, 'Sebelumnya', '2000-01-01', '2000-06-30', 'arsip');

ALTER TABLE `laporan_prilaku` ADD COLUMN `id_periode` INT NULL AFTER `id_user`;
ALTER TABLE `remisi` ADD COLUMN `id_periode` INT NULL AFTER `id_user`;
ALTER TABLE `tindaklanjut` ADD COLUMN `id_periode` INT NULL AFTER `id_users`;

UPDATE `laporan_prilaku` SET `id_periode` = 1 WHERE `id_periode` IS NULL;
UPDATE `remisi` SET `id_periode` = 1 WHERE `id_periode` IS NULL;
UPDATE `tindaklanjut` SET `id_periode` = 1 WHERE `id_periode` IS NULL;

ALTER TABLE `laporan_prilaku` ADD KEY `idx_laporan_periode` (`id_periode`);
ALTER TABLE `remisi` ADD KEY `idx_remisi_periode` (`id_periode`);
ALTER TABLE `tindaklanjut` ADD KEY `idx_tindaklanjut_periode` (`id_periode`);
