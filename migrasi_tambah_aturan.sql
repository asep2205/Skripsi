-- =============================================================
-- MIGRASI: Menambahkan aturan Reward + Punishment singkat
-- serta sample training data untuk NLP Naive Bayes
-- =============================================================

-- 1. Tambah aturan baru ke master_poin (Reward + Punishment)
INSERT INTO `master_poin` (`id_aturan`, `jenis`, `nama_perilaku`, `poin`) VALUES
-- Reward (merapikan buku)
(260, 'Reward', 'merapikan buku di perpustakaan', 20),
(261, 'Reward', 'membantu merapikan buku perpustakaan', 20),
(262, 'Reward', 'merapikan buku dan membersihkan perpustakaan', 20),
(263, 'Reward', 'merapikan buku perpustakaan', 20),
(264, 'Reward', 'siswa merapikan buku di perpustakaan', 20),
(265, 'Reward', 'membantu merapikan buku di perpustakaan', 20),
-- Punishment (singkat, agar cocok dengan strpos)
(266, 'Punishment', 'terlambat masuk', 10),
(267, 'Punishment', 'kesiangan', 10),
(268, 'Punishment', 'membolos', 15),
(269, 'Punishment', 'tidak mengerjakan tugas', 10),
(270, 'Punishment', 'tidak masuk sekolah', 10),
(271, 'Punishment', 'merokok di sekolah', 30),
(272, 'Punishment', 'berkelahi', 50),
(273, 'Punishment', 'mencontek', 15),
(274, 'Punishment', 'mencoret coret', 10),
(275, 'Punishment', 'membuang sampah sembarangan', 5),
(276, 'Punishment', 'tidak memakai seragam', 10),
(277, 'Punishment', 'rambut tidak rapi', 5),
(278, 'Punishment', 'telat', 10),
(279, 'Punishment', 'tidak membawa buku', 5),
(280, 'Punishment', 'main hp saat pelajaran', 10),
(281, 'Punishment', 'tidak sopan', 5),
(282, 'Punishment', 'berbohong', 10);

-- 2. Tambah sample training data untuk dataset NLP
INSERT INTO `dataset_training` (`id_data`, `teks_sampel`, `label`) VALUES
-- Reward: merapikan buku
(708, 'siswa ikut merapikan buku di perpustakaan sekolah', 'Reward'),
(709, 'siswa membantu petugas merapikan buku perpustakaan', 'Reward'),
(710, 'siswa merapikan buku perpustakaan dengan rapi', 'Reward'),
(711, 'siswa dengan inisiatif merapikan buku di perpustakaan', 'Reward'),
(712, 'siswa membersihkan dan merapikan rak buku perpustakaan', 'Reward'),
(713, 'siswa membantu merapikan buku pelajaran di perpustakaan', 'Reward'),
-- Punishment: pelanggaran umum
(714, 'siswa terlambat masuk sekolah', 'Punishment'),
(715, 'siswa telat datang ke sekolah', 'Punishment'),
(716, 'siswa kesiangan dan datang terlambat', 'Punishment'),
(717, 'siswa membolos saat jam pelajaran', 'Punishment'),
(718, 'siswa tidak mengerjakan tugas', 'Punishment'),
(719, 'siswa tidak masuk sekolah tanpa keterangan', 'Punishment'),
(720, 'siswa merokok di lingkungan sekolah', 'Punishment'),
(721, 'siswa berkelahi dengan teman', 'Punishment'),
(722, 'siswa mencontek saat ujian', 'Punishment'),
(723, 'siswa membuang sampah sembarangan', 'Punishment'),
(724, 'siswa tidak memakai seragam lengkap', 'Punishment'),
(725, 'siswa rambutnya panjang dan tidak rapi', 'Punishment'),
(726, 'siswa tidak membawa buku pelajaran', 'Punishment'),
(727, 'siswa bermain hp saat guru menjelaskan', 'Punishment'),
(728, 'siswa berkata tidak sopan kepada guru', 'Punishment'),
(729, 'siswa berbohong kepada wali kelas', 'Punishment'),
(730, 'siswa mencoret coret meja dan kursi', 'Punishment'),
(731, 'siswa tidak memakai atribut sekolah', 'Punishment'),
(732, 'siswa datang terlambat dan tidak mengerjakan tugas', 'Punishment'),
(733, 'siswa terlambat masuk setelah jam istirahat', 'Punishment');
