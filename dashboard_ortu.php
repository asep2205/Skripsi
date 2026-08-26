<?php 
// 1. Memulai session
session_start();

// Proteksi Halaman: Jika belum login atau bukan Orang Tua, tendang kembali ke halaman login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Orang Tua') {
    header("Location: login_ortu");
    exit();
}

// 2. INCLUDE KONEKSI DATABASE (Menggunakan file koneksi.php Anda)
include 'koneksi.php'; 

// Ambil ID Siswa dari session yang aktif saat login
$id_siswa_session = $_SESSION['id_siswa'];

// 3. QUERY AMBIL DATA SISWA LANGSUNG DARI TABEL `siswa`
$id_siswa_clean = mysqli_real_escape_string($koneksi, $id_siswa_session);
$query_siswa    = mysqli_query($koneksi, "SELECT nama_siswa, kelas, nis, total_poin_reward, total_poin_punishment FROM siswa WHERE id_siswa = '$id_siswa_clean'");
$siswa          = mysqli_fetch_assoc($query_siswa);

// Jika data siswa tidak ditemukan di database
if (!$siswa) {
    echo "Data siswa tidak ditemukan.";
    exit();
}

// Memindahkan nilai poin ke variabel lokal
$total_poin_reward     = $siswa['total_poin_reward'];
$total_poin_punishment = $siswa['total_poin_punishment'];
$kelas_siswa           = mysqli_real_escape_string($koneksi, $siswa['kelas']);

// =========================================================================
// LOGIKA AMBALAN: HITUNG PERINGKAT BERDASARKAN KASUS KELAS YANG SAMA
// =========================================================================
// 1. Hitung Peringkat Reward di Kelasnya
$q_rank_reward = mysqli_query($koneksi, "SELECT COUNT(*) + 1 AS rank FROM siswa WHERE kelas = '$kelas_siswa' AND total_poin_reward > '$total_poin_reward'");
$row_rank_reward = mysqli_fetch_assoc($q_rank_reward);
$rank_reward = $row_rank_reward['rank'];

// 2. Hitung Peringkat Punishment di Kelasnya
$q_rank_punish = mysqli_query($koneksi, "SELECT COUNT(*) + 1 AS rank FROM siswa WHERE kelas = '$kelas_siswa' AND total_poin_punishment > '$total_poin_punishment'");
$row_rank_punish = mysqli_fetch_assoc($q_rank_punish);
$rank_punish = $row_rank_punish['rank'];

// Hitung total siswa di kelas tersebut sebagai pembanding
$q_total_kelas = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM siswa WHERE kelas = '$kelas_siswa'");
$row_total_kelas = mysqli_fetch_assoc($q_total_kelas);
$total_siswa_sekelas = $row_total_kelas['total'];


// =========================================================================
// LOGIKA BARU: HITUNG PERINGKAT GLOBAL (DARI SELURUH KELAS / SEKOLAH)
// =========================================================================
// 1. Hitung Peringkat Reward di Seluruh Sekolah
$q_rank_reward_global = mysqli_query($koneksi, "SELECT COUNT(*) + 1 AS rank FROM siswa WHERE total_poin_reward > '$total_poin_reward'");
$row_rank_reward_global = mysqli_fetch_assoc($q_rank_reward_global);
$rank_reward_global = $row_rank_reward_global['rank'];

// 2. Hitung Peringkat Punishment di Seluruh Sekolah
$q_rank_punish_global = mysqli_query($koneksi, "SELECT COUNT(*) + 1 AS rank FROM siswa WHERE total_poin_punishment > '$total_poin_punishment'");
$row_rank_punish_global = mysqli_fetch_assoc($q_rank_punish_global);
$rank_punish_global = $row_rank_punish_global['rank'];

// Hitung total seluruh siswa di sekolah (semua kelas)
$q_total_global = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM siswa");
$row_total_global = mysqli_fetch_assoc($q_total_global);
$total_siswa_global = $row_total_global['total'];
// =========================================================================


// 4. QUERY AMBIL LOG JURNAL PERILAKU DARI TABEL laporan_prilaku (PERBAIKAN ERROR)
// Disesuaikan menjadi "Daftar Riwayat NLP Terbaru (Maks. 5 Data)" -> ditambah kolom foto & LIMIT 5
$query_jurnal = mysqli_query($koneksi, "SELECT tgl_input, teks_laporan, label_prediksi, kecocokan_kata, akurasi_map, poin_didapat, foto FROM laporan_prilaku WHERE id_siswa = '$id_siswa_clean' ORDER BY tgl_input DESC LIMIT 5");

// =========================================================================
// [BARU] QUERY DAFTAR RIWAYAT REMISI TERBARU (Maks. 5 Data) UNTUK SISWA INI
// =========================================================================
$query_remisi = mysqli_query($koneksi, "SELECT * FROM remisi WHERE id_siswa = '$id_siswa_clean' ORDER BY id_remisi DESC LIMIT 5");

// =========================================================================
// [BARU] QUERY DAFTAR LAPORAN TINDAK LANJUT TERBARU (Maks. 5 Data) UNTUK SISWA INI
// Catatan: tabel `tindaklanjut` belum punya kolom tanggal, jadi diurutkan
// berdasarkan id_tindak DESC (id terbesar = data yang terakhir dimasukkan).
// =========================================================================
$query_tindaklanjut = mysqli_query($koneksi, "SELECT * FROM tindaklanjut WHERE id_siswa = '$id_siswa_clean' ORDER BY id_tindak DESC LIMIT 5");

// 5. Logika Penentuan Level Reward
$status_reward = "Belum mencapai ambang penghargaan dasar (Min. 20 Poin).";
if ($total_poin_reward >= 250) {
    $status_reward = "Siswa Terbaik (Piagam penghargaan + hadiah khusus / penghargaan dari kepala sekolah)";
} elseif ($total_poin_reward >= 200) {
    $status_reward = "Siswa Inspiratif (Piagam penghargaan + rekomendasi mewakili sekolah dalam kegiatan/lomba)";
} elseif ($total_poin_reward >= 150) {
    $status_reward = "Siswa Unggul (Piagam penghargaan + bantuan perlengkapan sekolah)";
} elseif ($total_poin_reward >= 100) {
    $status_reward = "Siswa Berprestasi Tinggi (Piagam penghargaan + piala / medali)";
} elseif ($total_poin_reward >= 80) {
    $status_reward = "Siswa Teladan (Piagam penghargaan + hadiah pembinaan)";
} elseif ($total_poin_reward >= 60) {
    $status_reward = "Siswa Berprestasi (Piagam penghargaan + alat tulis / perlengkapan sekolah)";
} elseif ($total_poin_reward >= 40) {
    $status_reward = "Siswa Aktif (Piagam penghargaan dari sekolah)";
} elseif ($total_poin_reward >= 20) {
    $status_reward = "Penghargaan Dasar (Sertifikat apresiasi dari wali kelas)";
}

// 6. Logika Penentuan Tindakan Berdasarkan Poin Punishment
$status_tindakan = "Perilaku Baik / Belum ada tindakan kedisiplinan khusus.";
if ($total_poin_punishment >= 100) {
    $status_tindakan = "Dikembangkan kepada Orang Tua / Dikeluarkan dari Sekolah.";
} elseif ($total_poin_punishment >= 75) {
    $status_tindakan = "Skorsing selama 3 hari + Surat Perjanjian di atas materai.";
} elseif ($total_poin_punishment >= 50) {
    $status_tindakan = "Pemanggilan Orang Tua ke sekolah oleh Kepala Kompetensi Keahlian.";
} elseif ($total_poin_punishment >= 30) {
    $status_tindakan = "Peringatan keras tertulis + Pemanggilan Orang Tua oleh Wali Kelas & BK.";
} elseif ($total_poin_punishment >= 15) {
    $status_tindakan = "Teguran lisan & pembinaan langsung oleh Guru BK.";
}

// =========================================================================
// LOGIKA TAMBAHAN: QUERY JUMLAH LAPORAN UNTUK GRAFIK (HARI, BULAN, SEMESTER, TAHUN)
// =========================================================================

// A. HARI (7 Hari Terakhir)
$chart_hari_labels = []; $chart_hari_reward = []; $chart_hari_punish = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_hari_labels[] = date('d M', strtotime($date));
    
    $q_r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM laporan_prilaku WHERE id_siswa = '$id_siswa_clean' AND label_prediksi = 'Reward' AND DATE(tgl_input) = '$date'"));
    $q_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM laporan_prilaku WHERE id_siswa = '$id_siswa_clean' AND label_prediksi != 'Reward' AND DATE(tgl_input) = '$date'"));
    
    $chart_hari_reward[] = $q_r['total'] ?? 0;
    $chart_hari_punish[] = $q_p['total'] ?? 0;
}

// B. BULAN (6 Bulan Terakhir)
$chart_bulan_labels = []; $chart_bulan_reward = []; $chart_bulan_punish = [];
for ($i = 5; $i >= 0; $i--) {
    $month_year = date('Y-m', strtotime("-$i months"));
    $chart_bulan_labels[] = date('M Y', strtotime($month_year . "-01"));
    
    $q_r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM laporan_prilaku WHERE id_siswa = '$id_siswa_clean' AND label_prediksi = 'Reward' AND DATE_FORMAT(tgl_input, '%Y-%m') = '$month_year'"));
    $q_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM laporan_prilaku WHERE id_siswa = '$id_siswa_clean' AND label_prediksi != 'Reward' AND DATE_FORMAT(tgl_input, '%Y-%m') = '$month_year'"));
    
    $chart_bulan_reward[] = $q_r['total'] ?? 0;
    $chart_bulan_punish[] = $q_p['total'] ?? 0;
}

// C. SEMESTER (2 Semester Terakhir berdasarkan pembagian bulan Jan-Jun & Jul-Des)
$chart_sem_labels = []; $chart_sem_reward = []; $chart_sem_punish = [];
for ($i = 1; $i >= 0; $i--) {
    $target_time = strtotime("-$i semesters");
    $year = date('Y', $target_time);
    $month = date('n', $target_time);
    
    if ($month <= 6) {
        $sem_name = "Genap " . ($year-1) . "/$year";
        $months_range = "('01','02','03','04','05','06')";
    } else {
        $sem_name = "Ganjil $year/" . ($year+1);
        $months_range = "('07','08','09','10','11','12')";
    }
    $chart_sem_labels[] = $sem_name;
    
    $q_r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM laporan_prilaku WHERE id_siswa = '$id_siswa_clean' AND label_prediksi = 'Reward' AND YEAR(tgl_input) = '$year' AND DATE_FORMAT(tgl_input, '%m') IN $months_range"));
    $q_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM laporan_prilaku WHERE id_siswa = '$id_siswa_clean' AND label_prediksi != 'Reward' AND YEAR(tgl_input) = '$year' AND DATE_FORMAT(tgl_input, '%m') IN $months_range"));
    
    $chart_sem_reward[] = $q_r['total'] ?? 0;
    $chart_sem_punish[] = $q_p['total'] ?? 0;
}

// D. TAHUN (3 Tahun Terakhir)
$chart_tahun_labels = []; $chart_tahun_reward = []; $chart_tahun_punish = [];
for ($i = 2; $i >= 0; $i--) {
    $year = date('Y', strtotime("-$i years"));
    $chart_tahun_labels[] = $year;
    
    $q_r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM laporan_prilaku WHERE id_siswa = '$id_siswa_clean' AND label_prediksi = 'Reward' AND YEAR(tgl_input) = '$year'"));
    $q_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM laporan_prilaku WHERE id_siswa = '$id_siswa_clean' AND label_prediksi != 'Reward' AND YEAR(tgl_input) = '$year'"));
    
    $chart_tahun_reward[] = $q_r['total'] ?? 0;
    $chart_tahun_punish[] = $q_p['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Monitoring Orang Tua - SMKS Doa Bangsa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card-custom:hover { transform: translateY(-3px); }
        .bg-reward-card { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
        .bg-punish-card { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; }
        .avatar-circle { width: 50px; height: 50px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .table-responsive { border-radius: 12px; overflow: hidden; }

        .foto-bukti-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 2px solid #e2e8f0; cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease; }
        .foto-bukti-thumb:hover { transform: scale(1.08); border-color: #f59e0b; box-shadow: 0 4px 10px rgba(245,158,11,0.25); }

        @media (max-width: 767.98px) {
            .table-responsive-mobile thead { display: none; }
            .table-responsive-mobile tr { display: block; background: #fff; margin-bottom: 12px; border: 1px solid #dee2e6; border-radius: 10px; padding: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
            .table-responsive-mobile td { display: flex; justify-content: space-between; align-items: center; text-align: right; padding: 6px 4px; border-bottom: 1px solid #f1f1f1; font-size: 13px; }
            .table-responsive-mobile td::before { content: attr(data-label); font-weight: bold; text-align: left; float: left; color: #495057; }
            .table-responsive-mobile td:last-child { border-bottom: 0; }
            .table-responsive-mobile td.text-center { text-align: right !important; }
        }
        
        /* Nav tabs styling untuk grafik */
        .nav-pills-custom .nav-link { color: #495057; font-weight: 500; border-radius: 8px; padding: 6px 16px; font-size: 14px; }
        .nav-pills-custom .nav-link.active { background-color: #f59e0b; color: white !important; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom py-3 mb-3">
    <div class="container d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
        <a class="navbar-brand fw-bold text-white d-flex align-items-center m-0" href="#" style="font-size: 16px;">
            <i class="bi bi-people-fill me-2"></i> PORTAL MONITORING ORANG TUA
        </a>
        <div class="d-flex align-items-center justify-content-center gap-2">
            <span class="text-white small d-none d-md-inline">Wali dari <strong><?= htmlspecialchars($siswa['nama_siswa']); ?></strong></span>
            <a href="javascript:void(0);" id="btn-logout" class="btn btn-light btn-sm rounded-pill fw-semibold text-danger px-3 shadow-sm">Keluar</a>
        </div>
    </div>
</nav>

<div class="container mb-5 px-2 px-sm-3">
    
    <div class="card card-custom mb-3 p-1">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <div>
                <span class="badge bg-warning text-dark mb-1 fw-bold" style="font-size: 10px;">PROFIL SISWA TERVERIFIKASI</span>
                <h3 class="fw-bold text-dark mb-0 fs-4"><?= htmlspecialchars($siswa['nama_siswa']); ?></h3>
                <p class="text-muted mb-1 small">Kelas: <strong class="text-dark"><?= htmlspecialchars($siswa['kelas']); ?></strong> | NIS: <strong class="text-dark"><?= htmlspecialchars($siswa['nis']); ?></strong></p>
                
                <div class="d-flex flex-column gap-2 mt-2">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-2 px-2 py-1 small fw-medium">
                            <i class="bi bi-award-fill me-1"></i> Peringkat Kelas (Reward): Ke-<?= $rank_reward; ?> dari <?= $total_siswa_sekelas; ?> Siswa
                        </span>
                        <span class="badge bg-success text-white rounded-2 px-2 py-1 small fw-medium">
                            <i class="bi bi-globe me-1"></i> Peringkat Umum Sekolah: Ke-<?= $rank_reward_global; ?> dari <?= $total_siswa_global; ?> Siswa
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-2 px-2 py-1 small fw-medium">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Tingkat Pelanggaran Kelas: Ke-<?= $rank_punish; ?> dari <?= $total_siswa_sekelas; ?> Siswa
                        </span>
                        <span class="badge bg-danger text-white rounded-2 px-2 py-1 small fw-medium">
                            <i class="bi bi-shield-exclamation me-1"></i> Tingkat Pelanggaran Sekolah: Ke-<?= $rank_punish_global; ?> dari <?= $total_siswa_global; ?> Siswa
                        </span>
                    </div>
                </div>
            </div>
            <div class="bg-light p-2 rounded-3 mt-1 mt-md-0 text-md-end">
                <span class="fw-bold text-primary small"><i class="bi bi-mortarboard-fill me-1"></i> SMKS Doa Bangsa</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <?php 
        $max_reward = 300;
        $max_punishment = 100;

        $persen_reward = ($total_poin_reward / $max_reward) * 100;
        $persen_reward = $persen_reward > 100 ? 100 : ($persen_reward < 0 ? 0 : $persen_reward);

        $persen_punishment = ($total_poin_punishment / $max_punishment) * 100;
        $persen_punishment = $persen_punishment > 100 ? 100 : ($persen_punishment < 0 ? 0 : $persen_punishment);
        ?>

        <div class="col-12 col-md-6">
            <div class="card card-custom bg-reward-card p-2 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="w-100">
                        <h6 class="text-white-50 fw-semibold small mb-1">Total Poin Penghargaan (Reward)</h6>
                        <h2 class="fw-bold mb-2 fs-1">
                            <?= $total_poin_reward; ?> <span style="font-size: 14px; font-weight: normal; opacity: 0.85;">/ <?= $max_reward; ?> Maks Poin</span>
                        </h2>
                        
                        <div class="progress bg-white bg-opacity-25 mb-3" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: <?= $persen_reward; ?>%; border-radius: 4px;" aria-valuenow="<?= $persen_reward; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <p class="mb-0 small bg-white bg-opacity-20 p-2 rounded-2" style="font-size: 11px; line-height: 1.4;">
                            <i class="bi bi-trophy-fill me-1"></i> <strong>Apresiasi:</strong><br><?= $status_reward; ?>
                        </p>
                    </div>
                    <div class="avatar-circle ms-2 d-none d-sm-flex"><i class="bi bi-star-fill"></i></div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card card-custom bg-punish-card p-2 h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="w-100">
                        <h6 class="text-white-50 fw-semibold small mb-1">Total Poin Pelanggaran (Punishment)</h6>
                        <h2 class="fw-bold mb-2 fs-1">
                            <?= $total_poin_punishment; ?> <span style="font-size: 14px; font-weight: normal; opacity: 0.85;">/ <?= $max_punishment; ?> Maks Poin</span>
                        </h2>
                        
                        <div class="progress bg-white bg-opacity-25 mb-3" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: <?= $persen_punishment; ?>%; border-radius: 4px;" aria-valuenow="<?= $persen_punishment; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <p class="mb-0 small bg-white bg-opacity-20 p-2 rounded-2" style="font-size: 11px; line-height: 1.4;">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> <strong>Konsekuensi:</strong><br><?= htmlspecialchars($status_tindakan); ?>
                        </p>
                    </div>
                    <div class="avatar-circle ms-2 d-none d-sm-flex"><i class="bi bi-cone-striped"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
    BAGIAN BARU: GRAFIK MONITORING FREKUENSI LAPORAN PERILAKU SISWA
    ========================================================================= -->
    <div class="card card-custom mb-4">
        <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-line-fill me-2 text-warning"></i> Grafik Frekuensi Laporan Perilaku</h6>
            <ul class="nav nav-pills nav-pills-custom gap-1" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-hari-tab" data-bs-toggle="pill" data-bs-target="#pills-hari" type="button" role="tab">Hari</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-bulan-tab" data-bs-toggle="pill" data-bs-target="#pills-bulan" type="button" role="tab">Bulan</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-semester-tab" data-bs-toggle="pill" data-bs-target="#pills-semester" type="button" role="tab">Semester</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-tahun-tab" data-bs-toggle="pill" data-bs-target="#pills-tahun" type="button" role="tab">Tahun</button>
                </li>
            </ul>
        </div>
        <div class="card-body p-3">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-hari" role="tabpanel">
                    <div style="position: relative; height:280px; width:100%;">
                        <canvas id="chartHari"></canvas>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-bulan" role="tabpanel">
                    <div style="position: relative; height:280px; width:100%;">
                        <canvas id="chartBulan"></canvas>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-semester" role="tabpanel">
                    <div style="position: relative; height:280px; width:100%;">
                        <canvas id="chartSemester"></canvas>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-tahun" role="tabpanel">
                    <div style="position: relative; height:280px; width:100%;">
                        <canvas id="chartTahun"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ========================================================================= -->

    <div class="card card-custom mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-text me-2 text-warning"></i> Daftar Riwayat NLP Terbaru (Maks. 5 Data)</h6>
        </div>
        <div class="card-body p-1 p-sm-3">
            <div class="table-responsive" style="border:none;">
                <table class="table table-hover table-striped mb-0 align-middle table-responsive-mobile">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3" style="width: 5%">No</th>
                            <th style="width: 14%">Tanggal Input</th>
                            <th style="width: 27%">Teks Laporan (Guru)</th>
                            <th class="text-center" style="width: 10%">Foto Bukti</th>
                            <th class="text-center" style="width: 12%">Label Prediksi</th>
                            <th style="width: 20%">Kecocokan Kata (Aturan NLP)</th>
                            <th class="text-center" style="width: 6%">Akurasi</th>
                            <th class="text-center ps-3" style="width: 6%">Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($query_jurnal && mysqli_num_rows($query_jurnal) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($query_jurnal)) {
                                if ($row['label_prediksi'] == 'Reward') {
                                    $badge_class = 'bg-success-subtle text-success border border-success-subtle';
                                    $poin_icon = '<span class="text-success fw-bold">+' . $row['poin_didapat'] . '</span>';
                                } else {
                                    $badge_class = 'bg-danger-subtle text-danger border border-danger-subtle';
                                    $poin_icon = '<span class="text-danger fw-bold">-' . $row['poin_didapat'] . '</span>';
                                }
                            ?>
                                <tr>
                                    <td data-label="No" class="ps-3"><?= $no++; ?></td>
                                    <td data-label="Tanggal Input"><?= date('d M Y, H:i', strtotime($row['tgl_input'])); ?> WIB</td>
                                    <td data-label="Laporan" class="fst-italic text-secondary text-md-start">"<?= htmlspecialchars($row['teks_laporan']); ?>"</td>
                                    <td data-label="Foto Bukti" class="text-center">
                                        <?php if (!empty($row['foto'])): ?>
                                            <img src="<?= htmlspecialchars($row['foto']); ?>"
                                                 alt="Foto Bukti Laporan"
                                                 class="foto-bukti-thumb"
                                                 onclick="bukaZoomFoto('<?= htmlspecialchars($row['foto'], ENT_QUOTES); ?>')">
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Kategori" class="text-center">
                                        <span class="badge rounded-pill <?= $badge_class; ?> px-2 py-1 fw-semibold" style="font-size: 11px;">
                                            <?= $row['label_prediksi']; ?>
                                        </span>
                                    </td>
                                    <td data-label="Aturan NLP"><small class="text-dark fw-medium"><?= htmlspecialchars($row['kecocokan_kata']); ?></small></td>
                                    <td data-label="Akurasi" class="text-center"><span class="badge bg-light text-dark border"><?= $row['akurasi_map']; ?></span></td>
                                    <td data-label="Perubahan Poin" class="text-center fs-5 fw-bold"><?= $poin_icon; ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada catatan riwayat NLP untuk siswa ini.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-custom mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-heart-pulse-fill me-2 text-success"></i> Daftar Riwayat Remisi Terbaru (Maks. 5 Data)</h6>
        </div>
        <div class="card-body p-1 p-sm-3">
            <div class="table-responsive" style="border:none;">
                <table class="table table-hover table-striped mb-0 align-middle table-responsive-mobile">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3" style="width: 5%">No</th>
                            <th style="width: 15%">Tanggal & Jam</th>
                            <th style="width: 40%">Keterangan / Aksi Remisi</th>
                            <th class="text-center" style="width: 15%">Foto Bukti</th>
                            <th class="text-center ps-3" style="width: 25%">Poin Pengurangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($query_remisi && mysqli_num_rows($query_remisi) > 0) {
                            $no_r = 1;
                            while ($row_r = mysqli_fetch_assoc($query_remisi)) {
                                // Asumsi nama kolom tanggal di tabel remisi adalah tgl_remisi, sesuaikan jika berbeda
                                $tgl_remisi = isset($row_r['tgl_remisi']) ? $row_r['tgl_remisi'] : null;
                        ?>
                                <tr>
                                    <td data-label="No" class="ps-3"><?= $no_r++; ?></td>
                                    <td data-label="Tanggal & Jam">
                                        <?= $tgl_remisi ? date('d M Y, H:i', strtotime($tgl_remisi)) . ' WIB' : '-'; ?>
                                    </td>
                                    <td data-label="Keterangan" class="text-secondary text-md-start"><?= htmlspecialchars($row_r['keterangan'] ?? '-'); ?></td>
                                    <td data-label="Foto Bukti" class="text-center">
                                        <?php if (!empty($row_r['foto'])): ?>
                                            <img src="<?= htmlspecialchars($row_r['foto']); ?>"
                                                 alt="Foto Bukti Remisi"
                                                 class="foto-bukti-thumb"
                                                 onclick="bukaZoomFoto('<?= htmlspecialchars($row_r['foto'], ENT_QUOTES); ?>')">
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Poin" class="text-center">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold font-monospace">
                                            -<?= $row_r['poin_remisi'] ?? 0; ?> Poin
                                        </span>
                                    </td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat remisi untuk siswa ini.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-custom mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clipboard2-pulse-fill me-2 text-primary"></i> Daftar Laporan Tindak Lanjut Terbaru (Maks. 5 Data)</h6>
        </div>
        <div class="card-body p-1 p-sm-3">
            <small class="text-muted d-block px-2 px-sm-0 mb-2">Catatan: data ini belum memiliki kolom tanggal, sehingga urutan mengikuti data yang terakhir dicatat.</small>
            <div class="table-responsive" style="border:none;">
                <table class="table table-hover table-striped mb-0 align-middle table-responsive-mobile">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3" style="width: 5%">No</th>
                            <th class="text-center" style="width: 12%">Kategori</th>
                            <th style="width: 43%">Deskripsi Tindak Lanjut</th>
                            <th class="text-center" style="width: 15%">Foto Bukti</th>
                            <th class="text-center ps-3" style="width: 25%">Poin Dikurangi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($query_tindaklanjut && mysqli_num_rows($query_tindaklanjut) > 0) {
                            $no_tl = 1;
                            while ($row_tl = mysqli_fetch_assoc($query_tindaklanjut)) {
                                $is_reward_tl = (strtolower($row_tl['ketegoriRP']) == 'reward');
                                $badge_class_tl = $is_reward_tl ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle';
                                $poin_badge_tl = $is_reward_tl ? 'text-success' : 'text-danger';
                                $poin_sign_tl = $is_reward_tl ? '+' : '-';
                        ?>
                                <tr>
                                    <td data-label="No" class="ps-3"><?= $no_tl++; ?></td>
                                    <td data-label="Kategori" class="text-center">
                                        <span class="badge rounded-pill <?= $badge_class_tl; ?> border px-2 py-1 fw-semibold" style="font-size: 11px;">
                                            <?= strtoupper($row_tl['ketegoriRP']); ?>
                                        </span>
                                    </td>
                                    <td data-label="Deskripsi" class="text-secondary text-md-start" style="white-space: pre-line;"><?= htmlspecialchars($row_tl['tindaklanjut']); ?></td>
                                    <td data-label="Foto Bukti" class="text-center">
                                        <?php if (!empty($row_tl['foto'])): ?>
                                            <img src="<?= htmlspecialchars($row_tl['foto']); ?>"
                                                 alt="Foto Bukti Tindak Lanjut"
                                                 class="foto-bukti-thumb"
                                                 onclick="bukaZoomFoto('<?= htmlspecialchars($row_tl['foto'], ENT_QUOTES); ?>')">
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Poin" class="text-center fs-5 fw-bold <?= $poin_badge_tl; ?>"><?= $poin_sign_tl . $row_tl['poin']; ?></td>
                                </tr>
                        <?php }
                        } else { ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada laporan tindak lanjut untuk siswa ini.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Zoom Foto Bukti -->
<div class="modal fade" id="modalZoomFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-white small"><i class="bi bi-camera-fill me-2"></i>Foto Bukti</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="imgZoomFoto" src="" alt="Foto Bukti Diperbesar" class="img-fluid rounded-3" style="max-height: 75vh; width: auto;">
            </div>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Tambahan Library Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Fungsi buka modal zoom untuk foto bukti (NLP, Remisi, Tindak Lanjut)
    window.bukaZoomFoto = function(src) {
        document.getElementById('imgZoomFoto').src = src;
        const modalZoom = new bootstrap.Modal(document.getElementById('modalZoomFoto'));
        modalZoom.show();
    };

    <?php if(isset($_SESSION['swal_sukses'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Akses Diberikan!',
            text: '<?= $_SESSION['swal_sukses']; ?>',
            timer: 3500,
            showConfirmButton: false
        });
    <?php unset($_SESSION['swal_sukses']); endif; ?>

    const btnLogout = document.getElementById('btn-logout');
    if(btnLogout) {
        btnLogout.addEventListener('click', function() {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Sesi monitoring daring Anda akan disudahi.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "logout_ortu";
                }
            });
        });
    }

    // =========================================================================
    // KONFIGURASI GENERATE GRAFIK FREKUENSI LAPORAN (BARU)
    // =========================================================================
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { 
                beginAtZero: true, 
                grid: { color: '#f1f1f1' },
                ticks: {
                    stepSize: 1, // Memaksa angka integer bulat (karena jumlah laporan tidak ada desimal)
                    callback: function(value) { if (value % 1 === 0) { return value; } }
                }
            },
            x: { grid: { display: false } }
        },
        plugins: {
            legend: { position: 'top', labels: { boxWidth: 12, font: { family: 'Segoe UI' } } },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw + ' Laporan';
                    }
                }
            }
        }
    };

    // 1. Chart Hari
    new Chart(document.getElementById('chartHari'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_hari_labels); ?>,
            datasets: [
                { label: 'Laporan Positif (Reward)', data: <?= json_encode($chart_hari_reward); ?>, backgroundColor: '#10b981' },
                { label: 'Laporan Negatif (Punishment)', data: <?= json_encode($chart_hari_punish); ?>, backgroundColor: '#ef4444' }
            ]
        },
        options: commonOptions
    });

    // 2. Chart Bulan
    new Chart(document.getElementById('chartBulan'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_bulan_labels); ?>,
            datasets: [
                { label: 'Laporan Positif (Reward)', data: <?= json_encode($chart_bulan_reward); ?>, backgroundColor: '#10b981' },
                { label: 'Laporan Negatif (Punishment)', data: <?= json_encode($chart_bulan_punish); ?>, backgroundColor: '#ef4444' }
            ]
        },
        options: commonOptions
    });

    // 3. Chart Semester
    new Chart(document.getElementById('chartSemester'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_sem_labels); ?>,
            datasets: [
                { label: 'Laporan Positif (Reward)', data: <?= json_encode($chart_sem_reward); ?>, backgroundColor: '#10b981' },
                { label: 'Laporan Negatif (Punishment)', data: <?= json_encode($chart_sem_punish); ?>, backgroundColor: '#ef4444' }
            ]
        },
        options: commonOptions
    });

    // 4. Chart Tahun
    new Chart(document.getElementById('chartTahun'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_tahun_labels); ?>,
            datasets: [
                { label: 'Laporan Positif (Reward)', data: <?= json_encode($chart_tahun_reward); ?>, backgroundColor: '#10b981' },
                { label: 'Laporan Negatif (Punishment)', data: <?= json_encode($chart_tahun_punish); ?>, backgroundColor: '#ef4444' }
            ]
        },
        options: commonOptions
    });
});
</script>
</html>