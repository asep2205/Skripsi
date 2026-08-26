-- Jalankan sekali setelah migrasi_periode_akademik.sql.
-- Snapshot ini menjaga papan peringkat periode yang sudah diarsipkan,
-- meskipun saldo poin aktif pada tabel siswa telah di-reset.
CREATE TABLE IF NOT EXISTS `rekap_poin_periode` (
  `id_rekap` INT NOT NULL AUTO_INCREMENT,
  `id_periode` INT NOT NULL,
  `id_siswa` INT NOT NULL,
  `total_poin_reward` INT NOT NULL DEFAULT 0,
  `total_poin_punishment` INT NOT NULL DEFAULT 0,
  `dibuat_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rekap`),
  UNIQUE KEY `uniq_rekap_periode_siswa` (`id_periode`, `id_siswa`),
  KEY `idx_rekap_periode` (`id_periode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
