<?php
session_start();
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

include 'koneksi.php';

// Ambil flash message hasil simpan tindak lanjut (pola Post-Redirect-Get)
$pesan_tindak = $_SESSION['pesan_tindak'] ?? '';
$status_tindak = $_SESSION['status_tindak'] ?? '';
unset($_SESSION['pesan_tindak'], $_SESSION['status_tindak']);

// =========================================================================
// PROSES SIMPAN FORM TINDAK LANJUT / APRESIASI (+ FOTO BUKTI REAL-TIME)
// + PENGURANGAN POIN SISWA (Reward/Punishment sesuai status saat ditindaklanjuti)
// Disimpan ke tabel `tindaklanjut`
// =========================================================================
if (isset($_POST['proses_tindaklanjut'])) {
    $id_siswa               = (int)($_POST['id_siswa'] ?? 0);
    $kategori_rp            = trim($_POST['kategori_rp'] ?? '');
    $deskripsi_tindakan     = trim($_POST['deskripsi_tindakan'] ?? '');
    $deskripsi_tindaklanjut = trim($_POST['deskripsi_tindaklanjut'] ?? '');
    $poin_kurang            = (int)($_POST['poin_kurang'] ?? 0);
    $id_users               = (int)($_SESSION['id_user'] ?? 0);

    if ($id_siswa <= 0 || $kategori_rp === '' || $deskripsi_tindakan === '' || $deskripsi_tindaklanjut === '' || $poin_kurang <= 0) {
        $_SESSION['status_tindak'] = 'gagal';
        $_SESSION['pesan_tindak']  = "Semua kolom (termasuk poin yang dikurangi) wajib diisi dengan benar.";
    } else {
        // Ambil data poin siswa terkini untuk validasi & perhitungan pengurangan
        $q_cek_siswa = mysqli_query($koneksi, "SELECT nama_siswa, total_poin_reward, total_poin_punishment FROM siswa WHERE id_siswa = $id_siswa");

        if (!$q_cek_siswa || mysqli_num_rows($q_cek_siswa) == 0) {
            $_SESSION['status_tindak'] = 'gagal';
            $_SESSION['pesan_tindak']  = "Data siswa tidak ditemukan.";
        } else {
            $d_siswa = mysqli_fetch_assoc($q_cek_siswa);
            $reward_sekarang     = (int)$d_siswa['total_poin_reward'];
            $punishment_sekarang = (int)$d_siswa['total_poin_punishment'];

            // Cegah pengurangan melebihi poin yang tersedia pada kategori terkait
            if ($kategori_rp == 'Reward') {
                if ($poin_kurang > $reward_sekarang) { $poin_kurang = $reward_sekarang; }
            } else {
                if ($poin_kurang > $punishment_sekarang) { $poin_kurang = $punishment_sekarang; }
            }

            if ($poin_kurang <= 0) {
                $_SESSION['status_tindak'] = 'gagal';
                $_SESSION['pesan_tindak']  = "Poin " . strtolower($kategori_rp) . " siswa ini sudah 0, tidak ada yang bisa dikurangi.";
            } else {
                // Upload foto bukti realtime (WAJIB)
                $foto_final = "";
                if (isset($_FILES['foto_tindak']) && $_FILES['foto_tindak']['error'] == 0) {
                    $folder_upload = 'uploads/tindaklanjut/';
                    if (!is_dir($folder_upload)) {
                        mkdir($folder_upload, 0755, true);
                    }

                    $ext_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
                    $ext_file = strtolower(pathinfo($_FILES['foto_tindak']['name'], PATHINFO_EXTENSION));
                    $ukuran_maks = 5 * 1024 * 1024; // 5 MB

                    if (in_array($ext_file, $ext_diizinkan) && $_FILES['foto_tindak']['size'] <= $ukuran_maks) {
                        $nama_file_baru = 'tindak_' . $id_siswa . '_' . time() . '_' . uniqid() . '.' . $ext_file;
                        $path_tujuan = $folder_upload . $nama_file_baru;

                        if (move_uploaded_file($_FILES['foto_tindak']['tmp_name'], $path_tujuan)) {
                            $foto_final = $path_tujuan;
                        }
                    }
                }

                if (empty($foto_final)) {
                    $_SESSION['status_tindak'] = 'gagal';
                    $_SESSION['pesan_tindak']  = "Foto bukti realtime wajib diunggah (format JPG/PNG/WEBP, maksimal 5MB).";
                } else {
                    // Gabungkan kedua deskripsi ke satu kolom `tindaklanjut` yang tersedia di tabel
                    $isi_tindaklanjut = "Tindakan/Apresiasi: " . $deskripsi_tindakan . "\n\nTindak Lanjut: " . $deskripsi_tindaklanjut;

                    $kategori_db = mysqli_real_escape_string($koneksi, $kategori_rp);
                    $isi_db      = mysqli_real_escape_string($koneksi, $isi_tindaklanjut);
                    $foto_db     = mysqli_real_escape_string($koneksi, $foto_final);
                    $poin_db     = mysqli_real_escape_string($koneksi, $poin_kurang);

                    // Mulai transaksi agar data siswa & log tindaklanjut tetap sinkron
                    mysqli_begin_transaction($koneksi);

                    try {
                        // Kurangi poin sesuai kategori status siswa saat ditindaklanjuti
                        if ($kategori_rp == 'Reward') {
                            $reward_baru = $reward_sekarang - $poin_kurang;
                            $query_update_poin = "UPDATE siswa SET total_poin_reward = $reward_baru WHERE id_siswa = $id_siswa";
                            $sisa_poin_pesan = "Sisa poin reward: $reward_baru Poin.";
                        } else {
                            $punishment_baru = $punishment_sekarang - $poin_kurang;
                            $query_update_poin = "UPDATE siswa SET total_poin_punishment = $punishment_baru WHERE id_siswa = $id_siswa";
                            $sisa_poin_pesan = "Sisa poin punishment: $punishment_baru Poin.";
                        }

                        // PENTING: cek hasil query. mysqli_query() TIDAK melempar exception saat gagal,
                        // ia hanya mengembalikan false. Tanpa pengecekan ini, kegagalan akan
                        // "lolos" begitu saja dan sistem tetap melaporkan sukses padahal data tidak masuk.
                        if (!mysqli_query($koneksi, $query_update_poin)) {
                            throw new Exception("Gagal update poin siswa: " . mysqli_error($koneksi));
                        }

                        // Nama kolom di tabel `tindaklanjut` sudah diubah menjadi `ketegoriRP`,
                        // dan sekarang ada kolom tambahan id_siswa (relasi ke siswa) & id_users
                        // (siapa yang mencatat tindak lanjut ini, diambil dari sesi login)
                        $id_users_db = $id_users > 0 ? $id_users : "NULL"; // kolom id_users boleh NULL

                        $query_insert_tindak = "INSERT INTO tindaklanjut (id_siswa, id_users, ketegoriRP, tindaklanjut, poin, foto) 
                                                VALUES ($id_siswa, $id_users_db, '$kategori_db', '$isi_db', '$poin_db', '$foto_db')";

                        if (!mysqli_query($koneksi, $query_insert_tindak)) {
                            throw new Exception("Gagal insert tindak lanjut: " . mysqli_error($koneksi));
                        }

                        mysqli_commit($koneksi);
                        $_SESSION['status_tindak'] = 'sukses';
                        $_SESSION['pesan_tindak']  = "Tindak lanjut untuk <strong>" . htmlspecialchars($d_siswa['nama_siswa']) . "</strong> berhasil disimpan. Poin $kategori_rp berkurang sebanyak $poin_kurang Poin. $sisa_poin_pesan";
                    } catch (Exception $e) {
                        mysqli_rollback($koneksi);
                        @unlink($foto_final);
                        $_SESSION['status_tindak'] = 'gagal';
                        // Sementara ini pesan errornya ditampilkan apa adanya supaya mudah didebug.
                        // Setelah bug terselesaikan & sudah live, ganti kembali ke pesan generik
                        // ("Gagal menyimpan data ke database.") agar detail error tidak terekspos ke user.
                        $_SESSION['pesan_tindak']  = "Gagal menyimpan data ke database. Detail: " . $e->getMessage();
                    }
                }
            }
        }
    }

    header("location:reward_punishment.php");
    exit();
}

// Filter Kelas (Meniru logika filter halaman Data Siswa)
$filter_kelas = isset($_GET['kelas']) ? mysqli_real_escape_string($koneksi, $_GET['kelas']) : '';

$query = "SELECT * FROM siswa WHERE 1=1";
if ($filter_kelas != '') { 
    $query .= " AND kelas = '$filter_kelas'"; 
}
$query .= " ORDER BY ABS(total_poin_punishment - total_poin_reward) DESC, nama_siswa ASC";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Reward & Punishment - SMKS DB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        html, body { 
            background-color: #f0f4f8; 
            font-family: 'Poppins', sans-serif; 
            -webkit-tap-highlight-color: transparent;
            overflow-x: hidden;
            width: 100%;
        }
        
        .mobile-header { background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%); color: white; padding: 15px 20px; position: sticky; top: 0; z-index: 100; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .btn-back { color: white; font-size: 22px; text-decoration: none; }
        
        /* Desain Card List HP (Sama persis dengan skrip referensi) */
        .student-mobile-card { background: white; border-radius: 18px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.03); margin-bottom: 12px; padding: 16px; width: 100%; }
        .badge-poin { padding: 5px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .badge-kategori-aktual { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .action-btn-group { border-top: 1px dashed #e2e8f0; margin-top: 12px; padding-top: 12px; display: flex; justify-content: flex-end; gap: 8px; }
        
        /* CSS Kunci DataTables Pas Layar HP */
        #tabelSiswa {
            table-layout: fixed !important;
            width: 100% !important;
            border-collapse: collapse !important;
        }
        #tabelSiswa td {
            word-wrap: break-word;
            white-space: normal;
        }
        
        /* Sembunyikan pencarian asli DataTables agar tidak double */
        .dataTables_wrapper .dataTables_filter { display: none !important; }
        .dataTables_wrapper .dataTables_length, .dataTables_info { display: none !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0px !important; margin-left: 0px !important; border: none !important; }
        
        /* Styling Custom Search Bar yang dipindahkan */
        .custom-search-container input {
            border: none !important;
            box-shadow: none !important;
            padding: 8px 12px;
            font-size: 14px;
            width: 100%;
        }
        .custom-search-container input:focus {
            outline: none;
        }
        
        @media (max-width: 767.98px) { 
            .desktop-view { display: none !important; }
            
            .student-container { 
                padding-bottom: 30px; 
                width: 100% !important; 
                max-width: 100% !important;
                overflow-x: hidden;
            }
            .dataTables_paginate { display: flex; justify-content: center; margin-top: 15px; }
        }
        
        @media (min-width: 768px) { 
            .mobile-view { display: none !important; } 
            .student-container { margin-top: 30px; } 
            #tabelSiswa { table-layout: auto !important; }
        }

        .upload-foto-box { border: 2px dashed #cbd5e1; border-radius: 14px; padding: 18px; text-align: center; background-color: #f8fafc; cursor: pointer; transition: all 0.2s; }
        .upload-foto-box:hover { border-color: #4f46e5; background-color: #eef2ff; }
        .upload-foto-box i { font-size: 26px; color: #64748b; }
        .preview-foto-wrapper { display: none; margin-top: 12px; text-align: center; position: relative; }
        .preview-foto-wrapper img { max-width: 100%; max-height: 260px; border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); }
        .btn-hapus-foto { position: absolute; top: 6px; right: 6px; background: rgba(15,23,42,0.75); color: #fff; border: none; border-radius: 50%; width: 28px; height: 28px; }

        .btn-tindak-lanjut { font-size: 11.5px; font-weight: 600; }
        .header-tabel-desktop { background: #eef2ff; border-radius: 12px; margin: 0 0 8px 0; }
    </style>
</head>
<body>

<div class="mobile-header">
    <div class="container d-flex align-items-center justify-content-between p-0">
        <div class="d-flex align-items-center gap-3">
            <a href="dashboard" class="btn-back"><i class="bi bi-arrow-left"></i></a>
            <h5 class="fw-bold mb-0" style="font-size: 18px;">Reward & Punishment</h5>
        </div>
        <button class="btn btn-light btn-sm fw-bold rounded-pill px-3" onclick="window.location.reload();">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </div>
</div>

<div class="container student-container px-3 mt-3">

    <?php if (!empty($pesan_tindak)): ?>
        <div class="alert <?php echo ($status_tindak == 'sukses') ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show rounded-3 small py-2 mb-3" role="alert">
            <i class="bi <?php echo ($status_tindak == 'sukses') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo $pesan_tindak; ?>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 border-0 shadow-sm mb-3 bg-white" style="border-radius: 16px;">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-secondary mb-1">Cari Siswa</label>
                <div class="input-group input-group-sm rounded overflow-hidden border custom-search-container">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="pencarianSiswa" class="form-control" placeholder="Ketik nama atau NIS siswa...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-secondary mb-1">Filter Kategori RP</label>
                <div class="input-group input-group-sm rounded overflow-hidden border">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-trophy text-muted"></i></span>
                    <select id="filterKategoriRP" class="form-select form-select-sm bg-white border-0 fw-semibold text-secondary py-2">
                        <option value="">Semua Status</option>
                        <option value="Reward">Reward</option>
                        <option value="Punishment">Punishment</option>
                        <option value="Netral">Netral</option>
                    </select>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <form method="GET" action="" id="filterForm">
                    <label class="form-label small fw-semibold text-secondary mb-1">Filter Tingkat Kelas</label>
                    <div class="input-group input-group-sm rounded overflow-hidden border">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-funnel text-muted"></i></span>
                        <select name="kelas" class="form-select form-select-sm bg-white border-0 fw-semibold text-secondary py-2" onchange="document.getElementById('filterForm').submit();">
                            <option value="">Semua Kelas</option>
                            <?php
                            $query_kelas = mysqli_query($koneksi, "SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC");
                            while($k = mysqli_fetch_assoc($query_kelas)) {
                                $nama_kelas = $k['kelas'];
                                $selected = ($filter_kelas == $nama_kelas) ? 'selected' : '';
                                echo "<option value='$nama_kelas' $selected>$nama_kelas</option>";
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="desktop-view row align-items-center header-tabel-desktop px-3 py-2 fw-bold text-secondary small">
        <div class="col-md-3">Nama</div>
        <div class="col-md-1">Kelas</div>
        <div class="col-md-2 text-center">Poin Aktual</div>
        <div class="col-md-2 text-center">Kategori Reward/Punishment</div>
        <div class="col-md-2">Kategori Kelayakan</div>
        <div class="col-md-2">Tindakan / Apresiasi</div>
    </div>

    <table id="tabelSiswa" class="table table-borderless table-responsive w-100 m-0 p-0" style="display: table !important;">
        <thead class="d-none">
            <tr>
                <th>Konten</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            while($row = mysqli_fetch_assoc($result)) { 
                $p_punishment = abs($row['total_poin_punishment']); 
                $p_reward     = abs($row['total_poin_reward']);
                $nis          = $row['nis'];
                
                // Menghitung poin bersih aktual
                $poin_clean = $p_punishment - $p_reward;
                
                if ($poin_clean > 0) {
                    $jenis_status = 'Punishment';
                    $poin_cari = $poin_clean;
                    $badge_class = 'bg-danger text-white';
                    $badge_class_mobile = 'bg-danger-subtle text-danger';
                    $tanda_poin = "- " . $poin_clean;
                } elseif ($poin_clean < 0) {
                    $jenis_status = 'Reward';
                    $poin_cari = abs($poin_clean); 
                    $badge_class = 'bg-success text-white';
                    $badge_class_mobile = 'bg-success-subtle text-success';
                    $tanda_poin = "+ " . abs($poin_clean);
                } else {
                    $jenis_status = 'Netral';
                    $poin_cari = 0;
                    $badge_class = 'bg-secondary text-white';
                    $badge_class_mobile = 'bg-secondary-subtle text-secondary';
                    $tanda_poin = "0";
                }

                // Ambil aturan keputusan tindakan dinamis dari tabel evaluasi_siswa
                if ($jenis_status != 'Netral') {
                    $query_kategori = "SELECT kategori, tindakan FROM evaluasi_siswa 
                                       WHERE jenis = '$jenis_status' AND poin <= $poin_cari 
                                       ORDER BY poin DESC LIMIT 1";
                    $res_kat = mysqli_query($koneksi, $query_kategori);
                    $data_kat = mysqli_fetch_assoc($res_kat);
                    
                    $kategori_tampil = $data_kat ? $data_kat['kategori'] : 'Normal / Pemantauan';
                    $tindakan_tampil = $data_kat ? $data_kat['tindakan'] : 'Kondisi stabil, pertahankan.';
                } else {
                    $kategori_tampil = 'Siswa Seimbang';
                    $tindakan_tampil = 'Berkelakuan Baik / Hak Poin Bersih Kembali Kosong';
                }
            ?>
            <tr data-status="<?php echo $jenis_status; ?>">
                <td class="p-0 border-0">

                    <div class="mobile-view student-mobile-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div style="max-width: 60%;">
                                <span class="text-muted small fw-semibold">NIS <?php echo $nis; ?></span>
                                <h6 class="fw-bold text-dark mb-1 mt-1 text-truncate" style="font-size: 15px;"><?php echo htmlspecialchars($row['nama_siswa']); ?></h6>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1" style="font-size: 11px;"><?php echo $row['kelas']; ?></span>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-1" style="max-width: 40%;">
                                <div class="meta-title text-muted" style="font-size: 9px; font-weight:600; text-transform: uppercase;">Poin Bersih</div>
                                <span class="badge-poin <?php echo $badge_class_mobile; ?> px-2 py-1 rounded">
                                    <i class="bi bi-activity"></i> <?php echo $tanda_poin; ?>
                                </span>
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between" style="border-top-style: dotted !important;">
                            <div class="small fw-semibold text-secondary" style="font-size: 11px;">KATEGORI: <span class="text-primary"><?php echo $kategori_tampil; ?></span></div>
                            <span class="badge-kategori-aktual <?php echo $badge_class_mobile; ?>" style="font-size: 10px;"><?php echo strtoupper($jenis_status); ?></span>
                        </div>
                        <?php if ($jenis_status != 'Netral'): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill w-100 mt-2 btn-tindak-lanjut"
                                data-id-siswa="<?php echo $row['id_siswa']; ?>"
                                data-nama="<?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES); ?>"
                                data-kategori="<?php echo $jenis_status; ?>"
                                data-poin-tersedia="<?php echo $poin_cari; ?>"
                                data-saran="<?php echo htmlspecialchars($tindakan_tampil, ENT_QUOTES); ?>">
                            <i class="bi bi-clipboard2-pulse me-1"></i> <?php echo $tindakan_tampil; ?>
                        </button>
                        <?php else: ?>
                        <div class="text-center small text-muted mt-2"><i class="bi bi-shield-check text-success me-1"></i>Kondisi stabil, tidak perlu tindak lanjut.</div>
                        <?php endif; ?>
                    </div>

                    <div class="desktop-view card border-0 shadow-sm p-3 mb-2" style="border-radius: 14px;">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['nama_siswa']); ?></span>
                                <span class="d-none"><?php echo htmlspecialchars($nis); ?></span>
                            </div>
                            <div class="col-md-1">
                                <span class="badge bg-primary-subtle text-primary px-2 py-1 fw-bold"><?php echo $row['kelas']; ?></span>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge <?php echo $badge_class; ?> rounded px-2 py-1"><?php echo $tanda_poin; ?> Poin</span>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge-kategori-aktual <?php echo $badge_class_mobile; ?>"><?php echo strtoupper($jenis_status); ?></span>
                            </div>
                            <div class="col-md-2">
                                <span class="fw-bold text-primary small d-block text-truncate"><?php echo $kategori_tampil; ?></span>
                            </div>
                            <div class="col-md-2">
                                <?php if ($jenis_status != 'Netral'): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill w-100 btn-tindak-lanjut"
                                        data-id-siswa="<?php echo $row['id_siswa']; ?>"
                                        data-nama="<?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES); ?>"
                                        data-kategori="<?php echo $jenis_status; ?>"
                                        data-poin-tersedia="<?php echo $poin_cari; ?>"
                                        data-saran="<?php echo htmlspecialchars($tindakan_tampil, ENT_QUOTES); ?>"
                                        title="<?php echo htmlspecialchars($tindakan_tampil, ENT_QUOTES); ?>">
                                    <i class="bi bi-clipboard2-pulse me-1"></i>
                                    <span class="text-truncate d-inline-block align-middle" style="max-width: 100px;"><?php echo $tindakan_tampil; ?></span>
                                </button>
                                <?php else: ?>
                                <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i>Stabil</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal Form Tindak Lanjut / Apresiasi -->
<div class="modal fade" id="modalTindakLanjut" data-bs-backdrop="static" tabindex="-1" aria-labelledby="titleModalTindak" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white py-3" style="border-radius: 20px 20px 0 0;">
                <h6 class="modal-title fw-bold" id="titleModalTindak"><i class="bi bi-clipboard2-pulse-fill me-1"></i> Form Tindak Lanjut / Apresiasi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" id="formTindakLanjut">
                <div class="modal-body p-4">
                    <input type="hidden" name="kategori_rp" id="tindak_kategori_rp">
                    <input type="hidden" name="id_siswa" id="tindak_id_siswa">

                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-semibold text-secondary">Nama Siswa</label>
                            <input type="text" id="tindak_nama_siswa" class="form-control bg-light fw-bold" readonly style="border-radius: 10px;">
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold text-secondary">Kategori</label>
                            <input type="text" id="tindak_kategori_display" class="form-control bg-light fw-bold" readonly style="border-radius: 10px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="poin_kurang" class="form-label small fw-semibold text-secondary" id="labelPoinKurang">Poin yang Dikurangi</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-warning-subtle text-warning-emphasis fw-bold border-end-0"><i class="bi bi-dash-circle-fill"></i></span>
                            <input type="number" name="poin_kurang" id="poin_kurang" class="form-control border-start-0 py-2 fw-semibold" placeholder="Contoh: 5" required min="1" style="border-radius: 0 10px 10px 0;">
                        </div>
                        <div class="form-text" id="hintPoinTersedia" style="font-size: 11px;"></div>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi_tindakan" class="form-label small fw-semibold text-secondary">Deskripsi Tindakan / Apresiasi</label>
                        <textarea name="deskripsi_tindakan" id="deskripsi_tindakan" class="form-control" rows="3" placeholder="Tulis tindakan/apresiasi yang diberikan..." required style="border-radius: 10px; font-size: 13px;"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi_tindaklanjut" class="form-label small fw-semibold text-secondary">Deskripsi Tindak Lanjut</label>
                        <textarea name="deskripsi_tindaklanjut" id="deskripsi_tindaklanjut" class="form-control" rows="3" placeholder="Tulis rencana / tindak lanjut selanjutnya..." required style="border-radius: 10px; font-size: 13px;"></textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-secondary">Foto Bukti (Real-time)</label>
                        <div class="upload-foto-box" id="uploadTindakBox">
                            <i class="bi bi-camera-fill"></i>
                            <div class="mt-2 small text-muted">Ketuk untuk ambil foto atau pilih dari galeri</div>
                            <input type="file" name="foto_tindak" id="foto_tindak" accept="image/*" capture="environment" class="d-none" required>
                        </div>
                        <div class="preview-foto-wrapper" id="previewTindakWrapper">
                            <button type="button" class="btn-hapus-foto" id="btnHapusTindakFoto"><i class="bi bi-x"></i></button>
                            <img id="previewTindakFoto" src="" alt="Preview foto bukti">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2.5" style="border-radius: 0 0 20px 20px;">
                    <button type="button" class="btn btn-sm btn-secondary px-3 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="proses_tindaklanjut" class="btn btn-sm btn-primary px-4 rounded-pill"><i class="bi bi-save2 me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi DataTables persis dengan konfigurasi Data Siswa
        var table = $('#tabelSiswa').DataTable({
            "ordering": false,
            "pageLength": 10,
            "responsive": true,
            "autoWidth": false,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
            "language": {
                "sProcessing":   "Sedang memproses...",
                "sLengthMenu":   "Tampilkan _MENU_ data",
                "sZeroRecords":  "Tidak ditemukan data siswa yang sesuai",
                "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa",
                "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 data",
                "oPaginate": {
                    "sFirst":    "Pertama",
                    "sPrevious": "Sebelumnya",
                    "sNext":     "Selanjutnya",
                    "sLast":     "Terakhir"
                }
            }
        });

        // Menghubungkan Input Pencarian Kustom Baru ke dalam Mesin DataTables secara Realtime
        $('#pencarianSiswa').on('keyup change', function() {
            table.search($(this).val()).draw();
        });

        // LOGIKA KUSTOM UNTUK FILTER REWARD & PUNISHMENT TANPA MENGGANGGU FITUR LAIN
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var selectedStatus = $('#filterKategoriRP').val();
                // Mengambil nilai data-status dari element TR yang sedang dievaluasi oleh DataTables
                var rowStatus = $(table.row(dataIndex).node()).attr('data-status');
                
                if (selectedStatus === "" || rowStatus === selectedStatus) {
                    return true;
                }
                return false;
            }
        );

        // Memicu gambar ulang tabel ketika pilihan filter kategori diubah
        $('#filterKategoriRP').on('change', function() {
            table.draw();
        });

        // ==== Handler Klik "Tindakan / Apresiasi" -> Buka Modal Form Tindak Lanjut ====
        $(document).on('click', '.btn-tindak-lanjut', function() {
            const idSiswa = this.getAttribute('data-id-siswa');
            const namaSiswa = this.getAttribute('data-nama');
            const kategori = this.getAttribute('data-kategori');
            const saran = this.getAttribute('data-saran');
            const poinTersedia = parseInt(this.getAttribute('data-poin-tersedia')) || 0;

            $('#tindak_id_siswa').val(idSiswa);
            $('#tindak_nama_siswa').val(namaSiswa);
            $('#tindak_kategori_rp').val(kategori);
            $('#tindak_kategori_display').val(kategori.toUpperCase());
            $('#deskripsi_tindakan').val(saran);
            $('#deskripsi_tindaklanjut').val('');

            // Label & batas poin menyesuaikan kategori (Reward atau Punishment) siswa saat ini
            $('#labelPoinKurang').text('Poin ' + kategori + ' yang Dikurangi');
            $('#poin_kurang').attr('max', poinTersedia).val('');
            $('#hintPoinTersedia').html('Poin ' + kategori + ' tersedia saat ini: <strong>' + poinTersedia + ' Poin</strong>');

            // Reset input & preview foto setiap kali form dibuka untuk siswa baru
            document.getElementById('foto_tindak').value = '';
            document.getElementById('previewTindakFoto').src = '';
            document.getElementById('previewTindakWrapper').style.display = 'none';
            document.getElementById('uploadTindakBox').style.display = 'block';

            $('#modalTindakLanjut').modal('show');
        });
    });

    // ==== Upload Foto Bukti Tindak Lanjut + Preview Real-time ====
    const uploadTindakBox = document.getElementById('uploadTindakBox');
    const fotoTindakInput = document.getElementById('foto_tindak');
    const previewTindakWrapper = document.getElementById('previewTindakWrapper');
    const previewTindakFoto = document.getElementById('previewTindakFoto');
    const btnHapusTindakFoto = document.getElementById('btnHapusTindakFoto');

    uploadTindakBox.addEventListener('click', () => fotoTindakInput.click());

    fotoTindakInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                previewTindakFoto.src = e.target.result;
                previewTindakWrapper.style.display = 'block';
                uploadTindakBox.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

    btnHapusTindakFoto.addEventListener('click', function() {
        fotoTindakInput.value = '';
        previewTindakFoto.src = '';
        previewTindakWrapper.style.display = 'none';
        uploadTindakBox.style.display = 'block';
    });

    // ==== Validasi Form Tindak Lanjut Sebelum Dikirim ====
    document.getElementById('formTindakLanjut').addEventListener('submit', function(e) {
        const poinInput = document.getElementById('poin_kurang');
        const poinMax = parseInt(poinInput.getAttribute('max')) || 0;
        const poinVal = parseInt(poinInput.value) || 0;

        if (!fotoTindakInput.files[0]) {
            e.preventDefault();
            Swal.fire('Foto Wajib Diunggah', 'Silakan unggah foto bukti realtime terlebih dahulu.', 'warning');
            return;
        }

        if (poinVal <= 0) {
            e.preventDefault();
            Swal.fire('Poin Tidak Valid', 'Poin yang dikurangi minimal bernilai 1.', 'warning');
            return;
        }

        if (poinMax > 0 && poinVal > poinMax) {
            e.preventDefault();
            Swal.fire('Poin Melebihi Batas', 'Poin yang dikurangi tidak boleh melebihi poin yang tersedia (' + poinMax + ' Poin).', 'warning');
            return;
        }
    });
</script>
</body>
</html>