-- =============================================================
-- MIGRASI: Menambahkan aturan Reward untuk "merapikan buku"
-- serta sample training data untuk NLP Naive Bayes
-- =============================================================

-- 1. Tambah aturan baru ke master_poin
INSERT INTO `master_poin` (`id_aturan`, `jenis`, `nama_perilaku`, `poin`) VALUES
(260, 'Reward', 'merapikan buku di perpustakaan', 20),
(261, 'Reward', 'membantu merapikan buku perpustakaan', 20),
(262, 'Reward', 'merapikan buku dan membersihkan perpustakaan', 20),
(263, 'Reward', 'merapikan buku perpustakaan', 20),
(264, 'Reward', 'siswa merapikan buku di perpustakaan', 20),
(265, 'Reward', 'membantu merapikan buku di perpustakaan', 20);

-- 2. Tambah sample training data untuk dataset NLP
INSERT INTO `dataset_training` (`id_data`, `teks_sampel`, `label`) VALUES
(708, 'siswa ikut merapikan buku di perpustakaan sekolah', 'Reward'),
(709, 'siswa membantu petugas merapikan buku perpustakaan', 'Reward'),
(710, 'siswa merapikan buku perpustakaan dengan rapi', 'Reward'),
(711, 'siswa dengan inisiatif merapikan buku di perpustakaan', 'Reward'),
(712, 'siswa membersihkan dan merapikan rak buku perpustakaan', 'Reward'),
(713, 'siswa membantu merapikan buku pelajaran di perpustakaan', 'Reward');
