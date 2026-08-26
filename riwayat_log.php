<?php
// Memulai sesi PHP
session_start();

// [KOMENTAR PENGEMBANGAN] 
// Proteksi halaman dimatikan sementara
/*
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}
*/

include "koneksi.php";

// Menangkap filter pencarian dan kategori untuk tabel
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';

/**
 * QUERY UTAMA TABEL (DIBATASI HANYA 5 DATA TERBARU)
 */
$query_str = "SELECT lp.*, s.nama_siswa, s.nis, s.kelas 
              FROM laporan_prilaku lp 
              LEFT JOIN siswa s ON lp.id_siswa = s.id_siswa 
              WHERE 1=1";

if (!empty($search)) {
    $query_str .= " AND (lp.teks_laporan LIKE '%$search%' 
                    OR lp.kecocokan_kata LIKE '%$search%' 
                    OR s.nama_siswa LIKE '%$search%' 
                    OR s.nis LIKE '%$search%')";
}

if (!empty($filter_kategori)) {
    $query_str .= " AND lp.label_prediksi = '$filter_kategori'";
}

// Menambahkan LIMIT 5 untuk membatasi hanya 5 data riwayat terbaru
$query_str .= " ORDER BY lp.id_laporan DESC LIMIT 5";
$sql = mysqli_query($koneksi, $query_str);


// =========================================================================
// [BARU] QUERY AMBIL DATA REMISI (Maks 5 Data Terbaru)
// =========================================================================
$query_remisi_str = "SELECT r.*, s.nama_siswa, s.nis, s.kelas 
                     FROM remisi r
                     LEFT JOIN siswa s ON r.id_siswa = s.id_siswa 
                     WHERE 1=1";

if (!empty($search)) {
    $query_remisi_str .= " AND (r.keterangan LIKE '%$search%' 
                           OR s.nama_siswa LIKE '%$search%' 
                           OR s.nis LIKE '%$search%')";
}
$query_remisi_str .= " ORDER BY r.id_remisi DESC LIMIT 5"; // Sesuaikan id_remisi & tgl_remisi dengan kolom asli Anda
$sql_remisi = mysqli_query($koneksi, $query_remisi_str);


// =========================================================================
// [BARU] QUERY AMBIL DATA LAPORAN TINDAK LANJUT (Maks 5 Data Terbaru)
// Catatan: tabel `tindaklanjut` belum memiliki kolom tanggal/waktu, sehingga
// "terbaru" di sini diurutkan berdasarkan id_tindak DESC (id auto increment
// terbesar = data yang terakhir dimasukkan).
// =========================================================================
$query_tindaklanjut_str = "SELECT tl.*, s.nama_siswa, s.nis, s.kelas 
                           FROM tindaklanjut tl
                           LEFT JOIN siswa s ON tl.id_siswa = s.id_siswa 
                           WHERE 1=1";

if (!empty($search)) {
    $query_tindaklanjut_str .= " AND (tl.tindaklanjut LIKE '%$search%' 
                                 OR s.nama_siswa LIKE '%$search%' 
                                 OR s.nis LIKE '%$search%')";
}
if (!empty($filter_kategori)) {
    $query_tindaklanjut_str .= " AND tl.ketegoriRP = '$filter_kategori'";
}
$query_tindaklanjut_str .= " ORDER BY tl.id_tindak DESC LIMIT 5";
$sql_tindaklanjut = mysqli_query($koneksi, $query_tindaklanjut_str);


// =========================================================================
// [BARU] QUERY PAPAN PERINGKAT REWARD VS PUNISHMENT
// Diurutkan berdasarkan total poin terbesar -> terkecil agar mudah dibandingkan
// =========================================================================
$rank_reward = [];
$q_rank_reward = mysqli_query($koneksi, "SELECT nama_siswa, kelas, total_poin_reward 
                                          FROM siswa 
                                          WHERE total_poin_reward > 0 
                                          ORDER BY total_poin_reward DESC, nama_siswa ASC 
                                          LIMIT 10");
if ($q_rank_reward) {
    while ($rr = mysqli_fetch_assoc($q_rank_reward)) {
        $rank_reward[] = $rr;
    }
}

$rank_punish = [];
$q_rank_punish = mysqli_query($koneksi, "SELECT nama_siswa, kelas, total_poin_punishment 
                                          FROM siswa 
                                          WHERE total_poin_punishment > 0 
                                          ORDER BY total_poin_punishment DESC, nama_siswa ASC 
                                          LIMIT 10");
if ($q_rank_punish) {
    while ($rp = mysqli_fetch_assoc($q_rank_punish)) {
        $rank_punish[] = $rp;
    }
}


// =========================================================================
// QUERY INTEGRASI MATA RANTAI DATA (HARI, MINGGU, BULAN, SEMESTER, TAHUN)
// =========================================================================

// 1. Ambil daftar semua master kelas unik untuk dataset Chart.js
$q_list_kelas = mysqli_query($koneksi, "SELECT DISTINCT IFNULL(kelas, 'Tanpa Kelas') as kelas FROM siswa ORDER BY kelas ASC");
$list_kelas = [];
while($lk = mysqli_fetch_assoc($q_list_kelas)) {
    $list_kelas[] = $lk['kelas'];
}

// --- A. DATA HARIAN ---
$q_hari = "SELECT 
                DATE_FORMAT(lp.tgl_input, '%d %b %Y') AS periode,
                IFNULL(s.kelas, 'Tanpa Kelas') AS kelas,
                SUM(CASE WHEN LOWER(lp.label_prediksi) = 'reward' THEN 1 ELSE 0 END) AS total_reward,
                SUM(CASE WHEN LOWER(lp.label_prediksi) = 'punishment' THEN 1 ELSE 0 END) AS total_punishment
            FROM laporan_prilaku lp
            LEFT JOIN siswa s ON lp.id_siswa = s.id_siswa
            WHERE lp.status_verifikasi = 'disetujui'
            GROUP BY DATE(lp.tgl_input), s.kelas
            ORDER BY lp.tgl_input ASC";
$res_hari = mysqli_query($koneksi, $q_hari);
$raw_hari = []; $labels_hari = [];
while($r = mysqli_fetch_assoc($res_hari)) {
    if(!in_array($r['periode'], $labels_hari)) $labels_hari[] = $r['periode'];
    $raw_hari[$r['periode']][$r['kelas']] = ['reward' => (int)$r['total_reward'], 'punishment' => (int)$r['total_punishment']];
}

// --- B. DATA MINGGUAN ---
$q_minggu = "SELECT 
                CONCAT('Minggu ', WEEK(lp.tgl_input, 1), ' (', YEAR(lp.tgl_input), ')') AS periode,
                IFNULL(s.kelas, 'Tanpa Kelas') AS kelas,
                SUM(CASE WHEN LOWER(lp.label_prediksi) = 'reward' THEN 1 ELSE 0 END) AS total_reward,
                SUM(CASE WHEN LOWER(lp.label_prediksi) = 'punishment' THEN 1 ELSE 0 END) AS total_punishment
            FROM laporan_prilaku lp
            LEFT JOIN siswa s ON lp.id_siswa = s.id_siswa
            WHERE lp.status_verifikasi = 'disetujui'
            GROUP BY YEARWEEK(lp.tgl_input, 1), s.kelas
            ORDER BY lp.tgl_input ASC";
$res_minggu = mysqli_query($koneksi, $q_minggu);
$raw_minggu = []; $labels_minggu = [];
while($r = mysqli_fetch_assoc($res_minggu)) {
    if(!in_array($r['periode'], $labels_minggu)) $labels_minggu[] = $r['periode'];
    $raw_minggu[$r['periode']][$r['kelas']] = ['reward' => (int)$r['total_reward'], 'punishment' => (int)$r['total_punishment']];
}

// --- C. DATA BULANAN ---
$q_bulan = "SELECT 
                DATE_FORMAT(lp.tgl_input, '%M %Y') AS periode,
                IFNULL(s.kelas, 'Tanpa Kelas') AS kelas,
                SUM(CASE WHEN LOWER(lp.label_prediksi) = 'reward' THEN 1 ELSE 0 END) AS total_reward,
                SUM(CASE WHEN LOWER(lp.label_prediksi) = 'punishment' THEN 1 ELSE 0 END) AS total_punishment
            FROM laporan_prilaku lp
            LEFT JOIN siswa s ON lp.id_siswa = s.id_siswa
            WHERE lp.status_verifikasi = 'disetujui'
            GROUP BY DATE_FORMAT(lp.tgl_input, '%Y-%m'), s.kelas
            ORDER BY lp.tgl_input ASC";
$res_bulan = mysqli_query($koneksi, $q_bulan);
$raw_bulan = []; $labels_bulan = [];
while($r = mysqli_fetch_assoc($res_bulan)) {
    if(!in_array($r['periode'], $labels_bulan)) $labels_bulan[] = $r['periode'];
    $raw_bulan[$r['periode']][$r['kelas']] = ['reward' => (int)$r['total_reward'], 'punishment' => (int)$r['total_punishment']];
}

// --- D. DATA SEMESTER (6 BULAN) ---
$q_semester = "SELECT 
                    CONCAT(CASE WHEN MONTH(lp.tgl_input) BETWEEN 7 AND 12 THEN 'Semester Ganjil ' ELSE 'Semester Genap ' END, YEAR(lp.tgl_input)) AS periode,
                    IFNULL(s.kelas, 'Tanpa Kelas') AS kelas,
                    SUM(CASE WHEN LOWER(lp.label_prediksi) = 'reward' THEN 1 ELSE 0 END) AS total_reward,
                    SUM(CASE WHEN LOWER(lp.label_prediksi) = 'punishment' THEN 1 ELSE 0 END) AS total_punishment
                FROM laporan_prilaku lp
                LEFT JOIN siswa s ON lp.id_siswa = s.id_siswa
                WHERE lp.status_verifikasi = 'disetujui'
                GROUP BY CASE WHEN MONTH(lp.tgl_input) BETWEEN 7 AND 12 THEN 'Ganjil' ELSE 'Genap' END, YEAR(lp.tgl_input), s.kelas
                ORDER BY lp.tgl_input ASC";
$res_semester = mysqli_query($koneksi, $q_semester);
$raw_semester = []; $labels_semester = [];
while($r = mysqli_fetch_assoc($res_semester)) {
    if(!in_array($r['periode'], $labels_semester)) $labels_semester[] = $r['periode'];
    $raw_semester[$r['periode']][$r['kelas']] = ['reward' => (int)$r['total_reward'], 'punishment' => (int)$r['total_punishment']];
}

// --- E. DATA TAHUNAN ---
$q_tahun = "SELECT 
                YEAR(lp.tgl_input) AS periode,
                IFNULL(s.kelas, 'Tanpa Kelas') AS kelas,
                SUM(CASE WHEN LOWER(lp.label_prediksi) = 'reward' THEN 1 ELSE 0 END) AS total_reward,
                SUM(CASE WHEN LOWER(lp.label_prediksi) = 'punishment' THEN 1 ELSE 0 END) AS total_punishment
            FROM laporan_prilaku lp
            LEFT JOIN siswa s ON lp.id_siswa = s.id_siswa
            WHERE lp.status_verifikasi = 'disetujui'
            GROUP BY YEAR(lp.tgl_input), s.kelas
            ORDER BY periode ASC";
$res_tahun = mysqli_query($koneksi, $q_tahun);
$raw_tahun = []; $labels_tahun = [];
while($r = mysqli_fetch_assoc($res_tahun)) {
    if(!in_array($r['periode'], $labels_tahun)) $labels_tahun[] = $r['periode'];
    $raw_tahun[$r['periode']][$r['kelas']] = ['reward' => (int)$r['total_reward'], 'punishment' => (int)$r['total_punishment']];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Riwayat Laporan - SMKS DB</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { background-color: #f0f4f8; font-family: 'Poppins', sans-serif; -webkit-tap-highlight-color: transparent; }

        .mobile-header { 
            background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%); 
            color: white; padding: 16px 20px; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
        }
        .btn-back { color: white; font-size: 22px; text-decoration: none; }
        
        .filter-group-box { display: flex; gap: 4px; background: #f1f5f9; padding: 4px; border-radius: 30px; flex-wrap: wrap; }
        .chart-filter-btn {
            font-size: 11px; font-weight: 500; padding: 5px 12px; border-radius: 20px; border: none; background: transparent; color: #64748b; transition: all 0.2s;
        }
        .chart-filter-btn.active { background-color: #ffffff; color: #1e3a8a; font-weight: 600; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }

        /* Kategori Button Tweaks */
        .kategori-group-box { display: flex; gap: 4px; background: #f1f5f9; padding: 4px; border-radius: 10px; }
        .kategori-btn { font-size: 11px; font-weight: 600; padding: 5px 14px; border-radius: 7px; border: none; background: transparent; color: #64748b; transition: all 0.2s; }
        .kategori-btn.active.all { background: #4f46e5; color: #fff; }
        .kategori-btn.active.reward { background: #15803d; color: #fff; }
        .kategori-btn.active.punish { background: #b91c1c; color: #fff; }

        @media (max-width: 767.98px) { 
            .desktop-view { display: none !important; } 
            .log-container { padding-bottom: 40px; padding-top: 15px; } 
        }
        @media (min-width: 768px) { 
            .log-container { margin-top: 40px; max-width: 1000px; } 
            .mobile-header { border-radius: 0 0 20px 20px; } 
        }

        .foto-bukti-thumb {
            width: 50px; height: 50px; object-fit: cover; border-radius: 10px;
            border: 2px solid #e2e8f0; cursor: zoom-in; transition: all 0.2s ease;
        }
        .foto-bukti-thumb:hover { transform: scale(1.08); border-color: #4f46e5; box-shadow: 0 4px 10px rgba(79,70,229,0.25); }
    </style>
</head>
<body>

<div class="mobile-header">
    <div class="container d-flex align-items-center gap-3 p-0">
        <a href="dashboard" class="btn-back"><i class="bi bi-arrow-left"></i></a>
        <h5 class="fw-bold mb-0" style="font-size: 18px; letter-spacing: 0.5px;">Riwayat Laporan</h5>
    </div>
</div>

<div class="container log-container px-3">

    <div class="alert alert-info border-0 rounded-4 small py-3 mb-4 d-flex align-items-center shadow-sm" role="alert" style="background-color: #e0f2fe; color: #0369a1;">
        <i class="bi bi-info-circle-fill me-3 fs-5"></i>
        <span style="line-height: 1.4;">Menampilkan log klasifikasi teks laporan perilaku siswa menggunakan metode Naive Bayes.</span>
    </div>

    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 20px;">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
            <div>
                <h6 class="fw-bold mb-1" style="color: #1e293b; font-size: 16px;">
                    <i class="bi bi-bezier2 text-primary me-2"></i>Matriks Komparasi Tren Perilaku Kelas
                </h6>
                <small class="text-muted">Gunakan filter untuk meninjau dinamika data antar kelas secara dinamis.</small>
            </div>
            
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <div class="kategori-group-box">
                    <button class="kategori-btn active all" onclick="switchKategori('all', this)">Semua</button>
                    <button class="kategori-btn reward" onclick="switchKategori('reward', this)">Reward</button>
                    <button class="kategori-btn punish" onclick="switchKategori('punishment', this)">Punishment</button>
                </div>
                <div class="filter-group-box">
                    <button class="chart-filter-btn" onclick="switchWaktu('hari', this)">Hari</button>
                    <button class="chart-filter-btn" onclick="switchWaktu('minggu', this)">Minggu</button>
                    <button class="chart-filter-btn active" onclick="switchWaktu('bulan', this)">Bulan</button>
                    <button class="chart-filter-btn" onclick="switchWaktu('semester', this)">1 Semester</button>
                    <button class="chart-filter-btn" onclick="switchWaktu('tahun', this)">Tahun</button>
                </div>
            </div>
        </div>

        <div style="position: relative; height: 320px; width: 100%;">
            <canvas id="grafikBersatuKombinasi"></canvas>
        </div>
    </div>

    <?php
    // Mengambil periode bulan saat ini yang tersedia di data bulanan
    $bulan_sekarang = !empty($labels_bulan) ? end($labels_bulan) : '';
    
    $kelas_reward_terbanyak = "-";
    $max_reward = 0;
    $kelas_punish_terbanyak = "-";
    $max_punish = 0;
    $stat_per_kelas = [];

    // Prosesing ekstraksi data lokal dari array $raw_bulan
    if (!empty($bulan_sekarang) && isset($raw_bulan[$bulan_sekarang])) {
        foreach ($list_kelas as $kls) {
            $r_count = isset($raw_bulan[$bulan_sekarang][$kls]['reward']) ? $raw_bulan[$bulan_sekarang][$kls]['reward'] : 0;
            $p_count = isset($raw_bulan[$bulan_sekarang][$kls]['punishment']) ? $raw_bulan[$bulan_sekarang][$kls]['punishment'] : 0;
            $total_laporan = $r_count + $p_count;

            if ($total_laporan > 0) {
                $stat_per_kelas[$kls] = [
                    'reward' => $r_count,
                    'punish' => $p_count,
                    'total' => $total_laporan
                ];

                if ($r_count > $max_reward) {
                    $max_reward = $r_count;
                    $kelas_reward_terbanyak = $kls;
                }
                if ($p_count > $max_punish) {
                    $max_punish = $p_count;
                    $kelas_punish_terbanyak = $kls;
                }
            }
        }
    }
    ?>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="p-3 d-flex align-items-center rounded-4 shadow-sm" style="background-color: #e8f5e9; border: 1px solid #c8e6c9;">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white me-3" style="width: 50px; height: 50px; background-color: #2e7d32; font-size: 24px;">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div>
                    <small class="text-success fw-bold text-uppercase font-monospace" style="font-size: 11px; letter-spacing: 1px;">🏆 REWARD TERBANYAK</small>
                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($kelas_reward_terbanyak); ?></h5>
                    <small class="text-secondary">Total: <b><?= $max_reward; ?></b> Laporan Positif</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="p-3 d-flex align-items-center rounded-4 shadow-sm" style="background-color: #ffebee; border: 1px solid #ffcdd2;">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white me-3" style="width: 50px; height: 50px; background-color: #c62828; font-size: 24px;">
                    <i class="bi bi-cone-striped"></i>
                </div>
                <div>
                    <small class="text-danger fw-bold text-uppercase font-monospace" style="font-size: 11px; letter-spacing: 1px;">⚠️ PUNISHMENT TERBANYAK</small>
                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($kelas_punish_terbanyak); ?></h5>
                    <small class="text-secondary">Total: <b><?= $max_punish; ?></b> Laporan Pelanggaran</small>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Statistik Semua Kelas</h6>
            <span class="badge bg-white text-secondary border shadow-sm px-3 py-1.5 rounded-pill small fw-medium">Bulan Ini</span>
        </div>
        
        <div class="row g-3">
            <?php if(!empty($stat_per_kelas)): ?>
                <?php foreach($stat_per_kelas as $nama_kls => $data_kls): ?>
                    <div class="col-12 col-md-3">
                        <div class="card border-0 shadow-sm p-3 rounded-4" style="background: #ffffff;">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold small shadow-sm" style="width: 36px; height: 36px; font-size: 13px;">
                                    <?= substr($nama_kls, 0, 2); ?>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($nama_kls); ?></h6>
                                    <small class="text-muted small">Total <?= $data_kls['total']; ?> Laporan</small>
                                </div>
                            </div>
                            <div class="row g-2 text-center">
                                <div class="col-6">
                                    <div class="p-2 rounded-3 bg-light border-0">
                                        <h4 class="fw-bold text-success mb-0 font-monospace"><?= $data_kls['reward']; ?></h4>
                                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 10px;">REWARD</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded-3 bg-light border-0">
                                        <h4 class="fw-bold text-danger mb-0 font-monospace"><?= $data_kls['punish']; ?></h4>
                                        <small class="text-muted text-uppercase fw-semibold" style="font-size: 10px;">PUNISH</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><div class="card p-4 text-center text-muted border-0 shadow-sm rounded-4">Tidak ada ringkasan statistik bulan ini.</div></div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 20px;">
        <h6 class="fw-bold mb-1 mt-1" style="color: #1e293b; font-size: 16px;">
            <i class="bi bi-list-ol text-primary me-2"></i>Papan Peringkat Reward vs Punishment
        </h6>
        <small class="text-muted d-block mb-3">Diurutkan dari total poin terbesar hingga terkecil supaya mudah dibandingkan.</small>

        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="mb-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                        <i class="bi bi-trophy-fill me-1"></i>REWARD
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="12%" class="text-center py-2">No</th>
                                <th class="py-2">Nama</th>
                                <th width="28%" class="text-center py-2">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rank_reward)): $no_r = 1; foreach ($rank_reward as $rr): ?>
                            <tr>
                                <td class="text-center text-secondary fw-medium"><?= $no_r++; ?></td>
                                <td>
                                    <div class="fw-semibold text-dark small"><?= htmlspecialchars($rr['nama_siswa']); ?></div>
                                    <small class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($rr['kelas']); ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success text-white fw-bold">+<?= (int)$rr['total_poin_reward']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center text-muted py-4 small">Belum ada data reward.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="mb-2">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>PUNISHMENT
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="12%" class="text-center py-2">No</th>
                                <th class="py-2">Nama</th>
                                <th width="28%" class="text-center py-2">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rank_punish)): $no_p = 1; foreach ($rank_punish as $rp): ?>
                            <tr>
                                <td class="text-center text-secondary fw-medium"><?= $no_p++; ?></td>
                                <td>
                                    <div class="fw-semibold text-dark small"><?= htmlspecialchars($rp['nama_siswa']); ?></div>
                                    <small class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($rp['kelas']); ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger text-white fw-bold">-<?= (int)$rp['total_poin_punishment']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center text-muted py-4 small">Belum ada data punishment.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-3 border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <form method="GET" action="">
            <div class="row g-2">
                <div class="col-12 col-md-7">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0 px-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0" placeholder="Cari nama, NIS, isi laporan..." value="<?= htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-12 col-md-5">
                    <select name="kategori" class="form-select form-select-sm bg-light border-0 fw-medium text-secondary" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <option value="Reward" <?= $filter_kategori == 'Reward' ? 'selected' : ''; ?>>Reward</option>
                        <option value="Punishment" <?= $filter_kategori == 'Punishment' ? 'selected' : ''; ?>>Punishment</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="desktop-view card border-0 shadow-sm p-4 mb-4" style="border-radius: 20px;">
        <h6 class="fw-bold mb-3 mt-1" style="color: #1e293b; font-size: 16px;">Daftar Riwayat NLP Terbaru (Maks. 5 Data)</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center py-3">No</th>
                        <th width="12%" class="py-3">Tanggal & Jam</th>
                        <th width="20%" class="py-3">Biodata Siswa</th>
                        <th width="25%" class="py-3">Isi Laporan Kasus</th>
                        <th width="10%" class="text-center py-3">Foto Bukti</th>
                        <th width="10%" class="text-center py-3">Hasil Klasifikasi</th>
                        <th width="8%" class="text-center py-3">Status</th>
                        <th width="10%" class="text-center py-3">Kecocokan %</th>
                        <th width="8%" class="text-center py-3">Poin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($sql) > 0) {
                        $no = 1;
                        while($data = mysqli_fetch_array($sql)) {
                            $is_reward = (strtolower($data['label_prediksi']) == 'reward');
                            $badge_class = $is_reward ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle';
                            $icon_class = $is_reward ? 'bi-award-fill' : 'bi-exclamation-triangle-fill';
                            $poin_badge = $is_reward ? 'bg-success text-white' : 'bg-danger text-white';
                            $poin_sign = $is_reward ? '+' : '-';
                            
                            $nama_siswa = !empty($data['nama_siswa']) ? $data['nama_siswa'] : "ID: ".$data['id_siswa'];
                            $nis_siswa = !empty($data['nis']) ? $data['nis'] : "-";
                            $kelas_siswa = !empty($data['kelas']) ? $data['kelas'] : "-";
                    ?>
                    <tr>
                        <td class="text-center fw-medium text-secondary"><?= $no++; ?></td>
                        <td class="text-secondary small fw-medium">
                            <?= date('d-m-Y', strtotime($data['tgl_input'])); ?><br>
                            <span class="text-muted font-monospace small"><?= date('H:i:s', strtotime($data['tgl_input'])); ?></span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($nama_siswa); ?></div>
                            <small class="text-muted font-monospace">NIS: <?= htmlspecialchars($nis_siswa); ?> &bull; <?= htmlspecialchars($kelas_siswa); ?></small>
                        </td>
                        <td><div class="small text-secondary">"<?= htmlspecialchars($data['teks_laporan']); ?>"</div></td>
                        <td class="text-center">
                            <?php if (!empty($data['foto'])): ?>
                                <img src="<?= htmlspecialchars($data['foto']); ?>" 
                                     alt="Foto Bukti Laporan" 
                                     class="foto-bukti-thumb" 
                                     onclick="bukaZoomFoto('<?= htmlspecialchars($data['foto'], ENT_QUOTES); ?>', '<?= htmlspecialchars($nama_siswa, ENT_QUOTES); ?>')">
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $badge_class; ?> border px-2 py-1 rounded fw-bold" style="font-size: 11px;">
                                <i class="bi <?= $icon_class; ?> me-1"></i><?= strtoupper($data['label_prediksi']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php $status_laporan = $data['status_verifikasi'] ?? 'disetujui'; ?>
                            <span class="badge text-bg-<?= $status_laporan === 'pending' ? 'warning' : ($status_laporan === 'disetujui' ? 'success' : 'danger'); ?>">
                                <?= strtoupper(htmlspecialchars($status_laporan)); ?>
                            </span>
                        </td>
                        <td class="text-center text-dark fw-bold font-monospace small"><?= $data['akurasi_map'] ?? '0.00'; ?></td>
                        <td class="text-center">
                            <?php if ($status_laporan === 'disetujui'): ?>
                                <span class="badge <?= $poin_badge; ?> px-2 py-1 fw-bold"><?= $poin_sign . $data['poin_didapat']; ?></span>
                            <?php else: ?><span class="text-muted small">Belum dihitung</span><?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else { 
                        echo '<tr><td colspan="9" class="text-center text-muted py-5">Data tidak ditemukan.</td></tr>';
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="desktop-view card border-0 shadow-sm p-4 mb-5" style="border-radius: 20px;">
        <h6 class="fw-bold mb-3 mt-1" style="color: #1e293b; font-size: 16px;">
            <i class="bi bi-heart-pulse-fill text-success me-2"></i>Daftar Riwayat Remisi Terbaru (Maks. 5 Data)
        </h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center py-3">No</th>
                        <th width="13%" class="py-3">Tanggal & Jam</th>
                        <th width="22%" class="py-3">Biodata Siswa</th>
                        <th width="30%" class="py-3">Keterangan / Aksi Remisi</th>
                        <th width="15%" class="text-center py-3">Foto Bukti</th>
                        <th width="15%" class="text-center py-3">Poin Pengurangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($sql_remisi) > 0) {
                        $no_remisi = 1;
                        while($data_remisi = mysqli_fetch_array($sql_remisi)) {
                            $nama_siswa_r = !empty($data_remisi['nama_siswa']) ? $data_remisi['nama_siswa'] : "ID: ".$data_remisi['id_siswa'];
                            $nis_siswa_r = !empty($data_remisi['nis']) ? $data_remisi['nis'] : "-";
                            $kelas_siswa_r = !empty($data_remisi['kelas']) ? $data_remisi['kelas'] : "-";
                            // Asumsi nama kolom tanggal di tabel remisi adalah tgl_remisi, sesuaikan jika berbeda
                            $tgl_remisi = isset($data_remisi['tgl_remisi']) ? $data_remisi['tgl_remisi'] : date('Y-m-y H:i:s'); 
                    ?>
                    <tr>
                        <td class="text-center fw-medium text-secondary"><?= $no_remisi++; ?></td>
                        <td class="text-secondary small fw-medium">
                            <?= date('d-m-Y', strtotime($tgl_remisi)); ?><br>
                            <span class="text-muted font-monospace small"><?= date('H:i:s', strtotime($tgl_remisi)); ?></span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($nama_siswa_r); ?></div>
                            <small class="text-muted font-monospace">NIS: <?= htmlspecialchars($nis_siswa_r); ?> &bull; <?= htmlspecialchars($kelas_siswa_r); ?></small>
                        </td>
                        <td><div class="small text-secondary"><?= htmlspecialchars($data_remisi['keterangan'] ?? '-'); ?></div></td>
                        <td class="text-center">
                            <?php if (!empty($data_remisi['bukti'])): ?>
                                <img src="<?= htmlspecialchars($data_remisi['bukti']); ?>" 
                                     alt="Foto Bukti Remisi" 
                                     class="foto-bukti-thumb" 
                                     onclick="bukaZoomFoto('<?= htmlspecialchars($data_remisi['bukti'], ENT_QUOTES); ?>', '<?= htmlspecialchars($nama_siswa_r, ENT_QUOTES); ?>')">
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold font-monospace">
                                -<?= $data_remisi['poin_remisi'] ?? 0; ?> Poin
                            </span>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else { 
                        echo '<tr><td colspan="6" class="text-center text-muted py-5">Tidak ada riwayat remisi.</td></tr>';
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="desktop-view card border-0 shadow-sm p-4 mb-5" style="border-radius: 20px;">
        <h6 class="fw-bold mb-1 mt-1" style="color: #1e293b; font-size: 16px;">
            <i class="bi bi-clipboard2-pulse-fill text-primary me-2"></i>Daftar Laporan Tindak Lanjut Terbaru (Maks. 5 Data)
        </h6>
        <small class="text-muted d-block mb-3">Catatan: tabel <code>tindaklanjut</code> belum memiliki kolom tanggal/waktu, sehingga urutan di sini mengikuti ID terbesar (data yang terakhir dimasukkan tampil paling atas).</small>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center py-3">No</th>
                        <th width="22%" class="py-3">Biodata Siswa</th>
                        <th width="12%" class="text-center py-3">Kategori</th>
                        <th width="31%" class="py-3">Deskripsi Tindak Lanjut</th>
                        <th width="15%" class="text-center py-3">Foto Bukti</th>
                        <th width="15%" class="text-center py-3">Poin Dikurangi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($sql_tindaklanjut) > 0) {
                        $no_tl = 1;
                        while($data_tl = mysqli_fetch_array($sql_tindaklanjut)) {
                            $is_reward_tl = (strtolower($data_tl['ketegoriRP']) == 'reward');
                            $badge_class_tl = $is_reward_tl ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle';
                            $poin_badge_tl = $is_reward_tl ? 'bg-success text-white' : 'bg-danger text-white';
                            $poin_sign_tl = $is_reward_tl ? '+' : '-';

                            $nama_siswa_tl = !empty($data_tl['nama_siswa']) ? $data_tl['nama_siswa'] : "ID: ".$data_tl['id_siswa'];
                            $nis_siswa_tl = !empty($data_tl['nis']) ? $data_tl['nis'] : "-";
                            $kelas_siswa_tl = !empty($data_tl['kelas']) ? $data_tl['kelas'] : "-";
                    ?>
                    <tr>
                        <td class="text-center fw-medium text-secondary"><?= $no_tl++; ?></td>
                        <td>
                            <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($nama_siswa_tl); ?></div>
                            <small class="text-muted font-monospace">NIS: <?= htmlspecialchars($nis_siswa_tl); ?> &bull; <?= htmlspecialchars($kelas_siswa_tl); ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $badge_class_tl; ?> border px-2 py-1 rounded fw-bold" style="font-size: 11px;">
                                <?= strtoupper($data_tl['ketegoriRP']); ?>
                            </span>
                        </td>
                        <td><div class="small text-secondary" style="white-space: pre-line;"><?= htmlspecialchars($data_tl['tindaklanjut']); ?></div></td>
                        <td class="text-center">
                            <?php if (!empty($data_tl['foto'])): ?>
                                <img src="<?= htmlspecialchars($data_tl['foto']); ?>" 
                                     alt="Foto Bukti Tindak Lanjut" 
                                     class="foto-bukti-thumb" 
                                     onclick="bukaZoomFoto('<?= htmlspecialchars($data_tl['foto'], ENT_QUOTES); ?>', '<?= htmlspecialchars($nama_siswa_tl, ENT_QUOTES); ?>')">
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><span class="badge <?= $poin_badge_tl; ?> px-2 py-1 fw-bold"><?= $poin_sign_tl . $data_tl['poin']; ?></span></td>
                    </tr>
                    <?php 
                        }
                    } else { 
                        echo '<tr><td colspan="6" class="text-center text-muted py-5">Belum ada data tindak lanjut.</td></tr>';
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Zoom Foto Bukti -->
<div class="modal fade" id="modalZoomFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-white small">
                    <i class="bi bi-camera-fill me-2"></i>Foto Bukti — <span id="namaSiswaZoomFoto"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="imgZoomFoto" src="" alt="Foto Bukti Diperbesar" class="img-fluid rounded-3" style="max-height: 75vh; width: auto;">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Fungsi buka modal zoom untuk foto bukti laporan
    function bukaZoomFoto(src, namaSiswa) {
        document.getElementById('imgZoomFoto').src = src;
        document.getElementById('namaSiswaZoomFoto').textContent = namaSiswa || '-';
        const modalZoom = new bootstrap.Modal(document.getElementById('modalZoomFoto'));
        modalZoom.show();
    }

    const masterKelas = <?= json_encode($list_kelas); ?>;
    
    const databaseWaktu = {
        hari: { labels: <?= json_encode($labels_hari); ?>, raw: <?= json_encode($raw_hari); ?> },
        minggu: { labels: <?= json_encode($labels_minggu); ?>, raw: <?= json_encode($raw_minggu); ?> },
        bulan: { labels: <?= json_encode($labels_bulan); ?>, raw: <?= json_encode($raw_bulan); ?> },
        semester: { labels: <?= json_encode($labels_semester); ?>, raw: <?= json_encode($raw_semester); ?> },
        tahun: { labels: <?= json_encode($labels_tahun); ?>, raw: <?= json_encode($raw_tahun); ?> }
    };

    const warnaDatasets = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#64748b'];

    let filterWaktuAktif = 'bulan';
    let filterKategoriAktif = 'all'; 
    let grafikKombinasi;

    function dapatkanDatasetTerstruktur(waktu, kategori) {
        const sumberData = databaseWaktu[waktu];
        
        return masterKelas.map((kelas, idx) => {
            const poinData = sumberData.labels.map(periode => {
                if (sumberData.raw[periode] && sumberData.raw[periode][kelas]) {
                    if (kategori === 'all') {
                        return (sumberData.raw[periode][kelas]['reward'] || 0) + (sumberData.raw[periode][kelas]['punishment'] || 0);
                    } else {
                        return sumberData.raw[periode][kelas][kategori] || 0;
                    }
                }
                return 0;
            });

            return {
                label: kelas,
                data: poinData,
                borderColor: warnaDatasets[idx % warnaDatasets.length],
                backgroundColor: warnaDatasets[idx % warnaDatasets.length],
                borderWidth: 2.5,
                pointRadius: 3,
                tension: 0.15
            };
        });
    }

    const ctxGabungan = document.getElementById('grafikBersatuKombinasi').getContext('2d');
    grafikKombinasi = new Chart(ctxGabungan, {
        type: 'line', 
        data: {
            labels: databaseWaktu.bulan.labels,
            datasets: dapatkanDatasetTerstruktur('bulan', 'all')
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { family: 'Poppins', size: 10 } } },
                x: { ticks: { font: { family: 'Poppins', size: 10 } } }
            },
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Poppins', size: 11 }, boxWidth: 10 } }
            }
        }
    });

    function switchWaktu(waktu, elemen) {
        elemen.parentElement.querySelectorAll('.chart-filter-btn').forEach(btn => btn.classList.remove('active'));
        elemen.classList.add('active');
        filterWaktuAktif = waktu;
        perbaruiTampilanGrafik();
    }

    function switchKategori(kat, elemen) {
        elemen.parentElement.querySelectorAll('.kategori-btn').forEach(btn => btn.classList.remove('active'));
        elemen.classList.add('active');
        filterKategoriAktif = kat;
        perbaruiTampilanGrafik();
    }

    function perbaruiTampilanGrafik() {
        grafikKombinasi.data.labels = databaseWaktu[filterWaktuAktif].labels;
        grafikKombinasi.data.datasets = dapatkanDatasetTerstruktur(filterWaktuAktif, filterKategoriAktif);
        grafikKombinasi.update();
    }
</script>
</body>
</html>
