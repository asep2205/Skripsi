-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 26, 2026 at 11:53 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_perilaku`
--

-- --------------------------------------------------------

--
-- Table structure for table `dataset_training`
--

CREATE TABLE `dataset_training` (
  `id_data` int(11) NOT NULL,
  `teks_sampel` text NOT NULL,
  `label` enum('Reward','Punishment') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dataset_training`
--

INSERT INTO `dataset_training` (`id_data`, `teks_sampel`, `label`) VALUES
(1, 'Siswa datang terlambat ke sekolah karena bangun kesiangan', 'Punishment'),
(2, 'Murid dihukum karena telat masuk kelas setelah jam istirahat selesai', 'Punishment'),
(3, 'Siswa terlambat menghadiri upacara bendera hari senin karena ban motor bocor', 'Punishment'),
(4, 'Siswa sengaja terlambat datang agar terhindar dari razia rambut di gerbang depan', 'Punishment'),
(5, 'Siswa melompati pagar belakang sekolah karena datang terlambat tiga puluh menit', 'Punishment'),
(6, 'Murid telat mengumpulkan tugas akhir mata pelajaran pemrograman', 'Punishment'),
(7, 'Siswa kedapatan nongkrong di warung depan sekolah sehingga terlambat masuk jam pertama', 'Punishment'),
(8, 'Siswa datang telat lebih dari satu jam tanpa membawa surat keterangan dari orang tua', 'Punishment'),
(9, 'Murid terlambat memasuki laboratorium komputer saat ujian praktek sedang berlangsung', 'Punishment'),
(10, 'Siswa dihukum berdiri di lapangan karena datang telat ke sekolah berturut-turut selama tiga hari', 'Punishment'),
(11, 'Siswa secara sukarela membersihkan kaca jendela kelas yang berdebu', 'Reward'),
(12, 'Murid membuang sampah plastik bekas jajanan sembarangan di koridor sekolah', 'Punishment'),
(13, 'Siswa menyapu dan mengepel lantai ruang kelas hingga bersih sebelum pelajaran dimulai', 'Reward'),
(14, 'Siswa kedapatan mencoret-coret meja belajar menggunakan tipe-x dan spidol', 'Punishment'),
(15, 'Murid merapikan kembali peralatan setelah piket kebersihan selesai', 'Reward'),
(16, 'Siswa mengotori dinding toilet sekolah dengan bekas telapak kaki', 'Punishment'),
(17, 'Siswa membuang sisa makanan ke dalam tempat sampah organik yang benar', 'Reward'),
(18, 'Murid meninggalkan ruang laboratorium dalam keadaan kotor dan penuh sampah kertas', 'Punishment'),
(19, 'Siswa membantu menyiram tanaman dan membersihkan rumput liar di taman sekolah', 'Reward'),
(20, 'Siswa sengaja menyumbat wastafel tempat cuci tangan dengan bungkus makanan', 'Punishment'),
(21, 'Siswa sengaja menendang pintu kelas hingga engselnya rusak dan patah', 'Punishment'),
(22, 'Murid kedapatan mencoret-coret dinding lorong sekolah menggunakan piloks', 'Punishment'),
(23, 'Siswa merusak saklar lampu di ruang laboratorium komputer saat tidak ada guru', 'Punishment'),
(24, 'Siswa mematahkan penggaris kayu panjang milik inventaris kelas dengan sengaja', 'Punishment'),
(25, 'Murid kedapatan melempari kaca jendela perpustakaan menggunakan batu hingga retak', 'Punishment'),
(26, 'Siswa merobek buku paket pelajaran yang dipinjam dari perpustakaan sekolah', 'Punishment'),
(27, 'Siswa sengaja merusak jaring net bola voli di lapangan saat jam olahraga', 'Punishment'),
(28, 'Murid mencopot tanaman hias dan merusak pot bunga di koridor depan kelas', 'Punishment'),
(29, 'Siswa kedapatan menggores cat mobil milik guru di area parkir menggunakan paku', 'Punishment'),
(30, 'Siswa merusak kran air di tempat wudhu mushola hingga patah and air meluap', 'Punishment'),
(31, 'Siswa terbukti membawa minuman beralkohol ke lingkungan sekolah', 'Punishment'),
(32, 'Siswa kedapatan menyimpan alkohol saat pemeriksaan sekolah', 'Punishment'),
(33, 'Siswa terbukti mengonsumsi minuman keras di area sekolah', 'Punishment'),
(34, 'Siswa terlibat penggunaan alkohol berdasarkan laporan guru', 'Punishment'),
(35, 'Siswa membawa barang terlarang berupa minuman keras', 'Punishment'),
(36, 'Siswa terbukti menggunakan zat terlarang sesuai hasil pemeriksaan', 'Punishment'),
(37, 'Siswa kedapatan membawa narkotika ke sekolah', 'Punishment'),
(38, 'Siswa terbukti memakai narkoba berdasarkan pembuktian sekolah', 'Punishment'),
(39, 'Siswa terlibat penyalahgunaan psikotropika di lingkungan sekolah', 'Punishment'),
(40, 'Siswa membawa obat terlarang tanpa izin resmi', 'Punishment'),
(41, 'Siswa terbukti menyimpan zat adiktif di tas sekolah', 'Punishment'),
(42, 'Siswa kedapatan menggunakan narkotika saat kegiatan sekolah', 'Punishment'),
(43, 'Siswa terbukti mengedarkan barang terlarang kepada siswa lain', 'Punishment'),
(44, 'Siswa terlibat penjualan minuman keras di lingkungan sekolah', 'Punishment'),
(45, 'Siswa menawarkan alkohol kepada peserta didik lain', 'Punishment'),
(46, 'Siswa terbukti menjadi perantara barang terlarang', 'Punishment'),
(47, 'Siswa mengajak teman menggunakan minuman keras', 'Punishment'),
(48, 'Siswa terlibat transaksi barang terlarang di sekolah', 'Punishment'),
(49, 'Siswa membawa psikotropika tanpa hak dan izin', 'Punishment'),
(50, 'Siswa terbukti menggunakan zat adiktif terlarang', 'Punishment'),
(51, 'Siswa kedapatan menyebarkan minuman keras kepada teman', 'Punishment'),
(52, 'Siswa terlibat kepemilikan narkotika berdasarkan laporan resmi', 'Punishment'),
(53, 'Siswa terbukti melakukan pelanggaran berat terkait alkohol', 'Punishment'),
(54, 'Siswa kedapatan memakai obat terlarang saat jam sekolah', 'Punishment'),
(55, 'Siswa menyimpan minuman keras di lingkungan sekolah', 'Punishment'),
(56, 'Siswa membawa barang terlarang saat kegiatan sekolah berlangsung', 'Punishment'),
(57, 'Siswa terbukti terlibat penyalahgunaan zat berbahaya', 'Punishment'),
(58, 'Siswa kedapatan membawa psikotropika saat pemeriksaan', 'Punishment'),
(59, 'Siswa terlibat penggunaan narkotika bersama teman sekolah', 'Punishment'),
(60, 'Siswa terbukti melanggar tata tertib terkait narkoba', 'Punishment'),
(61, 'Siswa menggunakan zat terlarang di area sekolah', 'Punishment'),
(62, 'Siswa membawa alkohol ketika mengikuti kegiatan sekolah', 'Punishment'),
(63, 'Siswa terlibat pelanggaran berat penggunaan minuman keras', 'Punishment'),
(64, 'Siswa terbukti menyimpan barang terlarang di loker sekolah', 'Punishment'),
(65, 'Siswa kedapatan membawa zat adiktif tanpa izin', 'Punishment'),
(66, 'Siswa terbukti mengonsumsi alkohol bersama siswa lain', 'Punishment'),
(67, 'Siswa mengedarkan barang terlarang kepada peserta didik', 'Punishment'),
(68, 'Siswa terbukti melakukan transaksi barang terlarang', 'Punishment'),
(69, 'Siswa terlibat penggunaan psikotropika di sekolah', 'Punishment'),
(70, 'Siswa membawa minuman keras saat kegiatan luar sekolah', 'Punishment'),
(71, 'Siswa kedapatan memiliki narkotika berdasarkan pemeriksaan', 'Punishment'),
(72, 'Siswa terbukti memakai zat berbahaya di lingkungan sekolah', 'Punishment'),
(73, 'Siswa terlibat penyebaran alkohol kepada teman sekolah', 'Punishment'),
(74, 'Siswa membawa obat terlarang tanpa keterangan medis', 'Punishment'),
(75, 'Siswa terbukti melanggar aturan sekolah terkait narkoba', 'Punishment'),
(76, 'Siswa kedapatan menyimpan alkohol di tas pribadi', 'Punishment'),
(77, 'Siswa menggunakan barang terlarang saat kegiatan sekolah', 'Punishment'),
(78, 'Siswa terbukti menjadi pengguna zat adiktif', 'Punishment'),
(79, 'Siswa mengajak teman menggunakan barang terlarang', 'Punishment'),
(80, 'Siswa terlibat pelanggaran berat terkait psikotropika', 'Punishment'),
(81, 'Siswa kedapatan bermain judi online di lingkungan sekolah', 'Punishment'),
(82, 'Siswa terbukti melakukan taruhan online saat jam pelajaran', 'Punishment'),
(83, 'Siswa menggunakan aplikasi judi online di area sekolah', 'Punishment'),
(84, 'Siswa mengakses situs perjudian menggunakan fasilitas sekolah', 'Punishment'),
(85, 'Siswa terlibat aktivitas perjudian online bersama teman', 'Punishment'),
(86, 'Siswa kedapatan melakukan transaksi judi online', 'Punishment'),
(87, 'Siswa terbukti mempromosikan perjudian online kepada siswa lain', 'Punishment'),
(88, 'Siswa menyebarkan tautan judi online di grup kelas', 'Punishment'),
(89, 'Siswa mengajak teman mengikuti permainan judi online', 'Punishment'),
(90, 'Siswa menggunakan perangkat sekolah untuk aktivitas perjudian', 'Punishment'),
(91, 'Siswa terbukti melakukan deposit pada aplikasi judi online', 'Punishment'),
(92, 'Siswa kedapatan bermain judi online saat kegiatan belajar', 'Punishment'),
(93, 'Siswa terlibat pelanggaran berat terkait perjudian online', 'Punishment'),
(94, 'Siswa terbukti menyimpan aplikasi judi online di perangkat sekolah', 'Punishment'),
(95, 'Siswa melakukan aktivitas taruhan online di lingkungan sekolah', 'Punishment'),
(96, 'Siswa kedapatan mengakses konten perjudian saat jam sekolah', 'Punishment'),
(97, 'Siswa terbukti melanggar aturan sekolah terkait judi online', 'Punishment'),
(98, 'Siswa mengajak peserta didik lain bermain judi online', 'Punishment'),
(99, 'Siswa terlibat penyebaran informasi perjudian online', 'Punishment'),
(100, 'Siswa kedapatan aktif dalam aktivitas perjudian digital', 'Punishment'),
(101, 'Siswa terbukti melakukan perjudian online berulang', 'Punishment'),
(102, 'Siswa membawa perangkat berisi aplikasi perjudian online', 'Punishment'),
(103, 'Siswa terlibat penggunaan akun judi online di sekolah', 'Punishment'),
(104, 'Siswa kedapatan bermain taruhan digital saat pembelajaran', 'Punishment'),
(105, 'Siswa terbukti melakukan aktivitas perjudian melalui internet sekolah', 'Punishment'),
(106, 'Siswa menyebarkan promosi perjudian kepada teman sekolah', 'Punishment'),
(107, 'Siswa melakukan pelanggaran berat berupa perjudian online', 'Punishment'),
(108, 'Siswa terbukti terlibat aktivitas judi digital', 'Punishment'),
(109, 'Siswa kedapatan menggunakan telepon genggam untuk perjudian online', 'Punishment'),
(110, 'Siswa terlibat taruhan online saat berada di kelas', 'Punishment'),
(111, 'Siswa menggunakan akses internet sekolah untuk judi online', 'Punishment'),
(112, 'Siswa terbukti mengajak siswa lain melakukan perjudian online', 'Punishment'),
(113, 'Siswa kedapatan menyimpan bukti transaksi perjudian online', 'Punishment'),
(114, 'Siswa terlibat penggunaan platform judi online', 'Punishment'),
(115, 'Siswa terbukti memainkan perjudian daring di sekolah', 'Punishment'),
(116, 'Siswa kedapatan membuka situs taruhan online saat pelajaran', 'Punishment'),
(117, 'Siswa melakukan pelanggaran tata tertib terkait perjudian digital', 'Punishment'),
(118, 'Siswa terbukti aktif dalam kegiatan judi online', 'Punishment'),
(119, 'Siswa mengakses aplikasi taruhan online di lingkungan sekolah', 'Punishment'),
(120, 'Siswa terlibat aktivitas perjudian melalui perangkat pribadi', 'Punishment'),
(121, 'Siswa kedapatan melakukan judi online bersama teman sekolah', 'Punishment'),
(122, 'Siswa terbukti melanggar disiplin sekolah terkait perjudian', 'Punishment'),
(123, 'Siswa menyimpan riwayat perjudian online pada perangkat sekolah', 'Punishment'),
(124, 'Siswa kedapatan melakukan aktivitas taruhan digital berulang', 'Punishment'),
(125, 'Siswa terbukti melakukan perjudian online di area sekolah', 'Punishment'),
(126, 'Siswa terlibat pelanggaran sangat berat sesuai tata tertib sekolah', 'Punishment'),
(127, 'Siswa melakukan tindakan terlarang dengan pembuktian resmi sekolah', 'Punishment'),
(128, 'Siswa terbukti melanggar aturan sekolah kategori pelanggaran berat sekali', 'Punishment'),
(129, 'Siswa kedapatan merokok di lingkungan sekolah', 'Punishment'),
(130, 'Siswa berkelahi dengan teman di area sekolah', 'Punishment'),
(131, 'Siswa membawa rokok ke dalam kelas', 'Punishment'),
(132, 'Siswa membolos saat jam pelajaran berlangsung', 'Punishment'),
(133, 'Siswa keluar sekolah tanpa izin guru', 'Punishment'),
(134, 'Siswa melakukan perundungan terhadap teman kelas', 'Punishment'),
(135, 'Siswa berkata kasar kepada guru', 'Punishment'),
(136, 'Siswa merusak fasilitas sekolah dengan sengaja', 'Punishment'),
(137, 'Siswa terlibat tawuran antar sekolah', 'Punishment'),
(138, 'Siswa membawa benda tajam ke sekolah', 'Punishment'),
(139, 'Siswa mengancam teman di lingkungan sekolah', 'Punishment'),
(140, 'Siswa mencontek secara berulang saat ujian', 'Punishment'),
(141, 'Siswa memalsukan tanda tangan orang tua', 'Punishment'),
(142, 'Siswa mengambil barang milik teman tanpa izin', 'Punishment'),
(143, 'Siswa menyebarkan video perkelahian di sekolah', 'Punishment'),
(144, 'Siswa melakukan vandalisme pada meja kelas', 'Punishment'),
(145, 'Siswa tidak masuk sekolah tanpa keterangan selama beberapa hari', 'Punishment'),
(146, 'Siswa bermain judi saat jam sekolah', 'Punishment'),
(147, 'Siswa membuat keributan besar di kelas', 'Punishment'),
(148, 'Siswa menyalakan petasan di lingkungan sekolah', 'Punishment'),
(149, 'Siswa menghina teman secara berlebihan', 'Punishment'),
(150, 'Siswa sengaja memicu perkelahian antar siswa', 'Punishment'),
(151, 'Siswa membawa minuman keras ke sekolah', 'Punishment'),
(152, 'Siswa menggunakan ponsel saat ujian berlangsung', 'Punishment'),
(153, 'Siswa melawan perintah guru di kelas', 'Punishment'),
(154, 'Siswa mencoret dinding sekolah dengan kata tidak pantas', 'Punishment'),
(155, 'Siswa mengintimidasi adik kelas di sekolah', 'Punishment'),
(156, 'Siswa melakukan pemalakan terhadap teman', 'Punishment'),
(157, 'Siswa merusak komputer laboratorium sekolah', 'Punishment'),
(158, 'Siswa kabur saat kegiatan upacara berlangsung', 'Punishment'),
(159, 'Siswa tidur berulang kali saat pelajaran berlangsung', 'Punishment'),
(160, 'Siswa membawa kartu permainan judi ke sekolah', 'Punishment'),
(161, 'Siswa menyebarkan berita bohong tentang guru', 'Punishment'),
(162, 'Siswa berteriak kasar kepada teman di kelas', 'Punishment'),
(163, 'Siswa memukul teman saat kegiatan belajar', 'Punishment'),
(164, 'Siswa sengaja menghilangkan barang milik sekolah', 'Punishment'),
(165, 'Siswa memprovokasi teman untuk bolos bersama', 'Punishment'),
(166, 'Siswa membuat kelompok tawuran antar kelas', 'Punishment'),
(167, 'Siswa merusak kursi kelas dengan sengaja', 'Punishment'),
(168, 'Siswa mengakses situs terlarang menggunakan komputer sekolah', 'Punishment'),
(169, 'Siswa memalsukan surat izin tidak masuk sekolah', 'Punishment'),
(170, 'Siswa membawa vape ke lingkungan sekolah', 'Punishment'),
(171, 'Siswa merekam guru tanpa izin saat pembelajaran', 'Punishment'),
(172, 'Siswa mengganggu kegiatan belajar secara terus menerus', 'Punishment'),
(173, 'Siswa melempar barang ke arah teman di kelas', 'Punishment'),
(174, 'Siswa tidak mengikuti ujian tanpa alasan jelas', 'Punishment'),
(175, 'Siswa melakukan bullying secara verbal kepada teman', 'Punishment'),
(176, 'Siswa sengaja mengunci teman di dalam kelas', 'Punishment'),
(177, 'Siswa membawa gambar tidak pantas ke sekolah', 'Punishment'),
(178, 'Siswa memaksa teman memberikan uang jajan', 'Punishment'),
(179, 'Siswa menyebarkan hinaan melalui media sosial sekolah', 'Punishment'),
(180, 'Siswa memanjat pagar sekolah untuk keluar saat jam pelajaran', 'Punishment'),
(181, 'Siswa bermain game saat guru menjelaskan materi', 'Punishment'),
(182, 'Siswa sengaja mematikan listrik ruang kelas', 'Punishment'),
(183, 'Siswa menolak hukuman yang diberikan guru', 'Punishment'),
(184, 'Siswa membuat kerusuhan saat kegiatan sekolah', 'Punishment'),
(185, 'Siswa memukul meja hingga mengganggu kelas', 'Punishment'),
(186, 'Siswa mengajak teman meninggalkan kelas tanpa izin', 'Punishment'),
(187, 'Siswa membakar kertas di dalam ruang kelas', 'Punishment'),
(188, 'Siswa merusak tanaman di lingkungan sekolah', 'Punishment'),
(189, 'Siswa mengganggu teman saat ujian berlangsung', 'Punishment'),
(190, 'Siswa menggunakan kata tidak sopan kepada pegawai sekolah', 'Punishment'),
(191, 'Siswa menyembunyikan barang milik teman kelas', 'Punishment'),
(192, 'Siswa membawa speaker dan memutar musik keras saat pelajaran', 'Punishment'),
(193, 'Siswa sengaja terlambat masuk kelas setiap hari', 'Punishment'),
(194, 'Siswa memancing keributan di lingkungan sekolah', 'Punishment'),
(195, 'Siswa melakukan tindakan tidak disiplin secara berulang', 'Punishment'),
(196, 'Siswa tidak memakai atribut sekolah setelah diperingatkan', 'Punishment'),
(197, 'Siswa menghapus data komputer laboratorium sekolah', 'Punishment'),
(198, 'Siswa memukul pintu kelas saat marah', 'Punishment'),
(199, 'Siswa menyebarkan foto teman tanpa izin', 'Punishment'),
(200, 'Siswa mengolok teman hingga menangis', 'Punishment'),
(201, 'Siswa melempar sampah ke arah teman', 'Punishment'),
(202, 'Siswa berlari keluar kelas saat pelajaran berlangsung', 'Punishment'),
(203, 'Siswa memanjat jendela kelas ketika guru mengajar', 'Punishment'),
(204, 'Siswa memotong antrean kantin dan membuat keributan', 'Punishment'),
(205, 'Siswa mengganggu kelas lain saat pembelajaran berlangsung', 'Punishment'),
(206, 'Siswa membawa korek api ke sekolah', 'Punishment'),
(207, 'Siswa sengaja menyembunyikan buku perpustakaan', 'Punishment'),
(208, 'Siswa membentak guru ketika ditegur', 'Punishment'),
(209, 'Siswa merusak proyektor kelas dengan sengaja', 'Punishment'),
(210, 'Siswa tidak mengikuti aturan sekolah secara berulang', 'Punishment'),
(211, 'Siswa membuat akun palsu untuk menghina teman', 'Punishment'),
(212, 'Siswa menghasut teman untuk melawan guru', 'Punishment'),
(213, 'Siswa membolos saat kegiatan praktik berlangsung', 'Punishment'),
(214, 'Siswa tertangkap merokok di toilet sekolah', 'Punishment'),
(215, 'Siswa menendang meja kelas ketika emosi', 'Punishment'),
(216, 'Siswa menyebarkan rumor negatif tentang teman', 'Punishment'),
(217, 'Siswa membawa senjata mainan menyerupai asli ke sekolah', 'Punishment'),
(218, 'Siswa mengganggu jalannya presentasi teman', 'Punishment'),
(219, 'Siswa sengaja membuat kelas menjadi kotor', 'Punishment'),
(220, 'Siswa mencuri uang teman di kelas', 'Punishment'),
(221, 'Siswa memukul kaca jendela sekolah', 'Punishment'),
(222, 'Siswa menginjak tanaman sekolah hingga rusak', 'Punishment'),
(223, 'Siswa menulis kata kasar pada papan kelas', 'Punishment'),
(224, 'Siswa kabur sebelum jam pelajaran selesai', 'Punishment'),
(225, 'Siswa bermain kartu saat pembelajaran berlangsung', 'Punishment'),
(226, 'Siswa tidur di belakang kelas saat guru menjelaskan', 'Punishment'),
(227, 'Siswa membuat video ejekan terhadap teman sekolah', 'Punishment'),
(228, 'Siswa menolak mengikuti kegiatan sekolah wajib', 'Punishment'),
(229, 'Siswa menyebarkan jawaban ujian kepada teman', 'Punishment'),
(230, 'Siswa melakukan pelanggaran tata tertib secara terus menerus', 'Punishment'),
(231, 'Siswa terlambat masuk kelas tanpa alasan jelas', 'Punishment'),
(232, 'Siswa tidak memakai atribut sekolah lengkap', 'Punishment'),
(233, 'Siswa keluar kelas tanpa izin guru', 'Punishment'),
(234, 'Siswa bermain handphone saat pembelajaran berlangsung', 'Punishment'),
(235, 'Siswa berbicara saat guru menjelaskan materi', 'Punishment'),
(236, 'Siswa tidur pada saat jam pelajaran', 'Punishment'),
(237, 'Siswa tidak mengerjakan tugas sekolah', 'Punishment'),
(238, 'Siswa membuat keributan di dalam kelas', 'Punishment'),
(239, 'Siswa bercanda berlebihan saat pembelajaran', 'Punishment'),
(240, 'Siswa tidak mengikuti upacara tanpa keterangan', 'Punishment'),
(241, 'Siswa membuang sampah sembarangan di lingkungan sekolah', 'Punishment'),
(242, 'Siswa tidak memakai dasi sesuai aturan sekolah', 'Punishment'),
(243, 'Siswa tidak memakai ikat pinggang sekolah', 'Punishment'),
(244, 'Siswa memakai sepatu tidak sesuai ketentuan', 'Punishment'),
(245, 'Siswa datang terlambat mengikuti upacara', 'Punishment'),
(246, 'Siswa tidak membawa perlengkapan belajar', 'Punishment'),
(247, 'Siswa mengganggu teman saat belajar', 'Punishment'),
(248, 'Siswa makan di dalam kelas saat pelajaran', 'Punishment'),
(249, 'Siswa tidak memperhatikan guru ketika pembelajaran', 'Punishment'),
(250, 'Siswa sering izin keluar kelas tanpa alasan penting', 'Punishment'),
(251, 'Siswa mencoret meja dan fasilitas sekolah', 'Punishment'),
(252, 'Siswa berbicara kasar kepada teman', 'Punishment'),
(253, 'Siswa bermain saat kegiatan belajar berlangsung', 'Punishment'),
(254, 'Siswa tidak mengikuti piket kelas', 'Punishment'),
(255, 'Siswa terlambat mengumpulkan tugas sekolah', 'Punishment'),
(256, 'Siswa bercanda saat kegiatan upacara berlangsung', 'Punishment'),
(257, 'Siswa menggunakan aksesoris tidak sesuai aturan', 'Punishment'),
(258, 'Siswa memakai rambut tidak rapi', 'Punishment'),
(259, 'Siswa tidak memakai seragam sesuai jadwal', 'Punishment'),
(260, 'Siswa bermain game saat jam pelajaran', 'Punishment'),
(261, 'Siswa tidak hadir tanpa konfirmasi wali kelas', 'Punishment'),
(262, 'Siswa ribut saat proses pembelajaran', 'Punishment'),
(263, 'Siswa berpindah kelas tanpa izin', 'Punishment'),
(264, 'Siswa tidak mengikuti kegiatan sekolah wajib', 'Punishment'),
(265, 'Siswa mengabaikan instruksi guru di kelas', 'Punishment'),
(266, 'Siswa menggunakan bahasa kurang sopan', 'Punishment'),
(267, 'Siswa duduk di kantin saat jam pelajaran', 'Punishment'),
(268, 'Siswa tidak menjaga kebersihan kelas', 'Punishment'),
(269, 'Siswa membuat gaduh di lingkungan sekolah', 'Punishment'),
(270, 'Siswa bercanda secara berlebihan di kelas', 'Punishment'),
(271, 'Siswa mengganggu konsentrasi teman belajar', 'Punishment'),
(272, 'Siswa tidak membawa buku pelajaran', 'Punishment'),
(273, 'Siswa menyontek tugas teman', 'Punishment'),
(274, 'Siswa meninggalkan kelas sebelum pelajaran selesai', 'Punishment'),
(275, 'Siswa tidak mengikuti senam pagi sekolah', 'Punishment'),
(276, 'Siswa memakai jaket saat pembelajaran berlangsung', 'Punishment'),
(277, 'Siswa tidak memakai kaos kaki sesuai aturan', 'Punishment'),
(278, 'Siswa terlambat kembali setelah jam istirahat', 'Punishment'),
(279, 'Siswa memainkan musik saat jam pelajaran', 'Punishment'),
(280, 'Siswa tidak mematuhi tata tertib kelas', 'Punishment'),
(281, 'Siswa berbicara keras saat guru mengajar', 'Punishment'),
(282, 'Siswa duduk santai saat upacara berlangsung', 'Punishment'),
(283, 'Siswa bercanda ketika guru memberikan tugas', 'Punishment'),
(284, 'Siswa tidak mengikuti doa bersama sekolah', 'Punishment'),
(285, 'Siswa menyembunyikan barang milik teman', 'Punishment'),
(286, 'Siswa mengotori dinding kelas', 'Punishment'),
(287, 'Siswa tidak mengikuti instruksi piket harian', 'Punishment'),
(288, 'Siswa mengganggu kelas lain saat pembelajaran', 'Punishment'),
(289, 'Siswa berlari di koridor sekolah saat jam belajar', 'Punishment'),
(290, 'Siswa membawa makanan ke laboratorium', 'Punishment'),
(291, 'Siswa membuat suara gaduh di kelas', 'Punishment'),
(292, 'Siswa tidak memakai name tag sekolah', 'Punishment'),
(293, 'Siswa tidak menjaga kerapihan kelas', 'Punishment'),
(294, 'Siswa menggunakan kata tidak pantas kepada teman', 'Punishment'),
(295, 'Siswa bermain bola di dalam kelas', 'Punishment'),
(296, 'Siswa menunda tugas yang diberikan guru', 'Punishment'),
(297, 'Siswa tidak mengikuti apel pagi sekolah', 'Punishment'),
(298, 'Siswa mengganggu teman yang sedang presentasi', 'Punishment'),
(299, 'Siswa terlambat masuk setelah pergantian jam pelajaran', 'Punishment'),
(300, 'Siswa berbicara sendiri saat guru menjelaskan', 'Punishment'),
(301, 'Siswa memakai sandal di lingkungan sekolah', 'Punishment'),
(302, 'Siswa mengabaikan tugas piket kelas', 'Punishment'),
(303, 'Siswa bercanda saat kegiatan ibadah sekolah', 'Punishment'),
(304, 'Siswa meminjam barang teman tanpa izin', 'Punishment'),
(305, 'Siswa tidak mematuhi aturan laboratorium', 'Punishment'),
(306, 'Siswa keluar area sekolah tanpa izin', 'Punishment'),
(307, 'Siswa duduk di meja kelas saat pembelajaran', 'Punishment'),
(308, 'Siswa memainkan alat musik saat jam belajar', 'Punishment'),
(309, 'Siswa tidak mengikuti kegiatan literasi sekolah', 'Punishment'),
(310, 'Siswa membuat keramaian di perpustakaan', 'Punishment'),
(311, 'Siswa membawa kartu pelajar milik teman', 'Punishment'),
(312, 'Siswa tidak mematuhi arahan pengurus kelas', 'Punishment'),
(313, 'Siswa tidur di perpustakaan saat jam pelajaran', 'Punishment'),
(314, 'Siswa tidak menjaga ketertiban kelas', 'Punishment'),
(315, 'Siswa makan saat guru menjelaskan pelajaran', 'Punishment'),
(316, 'Siswa sering berpindah tempat duduk tanpa izin', 'Punishment'),
(317, 'Siswa terlambat mengikuti kegiatan ekstrakurikuler', 'Punishment'),
(318, 'Siswa bermain air di lingkungan sekolah', 'Punishment'),
(319, 'Siswa tidak menghormati teman saat berbicara', 'Punishment'),
(320, 'Siswa mengganggu jalannya diskusi kelas', 'Punishment'),
(321, 'Siswa tidak memakai pakaian olahraga sesuai aturan', 'Punishment'),
(322, 'Siswa duduk di lantai koridor sekolah', 'Punishment'),
(323, 'Siswa mengobrol saat ujian berlangsung', 'Punishment'),
(324, 'Siswa tidak menjaga fasilitas sekolah dengan baik', 'Punishment'),
(325, 'Siswa bermain kartu saat jam istirahat di kelas', 'Punishment'),
(326, 'Siswa menyembunyikan perlengkapan belajar teman', 'Punishment'),
(327, 'Siswa tidak mengikuti kegiatan kebersihan kelas', 'Punishment'),
(328, 'Siswa berbicara saat pengarahan sekolah berlangsung', 'Punishment'),
(329, 'Siswa memakai pakaian tidak sesuai tata tertib', 'Punishment'),
(330, 'Siswa meninggalkan sampah di bawah meja kelas', 'Punishment'),
(331, 'Siswa membuat candaan berlebihan saat pembelajaran', 'Punishment'),
(332, 'Siswa tidak disiplin saat antre di kantin', 'Punishment'),
(333, 'siswa terlambat masuk kelas', 'Punishment'),
(334, 'siswa datang terlambat saat upacara', 'Punishment'),
(335, 'siswa tidak memakai atribut lengkap', 'Punishment'),
(336, 'siswa tidak menggunakan dasi sekolah', 'Punishment'),
(337, 'siswa tidak memakai ikat pinggang', 'Punishment'),
(338, 'siswa memakai sepatu tidak sesuai aturan', 'Punishment'),
(339, 'siswa rambut tidak rapi', 'Punishment'),
(340, 'siswa berbicara saat guru menjelaskan', 'Punishment'),
(341, 'siswa bercanda saat pembelajaran berlangsung', 'Punishment'),
(342, 'siswa tidak membawa buku pelajaran', 'Punishment'),
(343, 'siswa tidak mengerjakan tugas rumah', 'Punishment'),
(344, 'siswa tidur di dalam kelas', 'Punishment'),
(345, 'siswa bermain handphone saat pelajaran', 'Punishment'),
(346, 'siswa makan di dalam kelas', 'Punishment'),
(347, 'siswa keluar kelas tanpa izin', 'Punishment'),
(348, 'siswa duduk berpindah pindah saat pelajaran', 'Punishment'),
(349, 'siswa ribut di dalam kelas', 'Punishment'),
(350, 'siswa tidak memperhatikan guru', 'Punishment'),
(351, 'siswa terlambat mengumpulkan tugas', 'Punishment'),
(352, 'siswa memakai seragam tidak rapi', 'Punishment'),
(353, 'siswa tidak memasukkan baju', 'Punishment'),
(354, 'siswa memakai jaket saat pembelajaran', 'Punishment'),
(355, 'siswa berbicara kasar kepada teman', 'Punishment'),
(356, 'siswa mengganggu teman saat belajar', 'Punishment'),
(357, 'siswa membuang sampah sembarangan', 'Punishment'),
(358, 'siswa mencoret meja kelas', 'Punishment'),
(359, 'siswa tidak mengikuti piket kelas', 'Punishment'),
(360, 'siswa bermain saat jam pelajaran', 'Punishment'),
(361, 'siswa bercermin saat pembelajaran', 'Punishment'),
(362, 'siswa memakai aksesoris berlebihan', 'Punishment'),
(363, 'siswa tidak memakai kaos kaki', 'Punishment'),
(364, 'siswa memakai sandal ke sekolah', 'Punishment'),
(365, 'siswa membawa makanan ke laboratorium', 'Punishment'),
(366, 'siswa tertawa berlebihan di kelas', 'Punishment'),
(367, 'siswa menyanyi saat guru menjelaskan', 'Punishment'),
(368, 'siswa tidak membawa alat praktik', 'Punishment'),
(369, 'siswa datang terlambat setelah istirahat', 'Punishment'),
(370, 'siswa bermain game di kelas', 'Punishment'),
(371, 'siswa tidak menjaga kebersihan kelas', 'Punishment'),
(372, 'siswa duduk di atas meja', 'Punishment'),
(373, 'siswa berbicara sendiri saat pelajaran', 'Punishment'),
(374, 'siswa izin terlalu lama kembali ke kelas', 'Punishment'),
(375, 'siswa memakai rambut panjang tidak sesuai aturan', 'Punishment'),
(376, 'siswa tidak memakai name tag', 'Punishment'),
(377, 'siswa memakai seragam olahraga di luar jadwal', 'Punishment'),
(378, 'siswa mencontek pekerjaan teman', 'Punishment'),
(379, 'siswa tidak mengikuti apel pagi', ''),
(380, 'siswa membuat keributan saat pergantian jam', 'Punishment'),
(381, 'siswa terlambat masuk setelah bel berbunyi', 'Punishment'),
(382, 'siswa tidak membawa perlengkapan sekolah', 'Punishment'),
(383, 'siswa mengobrol saat ujian berlangsung', 'Punishment'),
(384, 'siswa tidak merapikan kursi setelah belajar', 'Punishment'),
(385, 'siswa bermain lempar kertas di kelas', 'Punishment'),
(386, 'siswa meminjam barang tanpa izin', 'Punishment'),
(387, 'siswa menyalakan musik saat pelajaran', 'Punishment'),
(388, 'siswa tidak hadir piket tanpa alasan', 'Punishment'),
(389, 'siswa membuat suara gaduh di kelas', 'Punishment'),
(390, 'siswa menggunakan bahasa tidak sopan', 'Punishment'),
(391, 'siswa memakai topi di dalam kelas', 'Punishment'),
(392, 'siswa berjalan jalan saat pelajaran', 'Punishment'),
(393, 'siswa tidak mengikuti instruksi guru', 'Punishment'),
(394, 'siswa terlambat mengikuti kegiatan sekolah', 'Punishment'),
(395, 'siswa bermain di koridor saat jam belajar', 'Punishment'),
(396, 'siswa tidak menyimpan sepatu pada tempatnya', 'Punishment'),
(397, 'siswa membawa kartu permainan ke sekolah', 'Punishment'),
(398, 'siswa tidak menjaga kerapihan kelas', 'Punishment'),
(399, 'siswa membuka laptop untuk bermain game', 'Punishment'),
(400, 'siswa tidak fokus saat pembelajaran', 'Punishment'),
(401, 'siswa mencorat coret buku teman', 'Punishment'),
(402, 'siswa mengganggu kelompok lain saat diskusi', 'Punishment'),
(403, 'siswa bercanda berlebihan saat praktik', 'Punishment'),
(404, 'siswa tidak memakai pakaian praktik lengkap', 'Punishment'),
(405, 'siswa menggunakan earphone saat guru menjelaskan', 'Punishment'),
(406, 'siswa menunda pengumpulan tugas', 'Punishment'),
(407, 'siswa mengabaikan teguran guru', 'Punishment'),
(408, 'siswa membawa mainan ke kelas', 'Punishment'),
(409, 'siswa bermain bola di dalam kelas', 'Punishment'),
(410, 'siswa berdiri saat pelajaran berlangsung tanpa izin', 'Punishment'),
(411, 'siswa tidak membawa kartu pelajar', 'Punishment'),
(412, 'siswa sering izin keluar kelas', 'Punishment'),
(413, 'siswa mengganggu konsentrasi teman', 'Punishment'),
(414, 'siswa tidak membersihkan alat praktik', 'Punishment'),
(415, 'siswa berbicara keras di perpustakaan', 'Punishment'),
(416, 'siswa tidak mengikuti aturan antre', 'Punishment'),
(417, 'siswa membuat kegaduhan saat istirahat', 'Punishment'),
(418, 'siswa memotong pembicaraan guru', 'Punishment'),
(419, 'siswa tidak disiplin waktu masuk kelas', 'Punishment'),
(420, 'siswa memakai pakaian tidak sesuai ketentuan', 'Punishment'),
(421, 'siswa meninggalkan kelas sebelum pelajaran selesai', 'Punishment'),
(422, 'siswa mengobrol saat presentasi teman', 'Punishment'),
(423, 'siswa tidak mengikuti tata tertib kelas', 'Punishment'),
(424, 'siswa membawa minuman ke laboratorium', 'Punishment'),
(425, 'siswa bermain media sosial saat belajar', 'Punishment'),
(426, 'siswa tidak membawa catatan pelajaran', 'Punishment'),
(427, 'siswa duduk santai saat upacara', 'Punishment'),
(428, 'siswa tidak memperhatikan saat briefing', 'Punishment'),
(429, 'siswa mengotori lingkungan sekolah', 'Punishment'),
(430, 'siswa terlambat mengikuti kegiatan ekstrakurikuler', 'Punishment'),
(431, 'siswa membuat candaan saat pembelajaran serius', 'Punishment'),
(432, 'siswa tidak menjaga ketertiban kelas', 'Punishment'),
(433, 'siswa bermain saat guru meninggalkan kelas', 'Punishment'),
(434, 'siswa tidak menaati aturan sekolah', 'Punishment'),
(435, 'Siswa selalu mengerjakan tugas tepat waktu', 'Reward'),
(436, 'Siswa rajin mengikuti pembelajaran setiap hari', 'Reward'),
(437, 'Siswa rutin hadir tanpa terlambat', 'Reward'),
(438, 'Siswa selalu menyelesaikan pekerjaan rumah', 'Reward'),
(439, 'Siswa rajin membaca materi pelajaran', 'Reward'),
(440, 'Siswa aktif belajar mandiri di kelas', 'Reward'),
(441, 'Siswa konsisten mengumpulkan tugas harian', 'Reward'),
(442, 'Siswa tidak pernah menunda pekerjaan sekolah', 'Reward'),
(443, 'Siswa rajin mengikuti kegiatan belajar tambahan', 'Reward'),
(444, 'Siswa disiplin dalam menyelesaikan latihan', 'Reward'),
(445, 'Siswa selalu hadir dalam kegiatan sekolah', 'Reward'),
(446, 'Siswa rajin mencatat materi pelajaran', 'Reward'),
(447, 'Siswa tekun belajar saat jam pelajaran', 'Reward'),
(448, 'Siswa selalu membawa perlengkapan belajar lengkap', 'Reward'),
(449, 'Siswa rajin mengikuti praktik di laboratorium', 'Reward'),
(450, 'Siswa berusaha memahami materi dengan baik', 'Reward'),
(451, 'Siswa selalu fokus saat pembelajaran berlangsung', 'Reward'),
(452, 'Siswa rutin mengikuti kegiatan literasi sekolah', 'Reward'),
(453, 'Siswa rajin berdiskusi dengan teman kelompok', 'Reward'),
(454, 'Siswa selalu mempersiapkan diri sebelum ujian', 'Reward'),
(455, 'Siswa disiplin mengatur jadwal belajar', 'Reward'),
(456, 'Siswa rutin mengikuti bimbingan belajar', 'Reward'),
(457, 'Siswa aktif menyelesaikan latihan soal', 'Reward'),
(458, 'Siswa rajin datang lebih awal ke kelas', 'Reward'),
(459, 'Siswa selalu memperhatikan penjelasan guru', 'Reward'),
(460, 'Siswa tidak pernah absen tanpa keterangan', 'Reward'),
(461, 'Siswa rajin mengulang materi di rumah', 'Reward'),
(462, 'Siswa konsisten menjaga semangat belajar', 'Reward'),
(463, 'Siswa aktif mencari referensi tambahan pelajaran', 'Reward'),
(464, 'Siswa rajin mengikuti kegiatan akademik', 'Reward'),
(465, 'Siswa selalu menyimpan catatan pelajaran dengan rapi', 'Reward'),
(466, 'Siswa rutin mempelajari materi sebelum pelajaran dimulai', 'Reward'),
(467, 'Siswa rajin membantu teman memahami pelajaran', 'Reward'),
(468, 'Siswa selalu mengerjakan latihan yang diberikan guru', 'Reward'),
(469, 'Siswa disiplin mengikuti jadwal piket kelas', 'Reward'),
(470, 'Siswa rajin mengikuti evaluasi pembelajaran', 'Reward'),
(471, 'Siswa selalu menyelesaikan tugas kelompok', 'Reward'),
(472, 'Siswa rutin hadir dalam kegiatan ekstrakurikuler', 'Reward'),
(473, 'Siswa rajin belajar meskipun tidak ada ujian', 'Reward'),
(474, 'Siswa selalu menunjukkan tanggung jawab belajar', 'Reward'),
(475, 'Siswa disiplin menjaga kehadiran sekolah', 'Reward'),
(476, 'Siswa rajin mengikuti arahan guru', 'Reward'),
(477, 'Siswa aktif bertanya ketika belum memahami materi', 'Reward'),
(478, 'Siswa selalu memanfaatkan waktu belajar dengan baik', 'Reward'),
(479, 'Siswa rajin mengikuti kegiatan praktik sekolah', 'Reward'),
(480, 'Siswa rutin menyelesaikan laporan praktik', 'Reward'),
(481, 'Siswa disiplin membawa buku pelajaran', 'Reward'),
(482, 'Siswa rajin memperbaiki nilai pelajaran', 'Reward'),
(483, 'Siswa selalu berusaha meningkatkan prestasi belajar', 'Reward'),
(484, 'Siswa tekun mengikuti seluruh kegiatan pembelajaran', 'Reward'),
(485, 'Siswa konsisten menjaga kedisiplinan belajar', 'Reward'),
(486, 'Siswa rajin mengerjakan soal latihan tambahan', 'Reward'),
(487, 'Siswa selalu siap mengikuti pelajaran', 'Reward'),
(488, 'Siswa aktif dalam kegiatan belajar kelompok', 'Reward'),
(489, 'Siswa rutin belajar bersama teman', 'Reward'),
(490, 'Siswa rajin mencari materi tambahan di perpustakaan', 'Reward'),
(491, 'Siswa disiplin mengumpulkan laporan tepat waktu', 'Reward'),
(492, 'Siswa selalu hadir tepat waktu di kelas', 'Reward'),
(493, 'Siswa rajin mengikuti pengarahan sekolah', 'Reward'),
(494, 'Siswa konsisten menyelesaikan tugas mingguan', 'Reward'),
(495, 'Siswa tekun mempelajari materi praktik', 'Reward'),
(496, 'Siswa rajin mengikuti simulasi ujian', 'Reward'),
(497, 'Siswa selalu serius saat pembelajaran berlangsung', 'Reward'),
(498, 'Siswa aktif mengikuti kegiatan pendidikan karakter', 'Reward'),
(499, 'Siswa rajin menjaga kebersihan area belajar', 'Reward'),
(500, 'Siswa disiplin menaati aturan kelas', 'Reward'),
(501, 'Siswa rutin mengikuti kegiatan pembiasaan pagi', 'Reward'),
(502, 'Siswa rajin mempelajari materi sebelumnya', 'Reward'),
(503, 'Siswa selalu siap saat presentasi kelas', 'Reward'),
(504, 'Siswa aktif menyumbangkan ide dalam diskusi', 'Reward'),
(505, 'Siswa disiplin menyusun jadwal belajar pribadi', 'Reward'),
(506, 'Siswa rajin menghadiri kegiatan pembinaan sekolah', 'Reward'),
(507, 'Siswa selalu berusaha memahami tugas dengan baik', 'Reward'),
(508, 'Siswa rutin mengulang materi setelah pembelajaran', 'Reward'),
(509, 'Siswa rajin bertanya kepada guru mengenai pelajaran', 'Reward'),
(510, 'Siswa selalu mengerjakan tugas tanpa disuruh', 'Reward'),
(511, 'Siswa aktif mengikuti pembelajaran daring maupun luring', 'Reward'),
(512, 'Siswa disiplin dalam penggunaan waktu belajar', 'Reward'),
(513, 'Siswa rajin mengikuti kegiatan sekolah setiap minggu', 'Reward'),
(514, 'Siswa tekun menyelesaikan proyek pembelajaran', 'Reward'),
(515, 'Siswa konsisten menjaga motivasi belajar', 'Reward'),
(516, 'Siswa rajin mengikuti kegiatan praktik industri', 'Reward'),
(517, 'Siswa disiplin menyelesaikan target pembelajaran', 'Reward'),
(518, 'Siswa rutin mempersiapkan alat praktik', 'Reward'),
(519, 'Siswa aktif mengikuti seminar pendidikan sekolah', 'Reward'),
(520, 'Siswa rajin membantu kegiatan akademik kelas', 'Reward'),
(521, 'Siswa disiplin mengikuti tata tertib sekolah', 'Reward'),
(522, 'Siswa selalu bersemangat mengikuti pembelajaran', 'Reward'),
(523, 'Siswa rajin memperhatikan instruksi guru', 'Reward'),
(524, 'Siswa aktif mempelajari teknologi pembelajaran baru', 'Reward'),
(525, 'Siswa rutin membuat rangkuman pelajaran', 'Reward'),
(526, 'Siswa disiplin menjaga ketertiban kelas', 'Reward'),
(527, 'Siswa rajin menyelesaikan revisi tugas', 'Reward'),
(528, 'Siswa aktif mengikuti lomba akademik sekolah', 'Reward'),
(529, 'Siswa tekun belajar untuk meningkatkan nilai', 'Reward'),
(530, 'Siswa selalu menunjukkan sikap rajin belajar', 'Reward'),
(531, 'Siswa rutin melatih kemampuan akademik', 'Reward'),
(532, 'Siswa disiplin mengikuti seluruh mata pelajaran', 'Reward'),
(533, 'Siswa rajin mengembangkan kemampuan diri', 'Reward'),
(534, 'Siswa aktif mengikuti kegiatan edukatif sekolah', 'Reward'),
(535, 'Siswa selalu menjaga konsistensi belajar', 'Reward'),
(536, 'Siswa rajin berlatih soal setiap hari', 'Reward'),
(537, 'Siswa selalu memakai seragam lengkap dan rapi', 'Reward'),
(538, 'Siswa menjaga kerapihan pakaian sekolah setiap hari', 'Reward'),
(539, 'Siswa tampil rapi saat mengikuti pembelajaran', 'Reward'),
(540, 'Siswa menggunakan atribut sekolah dengan lengkap', 'Reward'),
(541, 'Siswa menjaga kebersihan dan kerapihan diri', 'Reward'),
(542, 'Siswa memakai sepatu sesuai aturan sekolah', 'Reward'),
(543, 'Siswa selalu berpakaian sopan dan rapi', 'Reward'),
(544, 'Siswa hadir dengan penampilan bersih dan rapi', 'Reward'),
(545, 'Siswa menjaga rambut tetap rapi sesuai aturan', 'Reward'),
(546, 'Siswa disiplin dalam menggunakan seragam sekolah', 'Reward'),
(547, 'Siswa konsisten memakai atribut lengkap', 'Reward'),
(548, 'Siswa terlihat rapi saat upacara sekolah', 'Reward'),
(549, 'Siswa menjaga penampilan dengan baik di sekolah', 'Reward'),
(550, 'Siswa selalu memakai pakaian sesuai tata tertib', 'Reward'),
(551, 'Siswa memperhatikan kerapihan saat berada di kelas', 'Reward'),
(552, 'Siswa menjaga kebersihan seragam setiap hari', 'Reward'),
(553, 'Siswa berpakaian rapi dan sopan di lingkungan sekolah', 'Reward'),
(554, 'Siswa tidak pernah melanggar aturan berpakaian', 'Reward'),
(555, 'Siswa tampil bersih dan teratur setiap hari', 'Reward'),
(556, 'Siswa menjaga kerapihan rambut dan seragam', 'Reward'),
(557, 'Siswa menggunakan dasi dan atribut lengkap', 'Reward'),
(558, 'Siswa selalu merapikan pakaian sebelum masuk kelas', 'Reward'),
(559, 'Siswa menjaga sepatu tetap bersih dan rapi', 'Reward'),
(560, 'Siswa disiplin menjaga penampilan sekolah', 'Reward'),
(561, 'Siswa memakai seragam sesuai ketentuan sekolah', 'Reward'),
(562, 'Siswa menjaga kerapihan saat kegiatan sekolah', 'Reward'),
(563, 'Siswa selalu tampil sopan di lingkungan sekolah', 'Reward'),
(564, 'Siswa menjaga kebersihan diri dengan baik', 'Reward'),
(565, 'Siswa konsisten menjaga atribut sekolah lengkap', 'Reward'),
(566, 'Siswa berpenampilan rapi saat kegiatan belajar', 'Reward'),
(567, 'Siswa selalu menjaga tata tertib berpakaian', 'Reward'),
(568, 'Siswa tampil bersih dan sopan setiap hari', 'Reward'),
(569, 'Siswa menjaga pakaian tetap bersih di sekolah', 'Reward'),
(570, 'Siswa selalu memperhatikan penampilan diri', 'Reward'),
(571, 'Siswa berpakaian sesuai aturan sekolah', 'Reward'),
(572, 'Siswa hadir dengan atribut yang lengkap', 'Reward'),
(573, 'Siswa menjaga rambut tetap pendek dan rapi', 'Reward'),
(574, 'Siswa disiplin menggunakan seragam harian', 'Reward'),
(575, 'Siswa tampil rapi saat kegiatan upacara', 'Reward'),
(576, 'Siswa menjaga kerapihan selama kegiatan belajar', 'Reward'),
(577, 'Siswa selalu memakai ikat pinggang sekolah', 'Reward'),
(578, 'Siswa memakai pakaian yang bersih dan rapi', 'Reward'),
(579, 'Siswa selalu tampil tertib dan sopan', 'Reward'),
(580, 'Siswa menjaga penampilan dengan disiplin', 'Reward'),
(581, 'Siswa memperhatikan kebersihan dan kerapihan pakaian', 'Reward'),
(582, 'Siswa memakai seragam dengan benar', 'Reward'),
(583, 'Siswa tidak pernah lupa memakai atribut sekolah', 'Reward'),
(584, 'Siswa menjaga kebersihan sepatu dan pakaian', 'Reward'),
(585, 'Siswa konsisten menjaga kerapihan di sekolah', 'Reward'),
(586, 'Siswa berpakaian sopan selama pembelajaran', 'Reward'),
(587, 'Siswa selalu menjaga rambut sesuai tata tertib', 'Reward'),
(588, 'Siswa tampil rapi dan percaya diri di sekolah', 'Reward'),
(589, 'Siswa menggunakan seragam lengkap saat upacara', 'Reward'),
(590, 'Siswa disiplin menjaga tata tertib berpakaian', 'Reward'),
(591, 'Siswa menjaga penampilan selama berada di sekolah', 'Reward'),
(592, 'Siswa selalu memakai atribut sesuai aturan', 'Reward'),
(593, 'Siswa menjaga kebersihan pakaian sekolah', 'Reward'),
(594, 'Siswa berpenampilan sopan dan teratur', 'Reward'),
(595, 'Siswa selalu hadir dengan pakaian rapi', 'Reward'),
(596, 'Siswa menjaga kerapihan selama kegiatan praktik', 'Reward'),
(597, 'Siswa disiplin menjaga kebersihan diri', 'Reward'),
(598, 'Siswa selalu memakai seragam dengan lengkap', 'Reward'),
(599, 'Siswa menjaga penampilan agar tetap rapi', 'Reward'),
(600, 'Siswa memperhatikan tata tertib berpakaian sekolah', 'Reward'),
(601, 'Siswa tampil bersih selama kegiatan belajar', 'Reward'),
(602, 'Siswa menjaga atribut sekolah tetap lengkap', 'Reward'),
(603, 'Siswa memakai pakaian sesuai ketentuan harian', 'Reward'),
(604, 'Siswa konsisten tampil rapi setiap hari', 'Reward'),
(605, 'Siswa menjaga kebersihan dan penampilan diri', 'Reward'),
(606, 'Siswa selalu tertib dalam berpakaian', 'Reward'),
(607, 'Siswa menjaga seragam tetap bersih dan sopan', 'Reward'),
(608, 'Siswa memakai atribut lengkap saat pembelajaran', 'Reward'),
(609, 'Siswa selalu tampil rapi dalam kegiatan sekolah', 'Reward'),
(610, 'Siswa menjaga kebersihan rambut dan pakaian', 'Reward'),
(611, 'Siswa disiplin dalam menjaga penampilan', 'Reward'),
(612, 'Siswa selalu berpakaian sesuai aturan', 'Reward'),
(613, 'Siswa menjaga kerapihan saat berada di lingkungan sekolah', 'Reward'),
(614, 'Siswa memakai seragam sekolah dengan baik', 'Reward'),
(615, 'Siswa tampil sopan dan rapi setiap hari', 'Reward'),
(616, 'Siswa selalu menjaga kebersihan sepatu sekolah', 'Reward'),
(617, 'Siswa menggunakan atribut sekolah secara lengkap', 'Reward'),
(618, 'Siswa menjaga tata tertib berpakaian dengan disiplin', 'Reward'),
(619, 'Siswa tampil rapi saat mengikuti kegiatan sekolah', 'Reward'),
(620, 'Siswa memperhatikan kebersihan diri dan pakaian', 'Reward'),
(621, 'Siswa disiplin menjaga seragam sekolah', 'Reward'),
(622, 'Siswa selalu hadir dengan penampilan rapi', 'Reward'),
(623, 'Siswa menjaga pakaian tetap sopan dan bersih', 'Reward'),
(624, 'Siswa menggunakan seragam sesuai tata tertib', 'Reward'),
(625, 'Siswa selalu menjaga kerapihan diri di sekolah', 'Reward'),
(626, 'Siswa tampil bersih dan tertib saat pembelajaran', 'Reward'),
(627, 'Siswa menjaga atribut tetap lengkap setiap hari', 'Reward'),
(628, 'Siswa selalu berpakaian rapi di lingkungan sekolah', 'Reward'),
(629, 'Siswa disiplin menjaga kebersihan dan penampilan', 'Reward'),
(630, 'Siswa menjaga rambut dan pakaian tetap rapi', 'Reward'),
(631, 'Siswa selalu memakai seragam dengan tertib', 'Reward'),
(632, 'Siswa tampil sopan selama kegiatan belajar', 'Reward'),
(633, 'Siswa menjaga penampilan sesuai aturan sekolah', 'Reward'),
(634, 'Siswa selalu memperhatikan kebersihan pakaian', 'Reward'),
(635, 'Siswa menjaga kerapihan dan kesopanan berpakaian', 'Reward'),
(636, 'Siswa tampil rapi dan disiplin setiap hari', 'Reward'),
(637, 'Siswa memperoleh nilai tertinggi pada mata pelajaran produktif', 'Reward'),
(638, 'Siswa mendapatkan hasil ujian dengan nilai sangat baik', 'Reward'),
(639, 'Siswa menunjukkan prestasi akademik yang konsisten', 'Reward'),
(640, 'Siswa memperoleh rata rata nilai di atas standar sekolah', 'Reward'),
(641, 'Siswa berhasil meraih peringkat terbaik di kelas', 'Reward'),
(642, 'Siswa mendapatkan nilai sempurna pada tugas harian', 'Reward'),
(643, 'Siswa memiliki kemampuan akademik yang sangat baik', 'Reward'),
(644, 'Siswa menunjukkan peningkatan nilai yang signifikan', 'Reward'),
(645, 'Siswa aktif belajar dan memperoleh hasil memuaskan', 'Reward'),
(646, 'Siswa mendapatkan nilai tinggi pada ujian semester', 'Reward'),
(647, 'Siswa berhasil menyelesaikan tugas dengan sangat baik', 'Reward'),
(648, 'Siswa memperoleh penghargaan akademik dari sekolah', 'Reward'),
(649, 'Siswa konsisten memperoleh nilai di atas rata rata', 'Reward'),
(650, 'Siswa menunjukkan kemampuan memahami materi dengan baik', 'Reward'),
(651, 'Siswa berhasil mencapai target akademik sekolah', 'Reward'),
(652, 'Siswa memperoleh nilai praktik terbaik di kelas', 'Reward'),
(653, 'Siswa memiliki prestasi belajar yang membanggakan', 'Reward'),
(654, 'Siswa mendapatkan hasil belajar yang sangat memuaskan', 'Reward'),
(655, 'Siswa berhasil memperoleh juara akademik kelas', 'Reward'),
(656, 'Siswa menunjukkan semangat belajar yang tinggi', 'Reward'),
(657, 'Siswa mampu menyelesaikan soal dengan tepat', 'Reward'),
(658, 'Siswa memperoleh nilai terbaik pada ujian harian', 'Reward'),
(659, 'Siswa menunjukkan kemampuan analisis yang baik', 'Reward'),
(660, 'Siswa aktif mengikuti kegiatan akademik sekolah', 'Reward'),
(661, 'Siswa memperoleh hasil evaluasi yang sangat baik', 'Reward'),
(662, 'Siswa mendapatkan prestasi akademik yang membanggakan', 'Reward'),
(663, 'Siswa berhasil mempertahankan nilai tinggi setiap semester', 'Reward'),
(664, 'Siswa menunjukkan disiplin dalam kegiatan belajar', 'Reward'),
(665, 'Siswa memperoleh hasil praktik yang sangat baik', 'Reward'),
(666, 'Siswa mampu memahami materi pembelajaran dengan cepat', 'Reward'),
(667, 'Siswa mendapatkan nilai tinggi dalam semua mata pelajaran', 'Reward'),
(668, 'Siswa menunjukkan ketekunan dalam belajar', 'Reward'),
(669, 'Siswa berhasil menyelesaikan proyek akademik dengan baik', 'Reward'),
(670, 'Siswa memperoleh nilai terbaik pada mata pelajaran kejuruan', 'Reward'),
(671, 'Siswa aktif mengikuti lomba akademik sekolah', 'Reward'),
(672, 'Siswa menunjukkan kemampuan berpikir kritis yang baik', 'Reward'),
(673, 'Siswa memperoleh penghargaan sebagai Siswa berprestasi', 'Reward'),
(674, 'Siswa berhasil mencapai nilai di atas KKM', 'Reward'),
(675, 'Siswa menunjukkan konsistensi dalam prestasi belajar', 'Reward'),
(676, 'Siswa memiliki kemampuan akademik yang unggul', 'Reward'),
(677, 'Siswa mendapatkan hasil belajar terbaik di kelas', 'Reward'),
(678, 'Siswa memperoleh nilai sangat baik pada ujian praktik', 'Reward'),
(679, 'Siswa berhasil meraih prestasi akademik sekolah', 'Reward'),
(680, 'Siswa menunjukkan tanggung jawab terhadap tugas belajar', 'Reward'),
(681, 'Siswa memperoleh nilai memuaskan dalam kegiatan pembelajaran', 'Reward'),
(682, 'Siswa berhasil mencapai prestasi akademik yang tinggi', 'Reward'),
(683, 'Siswa aktif dalam kegiatan pembelajaran di kelas', 'Reward'),
(684, 'Siswa memperoleh hasil ujian yang sangat memuaskan', 'Reward'),
(685, 'Siswa mampu menyelesaikan tugas akademik tepat waktu', 'Reward'),
(686, 'Siswa mendapatkan nilai tinggi secara konsisten', 'Reward'),
(687, 'Siswa memperoleh prestasi terbaik dalam bidang akademik', 'Reward'),
(688, 'Siswa menunjukkan kemampuan belajar yang sangat baik', 'Reward'),
(689, 'Siswa berhasil memperoleh nilai unggul pada ujian akhir', 'Reward'),
(690, 'Siswa memperoleh hasil akademik yang membanggakan', 'Reward'),
(691, 'Siswa aktif mengembangkan kemampuan akademik', 'Reward'),
(692, 'Siswa menunjukkan dedikasi tinggi terhadap pembelajaran', 'Reward'),
(693, 'Siswa berhasil memperoleh nilai terbaik pada kelas produktif', 'Reward'),
(694, 'Siswa memperoleh penghargaan atas prestasi akademik', 'Reward'),
(695, 'Siswa menunjukkan kemampuan memahami teori dan praktik', 'Reward'),
(696, 'Siswa memperoleh nilai tinggi pada evaluasi pembelajaran', 'Reward'),
(697, 'Siswa berhasil mencapai target nilai semester', 'Reward'),
(698, 'Siswa memiliki motivasi belajar yang sangat tinggi', 'Reward'),
(699, 'Siswa menunjukkan kemampuan akademik secara konsisten', 'Reward'),
(700, 'Siswa memperoleh hasil belajar yang optimal', 'Reward'),
(701, 'Siswa aktif mengikuti pembelajaran dengan baik', 'Reward'),
(702, 'Siswa berhasil mendapatkan nilai terbaik pada ujian sekolah', 'Reward'),
(703, 'Siswa memperoleh pencapaian akademik yang sangat baik', 'Reward'),
(704, 'Siswa menunjukkan kualitas belajar yang unggul', 'Reward'),
(705, 'Siswa berhasil mempertahankan prestasi akademik', 'Reward'),
(706, 'Siswa memperoleh hasil penilaian terbaik di kelas', 'Reward'),
(707, 'Siswa aktif menyelesaikan tugas akademik', 'Reward'),
(708, 'Siswa memperoleh nilai memuaskan pada seluruh mata pelajaran', 'Reward'),
(709, 'Siswa menunjukkan kemampuan akademik di atas rata rata', 'Reward'),
(710, 'Siswa berhasil mendapatkan prestasi belajar terbaik', 'Reward'),
(711, 'Siswa memperoleh nilai praktik yang sangat memuaskan', 'Reward'),
(712, 'Siswa menunjukkan semangat tinggi dalam belajar', 'Reward'),
(713, 'Siswa memperoleh hasil akademik yang konsisten', 'Reward'),
(714, 'Siswa berhasil mencapai nilai unggulan sekolah', 'Reward'),
(715, 'Siswa menunjukkan kemampuan menyelesaikan soal dengan baik', 'Reward'),
(716, 'Siswa memperoleh prestasi akademik yang sangat memuaskan', 'Reward'),
(717, 'Siswa aktif meningkatkan kemampuan belajar', 'Reward'),
(718, 'Siswa memperoleh nilai terbaik pada ujian kompetensi', 'Reward'),
(719, 'Siswa berhasil mencapai hasil belajar optimal', 'Reward'),
(720, 'Siswa menunjukkan kemampuan akademik yang membanggakan', 'Reward'),
(721, 'Siswa memperoleh hasil evaluasi akademik terbaik', 'Reward'),
(722, 'Siswa berhasil mempertahankan nilai akademik tinggi', 'Reward'),
(723, 'Siswa menunjukkan prestasi belajar yang sangat baik', 'Reward'),
(724, 'Siswa memperoleh penghargaan Siswa berprestasi akademik', 'Reward'),
(725, 'Siswa berhasil memperoleh nilai sangat memuaskan', 'Reward'),
(726, 'Siswa menunjukkan kemampuan belajar secara aktif', 'Reward'),
(727, 'Siswa memperoleh hasil ujian praktik terbaik', 'Reward'),
(728, 'Siswa berhasil mencapai prestasi akademik unggul', 'Reward'),
(729, 'Siswa memperoleh nilai terbaik pada kegiatan evaluasi', 'Reward'),
(730, 'Siswa menunjukkan kemampuan akademik yang luar biasa', 'Reward'),
(731, 'Siswa berhasil memperoleh hasil belajar terbaik', 'Reward'),
(732, 'Siswa memperoleh nilai akademik tinggi secara konsisten', 'Reward'),
(733, 'Siswa menunjukkan kualitas akademik yang sangat baik', 'Reward'),
(734, 'Siswa berhasil mendapatkan penghargaan akademik sekolah', 'Reward'),
(735, 'Siswa memperoleh hasil pembelajaran yang sangat baik', 'Reward'),
(736, 'Siswa menunjukkan kemampuan unggul dalam bidang akademik', 'Reward'),
(737, 'Siswa berhasil mencapai prestasi belajar yang membanggakan', 'Reward');

-- --------------------------------------------------------

--
-- Table structure for table `evaluasi_siswa`
--

CREATE TABLE `evaluasi_siswa` (
  `id_evaluasi` int(11) NOT NULL,
  `poin` text NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `tindakan` text NOT NULL,
  `jenis` enum('Reward','Punishment') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluasi_siswa`
--

INSERT INTO `evaluasi_siswa` (`id_evaluasi`, `poin`, `kategori`, `tindakan`, `jenis`) VALUES
(4, '40', 'Siswa Aktif', 'Piagam penghargaan dari sekolah', 'Reward'),
(5, '60', 'Siswa Berprestasi', 'Piagam penghargaan + alat tulis / perlengkapan sekolah', 'Reward'),
(6, '80', 'Siswa Teladan', 'Piagam penghargaan + hadiah pembinaan', 'Reward'),
(7, '100', 'Siswa Berprestasi Tinggi', 'Piagam penghargaan + piala / medali', 'Reward'),
(8, '150', 'Siswa Unggul', 'Piagam penghargaan + bantuan perlengkapan sekolah', 'Reward'),
(9, '200', 'Siswa Inspiratif', 'Piagam penghargaan + rekomendasi mewakili sekolah dalam kegiatan/lomba', 'Reward'),
(10, '250', 'Siswa Terbaik', 'Piagam penghargaan + hadiah khusus / penghargaan dari kepala sekolah', 'Reward'),
(11, '5', 'Ringan 1', 'Teguran lisan, dicatat pada buku pelanggaran dan buku saku', 'Punishment'),
(12, '15', 'Ringan 2', 'Teguran tertulis, dicatat pada buku pelanggaran dan buku saku', 'Punishment'),
(13, '25', 'Sedang', 'Teguran dan pemanggilan orang tua / wali, ditambah bakti kampus selama 3 hari', 'Punishment'),
(14, '60', 'Berat', 'Panggilan orang tua / wali, surat perjanjian bermaterai, ditambah bakti kampus selama 3 hari', 'Punishment'),
(15, '90', 'Berat Sekali', 'Panggilan orang tua / wali, surat perjanjian bermaterai, ditambah bakti kampus selama 3 hari', 'Punishment');

-- --------------------------------------------------------

--
-- Table structure for table `laporan_prilaku`
--

CREATE TABLE `laporan_prilaku` (
  `id_laporan` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_periode` int(11) DEFAULT NULL,
  `teks_laporan` text NOT NULL,
  `label_prediksi` enum('Reward','Punishment') NOT NULL,
  `kecocokan_kata` text NOT NULL,
  `poin_didapat` int(11) NOT NULL,
  `akurasi_map` text NOT NULL,
  `foto` text NOT NULL,
  `status_verifikasi` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `id_verifikator` int(11) DEFAULT NULL,
  `tgl_verifikasi` timestamp NULL DEFAULT NULL,
  `catatan_verifikasi` text DEFAULT NULL,
  `tgl_input` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laporan_prilaku`
--

INSERT INTO `laporan_prilaku` (`id_laporan`, `id_siswa`, `id_user`, `id_periode`, `teks_laporan`, `label_prediksi`, `kecocokan_kata`, `poin_didapat`, `akurasi_map`, `foto`, `status_verifikasi`, `id_verifikator`, `tgl_verifikasi`, `catatan_verifikasi`, `tgl_input`) VALUES
(192, 10, 1, 1, 'kamila telat masuk sekolah', 'Punishment', 'siswa datang terlambat karena kesiangan', 10, '78%', '', 'disetujui', NULL, NULL, NULL, '2026-08-13 03:28:11'),
(193, 28, 1, 1, 'siswa ini telat masuk sekolah', 'Punishment', 'siswa telat masuk sekolah dan dihukum', 10, '79%', 'uploads/bukti_laporan/bukti_28_1786592535_6a7d3d17a1322.jpg', 'disetujui', NULL, NULL, NULL, '2026-08-13 03:42:15'),
(194, 14, 1, 1, 'siswa ini bolos masuk sekolah karena telat', 'Punishment', 'siswa telat masuk sekolah dan dihukum', 10, '58%', 'uploads/bukti_laporan/bukti_14_1786668294_6a7e6506ba4a9.png', 'disetujui', NULL, NULL, NULL, '2026-08-14 00:44:55'),
(195, 14, 1, 1, 'siswa terajin di kelas XII-RPL-1 berdasarkan rekap kehadiran dan pengumpulan tugas bulan ini.', 'Reward', 'siswa menunda pengumpulan tugas', 10, '79%', 'uploads/bukti_laporan/bukti_14_1786668438_6a7e65969ef89.png', 'disetujui', NULL, NULL, NULL, '2026-08-14 00:47:19'),
(196, 15, 1, 1, 'REY menjadi siswa terajin di kelas XII-RPL-1 berdasarkan rekap kehadiran dan pengumpulan tugas bulan ini.', 'Reward', 'siswa menunda pengumpulan tugas', 20, '79%', 'uploads/bukti_laporan/bukti_15_1786668507_6a7e65dbb7b38.png', 'disetujui', NULL, NULL, NULL, '2026-08-14 00:48:27'),
(197, 99, 1, 1, 'siswa terajin di kelas XII-RPL-1 berdasarkan rekap kehadiran dan pengumpulan tugas bulan ini.', 'Reward', 'siswa menunda pengumpulan tugas', 10, '79%', 'uploads/bukti_laporan/bukti_99_1786668611_6a7e6643982f4.jpg', 'disetujui', NULL, NULL, NULL, '2026-08-14 00:50:11'),
(198, 21, 1, 1, 'siswa terlambat masuk kelas', 'Punishment', 'siswa terlambat masuk kelas', 10, '100%', 'uploads/bukti_laporan/bukti_21_1787729975_6a8e98379846d.jpg', 'disetujui', NULL, NULL, NULL, '2026-08-26 07:39:36'),
(199, 20, 1, 1, 'telat sekolah', 'Punishment', 'siswa telat mengumpulkan tugas sekolah', 10, '64%', 'uploads/bukti_laporan/bukti_20_1787733235_6a8ea4f30e47a.png', 'disetujui', 1, '2026-08-26 08:37:22', '', '2026-08-26 08:33:55'),
(200, 21, 1, 1, 'telat modol', 'Punishment', 'siswa telat mengumpulkan tugas sekolah', 10, '59%', 'uploads/bukti_laporan/bukti_21_1787733460_6a8ea5d4852b5.png', 'disetujui', 1, '2026-08-26 08:41:24', '', '2026-08-26 08:37:40'),
(201, 20, 1, 2, 'telat', 'Punishment', 'siswa telat mengumpulkan tugas sekolah', 10, '59%', 'uploads/bukti_laporan/bukti_20_1787736969_6a8eb38912c5e.png', 'disetujui', 1, '2026-08-26 09:36:18', '', '2026-08-26 09:36:09');

-- --------------------------------------------------------

--
-- Table structure for table `master_poin`
--

CREATE TABLE `master_poin` (
  `id_aturan` int(11) NOT NULL,
  `jenis` enum('Reward','Punishment') NOT NULL,
  `nama_perilaku` varchar(255) NOT NULL,
  `poin` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_poin`
--

INSERT INTO `master_poin` (`id_aturan`, `jenis`, `nama_perilaku`, `poin`) VALUES
(1, 'Punishment', 'siswa datang terlambat karena kesiangan', 10),
(2, 'Punishment', 'siswa telat masuk sekolah dan dihukum', 10),
(3, 'Punishment', 'siswa terlambat mengikuti upacara bendera', 10),
(4, 'Punishment', 'siswa terlambat saat ada razia di pintu gerbang', 10),
(5, 'Punishment', 'siswa melompat pagar sekolah karena terlambat', 10),
(6, 'Punishment', 'siswa telat mengumpulkan tugas sekolah', 10),
(7, 'Punishment', 'siswa nongkrong di warung saat jam pelajaran dan terlambat masuk', 10),
(8, 'Punishment', 'siswa telat masuk sekolah tanpa keterangan atau alpha', 10),
(9, 'Punishment', 'siswa terlambat mengikuti ujian sekolah', 10),
(10, 'Punishment', 'siswa dihukum di lapangan sekolah karena telat', 10),
(11, 'Punishment', 'siswa membuang sampah sembarangan di lingkungan sekolah', 10),
(12, 'Punishment', 'siswa mencoret meja belajar menggunakan spidol', 10),
(13, 'Punishment', 'siswa mengotori dinding toilet sekolah', 10),
(14, 'Punishment', 'siswa meninggalkan lingkungan dalam kondisi kotor dan sampah berserakan', 10),
(15, 'Punishment', 'siswa menyumbat wastafel sekolah dengan sampah', 10),
(16, 'Punishment', 'siswa menendang pintu fasilitas sekolah sampai rusak dan patah', 10),
(17, 'Punishment', 'siswa mencoret dinding sekolah dengan cat piloks', 10),
(18, 'Punishment', 'siswa merusak saklar lampu kelas', 10),
(19, 'Punishment', 'siswa mematahkan penggaris inventaris kelas', 10),
(20, 'Punishment', 'siswa melempari kaca jendela hingga retak', 10),
(21, 'Punishment', 'siswa merobek buku perpustakaan sekolah', 10),
(22, 'Punishment', 'siswa merusak jaring net olahraga', 10),
(23, 'Punishment', 'siswa mencopot tanaman dan merusak pot bunga sekolah', 10),
(24, 'Punishment', 'siswa menggores mobil guru menggunakan paku', 10),
(25, 'Punishment', 'siswa merusak kran air hingga patah', 10),
(26, 'Punishment', 'siswa membawa memakai penjual atau mengedar alkohol judi online narkoba dan psikotropika', 100),
(27, 'Punishment', 'siswa merampas atau mencuri milik orang lain baik di sekolah maupun di luar sekolah', 100),
(28, 'Punishment', 'siswa berkelahi melakukan penganiayaan atau membuat keonaran yang mengganggu ketertiban', 100),
(29, 'Punishment', 'siswa hamil menghamili melakukan pemerkosaan atau menikah selama masa sekolah', 100),
(30, 'Punishment', 'siswa ditato ditindik atau ditendok oleh diri sendiri maupun orang lain', 100),
(31, 'Punishment', 'siswa memalsukan surat atau tanda tangan pejabat sekolah', 50),
(32, 'Punishment', 'siswa menghina memfitnah atau merendahkan kepala sekolah guru tu dan karyawan sekolah', 50),
(33, 'Punishment', 'siswa membawa senjata tajam senjata api atau alat lain yang membahayakan', 50),
(34, 'Punishment', 'siswa membawa bacaan gambar vcd hp atau media yang bersifat pornografi', 50),
(35, 'Punishment', 'siswa merusak dan menghilangkan barang milik sekolah atau milik orang lain', 50),
(36, 'Punishment', 'siswa merusak lingkungan sekolah atau lingkungan umum', 50),
(37, 'Punishment', 'siswa melakukan pelecehan seksual', 50),
(38, 'Punishment', 'siswa menyalahgunakan keuangan sekolah', 30),
(39, 'Punishment', 'siswa merokok di lingkungan sekolah atau di luar dengan memakai seragam atribut sekolah', 30),
(40, 'Punishment', 'siswa kabur atau meninggalkan kelas sekolah tanpa izin guru pengajar', 30),
(41, 'Punishment', 'siswa memalsukan surat atau tanda tangan orang tua wali', 30),
(42, 'Punishment', 'siswa tidak masuk sekolah tanpa keterangan atau alpha tiga hari dalam sepekan', 30),
(43, 'Punishment', 'siswa tidak masuk sekolah tanpa keterangan atau alpha lebih dari tiga hari dalam sebulan', 30),
(44, 'Punishment', 'siswa membuat coretan atau gambar pada pakaian tas lingkungan sekolah atau tempat umum', 30),
(45, 'Punishment', 'siswa memakai aksesoris terlarang seperti jaket sweater topi gelang sabuk kalung perhiasan', 15),
(46, 'Punishment', 'siswa berpakaian seragam dengan ukuran bahan atau potongan tidak sesuai ketentuan', 15),
(47, 'Punishment', 'siswa membawa rokok ke sekolah', 15),
(48, 'Punishment', 'siswa memakai pakaian yang tidak sesuai dengan ketentuan hari yang berlaku', 15),
(49, 'Punishment', 'siswa tidak mengikuti upacara bendera atau kegiatan pagi siang tanpa alasan', 15),
(50, 'Punishment', 'siswa tidak memakai pakaian praktik selama melaksanakan pelajaran praktik', 15),
(51, 'Punishment', 'siswa berpenampilan rambut gondrong rambut dicat tidak memakai atribut lengkap atau makeup berlebihan', 15),
(52, 'Punishment', 'siswa melompat benteng sekolah untuk masuk mengikuti pelajaran', 15),
(53, 'Punishment', 'siswa menyalahgunakan media sosial tidak sesuai peruntukan seperti konten foto video status chat kasar', 15),
(54, 'Punishment', 'siswa menghilangkan buku saku dan wajib mengganti yang baru', 5),
(55, 'Punishment', 'siswa tidak membawa buku saku saat pemeriksaan', 5),
(56, 'Punishment', 'siswa tidak masuk sekolah tanpa keterangan harian', 5),
(57, 'Punishment', 'siswa datang terlambat melewati pintu gerbang di atas jam tujuh lima belas', 5),
(58, 'Punishment', 'siswa keluar lingkungan sekolah saat jam istirahat tanpa izin piket', 5),
(59, 'Punishment', 'siswa buang sampah sembarangan tidak pada tempatnya', 5),
(60, 'Punishment', 'siswa membiarkan baju dikeluarkan dari celana atau rok', 5),
(61, 'Punishment', 'siswa berbicara kasar tidak sopan dan tidak senonoh', 5),
(62, 'Reward', 'siswa sukarela membersihkan kaca jendela kelas', 10),
(63, 'Reward', 'siswa menyapu dan mengepel lantai hingga bersih', 10),
(64, 'Reward', 'siswa merapikan peralatan piket setelah selesai digunakan', 10),
(65, 'Reward', 'siswa membuang sampah organik pada tempat yang benar', 10),
(66, 'Reward', 'siswa membantu menyiram tanaman di taman sekolah', 10),
(67, 'Reward', 'siswa berhasil meraih prestasi tingkat kecamatan melalui partisipasi aktif dan kemampuan baik dalam perlombaan', 25),
(68, 'Reward', 'siswa menunjukkan prestasi tingkat kecamatan dengan memperoleh penghargaan pada kegiatan lomba antar sekolah', 25),
(69, 'Reward', 'siswa memiliki prestasi tingkat kecamatan yang membanggakan dan membawa nama baik sekolah', 25),
(70, 'Reward', 'siswa aktif mengikuti kompetisi dan berhasil mendapatkan prestasi tingkat kecamatan', 25),
(71, 'Reward', 'siswa meraih prestasi tingkat kecamatan yang menunjukkan sikap disiplin kerja keras dan tanggung jawab', 25),
(72, 'Reward', 'siswa memperoleh prestasi tingkat kecamatan dalam perlombaan akademik maupun non akademik', 25),
(73, 'Reward', 'siswa memperoleh prestasi tingkat kabupaten dalam perlombaan pramuka dengan meraih juara dua', 50),
(74, 'Reward', 'siswa berhasil meraih prestasi tingkat kabupaten pada ajang perlombaan pramuka dengan juara dua', 50),
(75, 'Reward', 'siswa mendapatkan prestasi tingkat kabupaten dalam kegiatan lomba pramuka sebagai juara dua', 50),
(76, 'Reward', 'siswa menorehkan prestasi tingkat kabupaten pada perlombaan pramuka tingkat kabupaten juara dua', 50),
(77, 'Reward', 'siswa memperoleh prestasi tingkat provinsi dalam perlombaan pramuka tingkat provinsi juara dua', 75),
(78, 'Reward', 'siswa berhasil meraih prestasi tingkat provinsi pada kegiatan perlombaan pramuka juara dua', 75),
(79, 'Reward', 'siswa mendapatkan penghargaan prestasi tingkat provinsi dalam lomba pramuka perolehan juara dua', 75),
(80, 'Reward', 'siswa menunjukkan prestasi membanggakan pada perlombaan pramuka tingkat provinsi meraih juara dua', 75),
(81, 'Reward', 'siswa sangat aktif bertanya saat proses pembelajaran berlangsung', 20),
(82, 'Reward', 'siswa aktif mengikuti jalannya diskusi di dalam kelas', 20),
(83, 'Reward', 'siswa selalu hadir tepat waktu di setiap jam pelajaran', 20),
(84, 'Reward', 'siswa bersedia membantu teman yang mengalami kesulitan belajar', 20),
(85, 'Reward', 'siswa rajin mengerjakan seluruh tugas yang diberikan oleh guru', 20),
(86, 'Reward', 'siswa berpartisipasi aktif dalam setiap kegiatan sekolah', 20),
(87, 'Reward', 'siswa aktif dalam kepengurusan organisasi sekolah', 20),
(88, 'Reward', 'siswa konsisten menjaga kebersihan dan kerapihan ruang kelas', 20),
(89, 'Reward', 'siswa selalu berbicara sopan dan santun kepada guru', 20),
(90, 'Reward', 'Siswa mengikuti pelaksanaan upacara bendera dengan tertib dan khidmat', 20),
(91, 'Reward', 'siswa selalu memakai atribut seragam sekolah secara lengkap', 20),
(92, 'Reward', 'siswa rajin membaca buku dan berkunjung ke perpustakaan', 20),
(93, 'Reward', 'siswa menjadi ketua kelompok yang bertanggung jawab dan baik', 20),
(94, 'Reward', 'siswa membantu guru menyiapkan peralatan kegiatan kelas', 20),
(95, 'Reward', 'siswa sangat aktif saat melakukan presentasi kelompok', 20),
(96, 'Reward', 'siswa berhasil mendapat nilai terbaik dan juara di kelas', 20),
(97, 'Reward', 'siswa selalu menerapkan sikap disiplin dalam belajar', 20),
(98, 'Reward', 'siswa memiliki rasa tanggung jawab penuh terhadap tugas harian', 20),
(99, 'Reward', 'siswa aktif mengikuti kegiatan ekstrakurikuler sekolah', 20),
(100, 'Reward', 'siswa menjadi teladan dan contoh bagi teman-temannya', 20),
(101, 'Reward', 'siswa berprestasi dalam ajang lomba yang diadakan sekolah', 20),
(102, 'Reward', 'siswa aktif mengikuti praktik pembelajaran di laboratorium', 20),
(103, 'Reward', 'siswa selalu menjaga kerapihan pakaian seragam sepanjang hari', 20),
(104, 'Reward', 'siswa datang lebih awal ke sekolah sebelum bel berbunyi', 20),
(105, 'Reward', 'siswa mampu menyelesaikan tugas tepat waktu sesuai tenggat', 20),
(106, 'Reward', 'siswa aktif memberikan ide dan saran saat diskusi kelompok', 20),
(107, 'Reward', 'siswa rajin mengikuti kegiatan literasi sekolah', 20),
(108, 'Reward', 'siswa memiliki sikap sopan santun serta etika yang baik', 20),
(109, 'Reward', 'siswa membantu menjaga kebersihan lingkungan sekolah', 20),
(110, 'Reward', 'siswa aktif dalam kegiatan sosial yang diadakan sekolah', 20),
(111, 'Reward', 'siswa menjadi figur inspiratif bagi Siswa lain di kelas', 20),
(112, 'Reward', 'siswa berinisiatif membantu teman yang sedang mengalami kesulitan', 20),
(113, 'Reward', 'siswa konsisten hadir di sekolah tanpa ada catatan alpha', 20),
(114, 'Reward', 'siswa aktif mengikuti seluruh kegiatan keagamaan di sekolah', 20),
(115, 'Reward', 'siswa menjadi anggota organisasi yang berdedikasi dan disiplin', 20),
(116, 'Reward', 'siswa berprestasi di bidang olahraga dan seni', 20),
(117, 'Reward', 'siswa berprestasi di bidang akademik sekolah', 20),
(118, 'Reward', 'siswa selalu menjaga ketertiban kelas saat jam pelajaran', 20),
(119, 'Reward', 'siswa aktif menjawab pertanyaan yang diajukan oleh guru', 20),
(120, 'Reward', 'siswa memiliki semangat dan motivasi belajar yang tinggi', 20),
(121, 'Reward', 'siswa mengikuti seluruh kegiatan sekolah dengan sangat baik', 20),
(122, 'Reward', 'siswa terpilih menjadi Siswa paling disiplin di sekolah', 20),
(123, 'Reward', 'siswa rajin mengikuti dan melaksanakan piket kelas', 20),
(124, 'Reward', 'siswa aktif berkontribusi dalam kerja kelompok', 20),
(125, 'Reward', 'siswa dikenal sebagai Siswa dengan perilaku dan budi pekerti baik', 20),
(126, 'Reward', 'siswa selalu menghormati guru dan menghargai sesama teman', 20),
(127, 'Reward', 'siswa aktif mengikuti seminar atau workshop sekolah', 20),
(128, 'Reward', 'siswa memiliki tanggung jawab tinggi terhadap tugas akademik dan praktik', 20),
(129, 'Reward', 'siswa aktif dalam kegiatan kepramukaan sekolah', 20),
(130, 'Reward', 'siswa membantu teman memahami materi pelajaran yang sulit', 20),
(131, 'Reward', 'siswa selalu menjaga nama baik dan citra sekolah', 20),
(132, 'Reward', 'siswa aktif dalam perlombaan antar kelas atau classmeeting', 20),
(133, 'Reward', 'siswa berhasil meraih peringkat atau juara kelas', 20),
(134, 'Reward', 'siswa menjadi Siswa dengan persentase kehadiran terbaik', 20),
(135, 'Reward', 'siswa rajin mengikuti pelatihan atau pembinaan sekolah', 20),
(136, 'Reward', 'siswa aktif memberikan solusi pemecahan masalah saat diskusi', 20),
(137, 'Reward', 'siswa dinobatkan menjadi Siswa teladan sekolah', 20),
(138, 'Reward', 'siswa aktif dalam kegiatan aksi kebersihan lingkungan sekolah', 20),
(139, 'Reward', 'siswa rajin mengumpulkan tugas harian tepat waktu', 20),
(140, 'Reward', 'siswa memiliki kemampuan komunikasi yang baik dan efektif', 20),
(141, 'Reward', 'siswa aktif dalam setiap agenda kegiatan kelas', 20),
(142, 'Reward', 'siswa mampu bekerja sama dengan baik di dalam tim', 20),
(143, 'Reward', 'siswa selalu bersikap jujur dalam ujian maupun keseharian', 20),
(144, 'Reward', 'siswa berani tampil percaya diri saat presentasi di depan kelas', 20),
(145, 'Reward', 'siswa memiliki komitmen dan dedikasi tinggi dalam pembelajaran', 20),
(146, 'Reward', 'siswa rajin latihan soal-soal dan ulasan materi secara mandiri', 20),
(147, 'Reward', 'siswa tekun belajar menghadapi pelaksanaan ujian sekolah', 20),
(148, 'Reward', 'siswa menunjukkan kerapihan rambut sesuai dengan regulasi sekolah', 20),
(149, 'Reward', 'siswa tertib berpakaian seragam sesuai dengan jadwal yang ditentukan', 20),
(150, 'Reward', 'siswa menampilkan penampilan sopan beretika di depan guru', 20),
(151, 'Reward', 'siswa memiliki kemampuan analisis dan berpikir kritis yang baik', 20),
(152, 'Reward', 'siswa memiliki pemahaman materi teori dan kompetensi praktik yang unggul', 20),
(153, 'Reward', 'siswa konsisten mempertahankan hasil belajar yang optimal', 20),
(291, 'Punishment', 'siswa tidak sopan dan santun kepada guru', 5);

-- --------------------------------------------------------

--
-- Table structure for table `periode_akademik`
--

CREATE TABLE `periode_akademik` (
  `id_periode` int(11) NOT NULL,
  `tahun_ajaran` varchar(9) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('aktif','arsip') NOT NULL DEFAULT 'arsip',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `periode_akademik`
--

INSERT INTO `periode_akademik` (`id_periode`, `tahun_ajaran`, `tanggal_mulai`, `tanggal_selesai`, `status`, `dibuat_pada`) VALUES
(1, 'Sebelumny', '2000-01-01', '2000-06-30', 'arsip', '2026-08-26 09:27:13'),
(2, '2026/2027', '2026-01-01', '2026-12-31', 'arsip', '2026-08-26 09:28:08'),
(3, '2024/2025', '2026-01-01', '2026-12-31', 'arsip', '2026-08-26 09:28:39'),
(5, '2027/2028', '2027-01-01', '2028-01-26', 'aktif', '2026-08-26 09:37:23');

-- --------------------------------------------------------

--
-- Table structure for table `rekap_poin_periode`
--

CREATE TABLE `rekap_poin_periode` (
  `id_rekap` int(11) NOT NULL,
  `id_periode` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `total_poin_reward` int(11) NOT NULL DEFAULT 0,
  `total_poin_punishment` int(11) NOT NULL DEFAULT 0,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rekap_poin_periode`
--

INSERT INTO `rekap_poin_periode` (`id_rekap`, `id_periode`, `id_siswa`, `total_poin_reward`, `total_poin_punishment`, `dibuat_pada`) VALUES
(1, 2, 1, 0, 0, '2026-08-26 09:39:34'),
(2, 2, 2, 0, 0, '2026-08-26 09:39:34'),
(3, 2, 3, 0, 0, '2026-08-26 09:39:34'),
(4, 2, 4, 0, 0, '2026-08-26 09:39:34'),
(5, 2, 5, 0, 0, '2026-08-26 09:39:34'),
(6, 2, 6, 0, 0, '2026-08-26 09:39:34'),
(7, 2, 7, 0, 0, '2026-08-26 09:39:34'),
(8, 2, 8, 0, 0, '2026-08-26 09:39:34'),
(9, 2, 9, 0, 0, '2026-08-26 09:39:34'),
(10, 2, 10, 0, 0, '2026-08-26 09:39:34'),
(11, 2, 11, 0, 0, '2026-08-26 09:39:34'),
(12, 2, 12, 0, 0, '2026-08-26 09:39:34'),
(13, 2, 13, 0, 0, '2026-08-26 09:39:34'),
(14, 2, 14, 0, 0, '2026-08-26 09:39:34'),
(15, 2, 15, 0, 0, '2026-08-26 09:39:34'),
(16, 2, 16, 0, 0, '2026-08-26 09:39:34'),
(17, 2, 17, 0, 0, '2026-08-26 09:39:34'),
(18, 2, 18, 0, 0, '2026-08-26 09:39:34'),
(19, 2, 19, 0, 0, '2026-08-26 09:39:34'),
(20, 2, 20, 0, 10, '2026-08-26 09:39:34'),
(21, 2, 21, 0, 0, '2026-08-26 09:39:34'),
(22, 2, 22, 0, 0, '2026-08-26 09:39:34'),
(23, 2, 23, 0, 0, '2026-08-26 09:39:34'),
(24, 2, 24, 0, 0, '2026-08-26 09:39:34'),
(25, 2, 25, 0, 0, '2026-08-26 09:39:34'),
(26, 2, 26, 0, 0, '2026-08-26 09:39:34'),
(27, 2, 27, 0, 0, '2026-08-26 09:39:34'),
(28, 2, 28, 0, 0, '2026-08-26 09:39:34'),
(29, 2, 29, 0, 0, '2026-08-26 09:39:34'),
(30, 2, 30, 0, 0, '2026-08-26 09:39:34'),
(31, 2, 31, 0, 0, '2026-08-26 09:39:34'),
(32, 2, 32, 0, 0, '2026-08-26 09:39:34'),
(33, 2, 33, 0, 0, '2026-08-26 09:39:34'),
(34, 2, 34, 0, 0, '2026-08-26 09:39:34'),
(35, 2, 35, 0, 0, '2026-08-26 09:39:34'),
(36, 2, 36, 0, 0, '2026-08-26 09:39:34'),
(37, 2, 37, 0, 0, '2026-08-26 09:39:34'),
(38, 2, 38, 0, 0, '2026-08-26 09:39:34'),
(39, 2, 39, 0, 0, '2026-08-26 09:39:34'),
(40, 2, 40, 0, 0, '2026-08-26 09:39:34'),
(41, 2, 41, 0, 0, '2026-08-26 09:39:34'),
(42, 2, 42, 0, 0, '2026-08-26 09:39:34'),
(43, 2, 43, 0, 0, '2026-08-26 09:39:34'),
(44, 2, 44, 0, 0, '2026-08-26 09:39:34'),
(45, 2, 45, 0, 0, '2026-08-26 09:39:34'),
(46, 2, 46, 0, 0, '2026-08-26 09:39:34'),
(47, 2, 47, 0, 0, '2026-08-26 09:39:34'),
(48, 2, 48, 0, 0, '2026-08-26 09:39:34'),
(49, 2, 99, 0, 0, '2026-08-26 09:39:34'),
(50, 2, 100, 0, 0, '2026-08-26 09:39:34');

-- --------------------------------------------------------

--
-- Table structure for table `remisi`
--

CREATE TABLE `remisi` (
  `id_remisi` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_periode` int(11) DEFAULT NULL,
  `poin_remisi` int(12) NOT NULL,
  `pengajuan` enum('konfirmasi','pengajuan') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `bukti` text DEFAULT NULL,
  `tgl_input` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remisi`
--

INSERT INTO `remisi` (`id_remisi`, `id_siswa`, `id_user`, `id_periode`, `poin_remisi`, `pengajuan`, `keterangan`, `bukti`, `tgl_input`) VALUES
(5, 43, 1, 1, 40, 'konfirmasi', 'Sistem Konfirmasi Otomatis: Sinkronisasi pemotongan 40 poin sanksi menggunakan poin reward.', NULL, '2026-08-01 22:10:47'),
(6, 8, 1, 1, 10, 'pengajuan', 'oke', 'uploads/bukti_remisi/bukti_remisi_8_1786782102_6a8021967b909.jpg', '2026-08-15 15:21:42'),
(7, 8, 1, 1, 50, 'pengajuan', 'fs', 'uploads/bukti_remisi/bukti_remisi_8_1786788969_6a803c695483e.jpg', '2026-08-15 17:16:09');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `total_poin_reward` int(11) DEFAULT 0,
  `total_poin_punishment` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `nis`, `nama_siswa`, `kelas`, `total_poin_reward`, `total_poin_punishment`) VALUES
(1, '232401001', 'Algam Risgi Sugirman', 'XII-RPL-1', 0, 0),
(2, '232401002', 'Cikal Putra Subrani', 'XII-RPL-1', 0, 0),
(3, '232401003', 'Dodi Aril Fauzi', 'XII-RPL-1', 0, 0),
(4, '232401005', 'Eki Apriansyah', 'XII-RPL-1', 0, 0),
(5, '232401004', 'Eneng Salsa Nabila', 'XII-RPL-1', 0, 0),
(6, '232401006', 'Erwin Suherwan', 'XII-RPL-1', 0, 0),
(7, '232401007', 'Fauzan Sya\'ban Nur\'islam', 'XII-RPL-1', 0, 0),
(8, '232401008', 'Hermawan Anggara', 'XII-RPL-1', 0, 0),
(9, '232401009', 'Jannata Tsamaniya Arsyistawaa', 'XII-RPL-1', 0, 0),
(10, '232401011', 'Kamilaniya Tsaqila', 'XII-RPL-1', 0, 0),
(11, '232401012', 'Kurniasandi Aunurrafiq', 'XII-RPL-1', 0, 0),
(12, '232401019', 'Mirna Sulastri', 'XII-RPL-1', 0, 0),
(13, '232401013', 'Niswah Kamilah Nur Ala', 'XII-RPL-1', 0, 0),
(14, '232401014', 'Ramdan Alwiansyah', 'XII-RPL-1', 0, 0),
(15, '232401015', 'Rey Gustiawan', 'XII-RPL-1', 0, 0),
(16, '232401016', 'Siti Hasanah', 'XII-RPL-1', 0, 0),
(17, '232401017', 'Suryana', 'XII-RPL-1', 0, 0),
(18, '232401018', 'Tiara Amanda Putri Fatriana', 'XII-RPL-1', 0, 0),
(19, '232401020', 'Wahid Dicky Nugroho', 'XII-RPL-1', 0, 0),
(20, '232401021', 'Abdul Hakim', 'XII-RPL-2', 0, 0),
(21, '232401022', 'Aditya Putra Nurahman', 'XII-RPL-2', 0, 0),
(22, '232401027', 'Adrian Bagus Ramadani', 'XII-RPL-2', 0, 0),
(23, '232401023', 'Agil Al Pauji', 'XII-RPL-2', 0, 0),
(24, '232401024', 'Agunawan', 'XII-RPL-2', 0, 0),
(25, '232401025', 'Ali Akbar Nur Subhki', 'XII-RPL-2', 0, 0),
(26, '232401026', 'Alma Defaulia Maharani', 'XII-RPL-2', 0, 0),
(27, '232401028', 'Arabi Rudinata', 'XII-RPL-2', 0, 0),
(28, '232401029', 'Ardiansyah', 'XII-RPL-2', 0, 0),
(29, '232401030', 'Astri Lianti', 'XII-RPL-2', 0, 0),
(30, '232401031', 'Aura Putri Ramadani', 'XII-RPL-2', 0, 0),
(31, '232401032', 'Erlangga Wiradinata', 'XII-RPL-2', 0, 0),
(32, '232401033', 'Ikhsan Alfandi Pratama', 'XII-RPL-2', 0, 0),
(33, '232401035', 'Kireina Nazrila Irawan', 'XII-RPL-2', 0, 0),
(34, '232401040', 'Moch. Viras Algazali', 'XII-RPL-2', 0, 0),
(35, '232401039', 'Muhamad Rizki Andika', 'XII-RPL-2', 0, 0),
(36, '232401036', 'Muhamad Sidik', 'XII-RPL-2', 0, 0),
(37, '232401042', 'Naila Nazwa Latifa', 'XII-RPL-2', 0, 0),
(38, '232401043', 'Nyai Indah Lestari', 'XII-RPL-2', 0, 0),
(39, '232401044', 'Raka Leaner Yacob Boom', 'XII-RPL-2', 0, 0),
(40, '232401045', 'Ratu Chika Sri Astuti', 'XII-RPL-2', 0, 0),
(41, '232401046', 'Raysad Radana', 'XII-RPL-2', 0, 0),
(42, '232401047', 'Rehan Ramadan', 'XII-RPL-2', 0, 0),
(43, '232401049', 'Rendi Maulana', 'XII-RPL-2', 0, 0),
(44, '232401048', 'Resta Septiani', 'XII-RPL-2', 0, 0),
(45, '232401050', 'Rian Saputra', 'XII-RPL-2', 0, 0),
(46, '232401052', 'Sahilmi', 'XII-RPL-2', 0, 0),
(47, '232401053', 'Satria Nugraha Tri Wiguna', 'XII-RPL-2', 0, 0),
(48, '232401054', 'Virzy Pratama', 'XII-RPL-2', 0, 0),
(99, '1234', 'Aditya', 'XII-RPL-1', 0, 0),
(100, '3333', 'ASEP SUKANDAR', 'XII-AKL-1', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tindaklanjut`
--

CREATE TABLE `tindaklanjut` (
  `id_tindak` int(10) NOT NULL,
  `id_siswa` int(10) NOT NULL,
  `id_users` int(10) DEFAULT NULL,
  `id_periode` int(11) DEFAULT NULL,
  `ketegoriRP` varchar(25) NOT NULL,
  `tindaklanjut` text DEFAULT NULL,
  `poin` varchar(25) DEFAULT NULL,
  `foto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tindaklanjut`
--

INSERT INTO `tindaklanjut` (`id_tindak`, `id_siswa`, `id_users`, `id_periode`, `ketegoriRP`, `tindaklanjut`, `poin`, `foto`) VALUES
(2, 12, 1, 1, 'Reward', 'Tindakan/Apresiasi: Piagam penghargaan + hadiah pembinaan\n\nTindak Lanjut: pemberian mendali', '20', 'uploads/tindaklanjut/tindak_12_1786788024_6a8038b806102.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('Admin','Guru','BK','Kepsek') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', 'ea496d90f59f981936f0fea80eab1c00', 'Asep_Sukandar', 'Admin'),
(2, 'guru', '96cb866adb6dbd384ba9ba2d0814266b', 'Budi Utomo, S.Pd', 'Guru'),
(3, 'kesiswaan', 'accc7841ce41b0f788a737bf9798ea4f', 'Siti Aminah, S.Psi', 'BK'),
(4, 'andriansyah', '96cb866adb6dbd384ba9ba2d0814266b', 'Andriansyah, SE', 'Guru'),
(5, 'annisarizkimubarok', '96cb866adb6dbd384ba9ba2d0814266b', 'Annisa Rizki Mubarok, S.Ak', 'Guru'),
(6, 'arisupriatna', '96cb866adb6dbd384ba9ba2d0814266b', 'Ari Supriatna, A.Md.T', 'Guru'),
(7, 'asepsukandar', '96cb866adb6dbd384ba9ba2d0814266b', 'Asep Sukandar, S.Kom', 'Guru'),
(8, 'endangmuhtadin', '96cb866adb6dbd384ba9ba2d0814266b', 'Endang Muhtadin, S.Kom', 'Guru'),
(9, 'hermanto', '96cb866adb6dbd384ba9ba2d0814266b', 'Hermanto, M.Pd', 'Guru'),
(10, 'irwankurniawan', '8561863b55faf85b9ad67c52b3b851ac', 'Irwan Kurniawan, ST., Gr.', 'Kepsek'),
(11, 'jejenjaelani', '96cb866adb6dbd384ba9ba2d0814266b', 'Jejen Jaelani, S.Pd., Gr', 'Guru'),
(12, 'kemalhermawan', '96cb866adb6dbd384ba9ba2d0814266b', 'Kemal Hermawan, S.Pd., Gr.', 'Guru'),
(13, 'khudaenimuphlian', '96cb866adb6dbd384ba9ba2d0814266b', 'Khudaeni Muphlian, MM.Pd', 'Guru'),
(14, 'maryam', '96cb866adb6dbd384ba9ba2d0814266b', 'Maryam, S.Pd., Gr.', 'Guru'),
(15, 'rifkiasaputra', '96cb866adb6dbd384ba9ba2d0814266b', 'Rifkia Saputra, S.Tr.T., Gr., M.Pd', 'Guru'),
(16, 'maulynurkharimah', '96cb866adb6dbd384ba9ba2d0814266b', 'Mauly Nurkharimah, S.Ak,. Gr', 'Guru'),
(17, 'jamaludin', '7e7ec59d1f4b21021577ff562dc3d48b', 'Muhamad Jamalludin, S.Pd', 'BK'),
(18, 'nengsulastri', '96cb866adb6dbd384ba9ba2d0814266b', 'Neng Sulastri, S.Pd., Gr', 'Guru'),
(19, 'nurulanisa', '96cb866adb6dbd384ba9ba2d0814266b', 'Nurul Anisa, S.Pd., Gr', 'Guru'),
(20, 'oviendaokvirianaputri', '96cb866adb6dbd384ba9ba2d0814266b', 'Ovienda Okviriana Putri, ST., Gr', 'Guru'),
(21, 'rudihartono', '96cb866adb6dbd384ba9ba2d0814266b', 'Rudi Hartono, S.Pd.', 'Guru'),
(22, 'samsudin', '96cb866adb6dbd384ba9ba2d0814266b', 'Samsudin, S.Pd., Gr.', 'Guru'),
(23, 'sitisarahnurseptiani', '96cb866adb6dbd384ba9ba2d0814266b', 'Siti Sarah Nurseptiani, S.Pd., Gr', 'Guru'),
(24, 'trisnosrihartono', '96cb866adb6dbd384ba9ba2d0814266b', 'Trisno Sri Hartono, S.Pd.', 'Guru'),
(25, 'ujangderi', '96cb866adb6dbd384ba9ba2d0814266b', 'Ujang Deri, S.Pd.I., Gr', 'Guru'),
(26, 'riyansyah', '96cb866adb6dbd384ba9ba2d0814266b', 'Riyansyah, S.Pd., Gr', 'Guru'),
(27, 'sopiansaputra', '96cb866adb6dbd384ba9ba2d0814266b', 'Sopian Saputra, S.Pd., Gr.', 'Guru');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dataset_training`
--
ALTER TABLE `dataset_training`
  ADD PRIMARY KEY (`id_data`);

--
-- Indexes for table `evaluasi_siswa`
--
ALTER TABLE `evaluasi_siswa`
  ADD PRIMARY KEY (`id_evaluasi`);

--
-- Indexes for table `laporan_prilaku`
--
ALTER TABLE `laporan_prilaku`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_siswa` (`id_siswa`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `idx_laporan_periode` (`id_periode`);

--
-- Indexes for table `master_poin`
--
ALTER TABLE `master_poin`
  ADD PRIMARY KEY (`id_aturan`);

--
-- Indexes for table `periode_akademik`
--
ALTER TABLE `periode_akademik`
  ADD PRIMARY KEY (`id_periode`),
  ADD UNIQUE KEY `uniq_tahun_ajaran` (`tahun_ajaran`);

--
-- Indexes for table `rekap_poin_periode`
--
ALTER TABLE `rekap_poin_periode`
  ADD PRIMARY KEY (`id_rekap`),
  ADD UNIQUE KEY `uniq_rekap_periode_siswa` (`id_periode`,`id_siswa`),
  ADD KEY `idx_rekap_periode` (`id_periode`);

--
-- Indexes for table `remisi`
--
ALTER TABLE `remisi`
  ADD PRIMARY KEY (`id_remisi`),
  ADD KEY `id_siswa` (`id_siswa`,`id_user`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `idx_remisi_periode` (`id_periode`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nis` (`nis`);

--
-- Indexes for table `tindaklanjut`
--
ALTER TABLE `tindaklanjut`
  ADD PRIMARY KEY (`id_tindak`),
  ADD KEY `id_siswa` (`id_siswa`),
  ADD KEY `id_users` (`id_users`),
  ADD KEY `idx_tindaklanjut_periode` (`id_periode`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dataset_training`
--
ALTER TABLE `dataset_training`
  MODIFY `id_data` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=738;

--
-- AUTO_INCREMENT for table `evaluasi_siswa`
--
ALTER TABLE `evaluasi_siswa`
  MODIFY `id_evaluasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `laporan_prilaku`
--
ALTER TABLE `laporan_prilaku`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT for table `master_poin`
--
ALTER TABLE `master_poin`
  MODIFY `id_aturan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=292;

--
-- AUTO_INCREMENT for table `periode_akademik`
--
ALTER TABLE `periode_akademik`
  MODIFY `id_periode` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rekap_poin_periode`
--
ALTER TABLE `rekap_poin_periode`
  MODIFY `id_rekap` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `remisi`
--
ALTER TABLE `remisi`
  MODIFY `id_remisi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `tindaklanjut`
--
ALTER TABLE `tindaklanjut`
  MODIFY `id_tindak` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `laporan_prilaku`
--
ALTER TABLE `laporan_prilaku`
  ADD CONSTRAINT `laporan_prilaku_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`),
  ADD CONSTRAINT `laporan_prilaku_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `remisi`
--
ALTER TABLE `remisi`
  ADD CONSTRAINT `remisi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `remisi_ibfk_2` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tindaklanjut`
--
ALTER TABLE `tindaklanjut`
  ADD CONSTRAINT `tindaklanjut_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tindaklanjut_ibfk_2` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
