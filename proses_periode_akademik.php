<?php
session_start();
if (($_SESSION['status'] ?? '') !== 'login' || ($_SESSION['role'] ?? '') !== 'Admin') { header('location:index.php'); exit(); }
include 'koneksi.php';

function kembali_periode(string $status, string $pesan): void { $_SESSION['status_periode'] = $status; $_SESSION['pesan_periode'] = $pesan; header('location:periode_akademik.php'); exit(); }
$aksi = $_POST['aksi'] ?? '';
if ($aksi === 'buat') {
    $tahun = trim($_POST['tahun_ajaran'] ?? '');
    $mulai = $_POST['tanggal_mulai'] ?? ''; $selesai = $_POST['tanggal_selesai'] ?? '';
    if (!preg_match('/^\d{4}\/\d{4}$/', $tahun) || !$mulai || !$selesai || $mulai > $selesai) kembali_periode('gagal', 'Data tahun ajaran tidak valid.');
    $stmt = mysqli_prepare($koneksi, 'INSERT INTO periode_akademik (tahun_ajaran, tanggal_mulai, tanggal_selesai) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'sss', $tahun, $mulai, $selesai);
    if (!mysqli_stmt_execute($stmt)) kembali_periode('gagal', 'Periode gagal disimpan. Kombinasi tahun ajaran dan semester sudah mungkin sudah ada.');
    kembali_periode('sukses', 'Periode berhasil dibuat. Aktifkan saat tahun ajaran dimulai.');
}
if ($aksi === 'aktifkan') {
    $id = (int)($_POST['id_periode'] ?? 0);
    if ($id <= 0) kembali_periode('gagal', 'Periode tidak valid.');
    mysqli_begin_transaction($koneksi);
    try {
        $tabel_rekap = mysqli_query($koneksi, "SHOW TABLES LIKE 'rekap_poin_periode'");
        if (!$tabel_rekap || mysqli_num_rows($tabel_rekap) === 0) {
            throw new Exception('Tabel rekap belum dibuat. Jalankan file migrasi_rekap_poin_periode.sql di database db_perilaku terlebih dahulu.');
        }
        $cek = mysqli_query($koneksi, "SELECT id_periode FROM periode_akademik WHERE id_periode = $id FOR UPDATE");
        if (!$cek || !mysqli_fetch_assoc($cek)) throw new Exception('Periode tidak ditemukan.');
        // Simpan saldo periode yang sedang aktif sebelum di-reset. Dengan ini
        // papan peringkat periode lama tetap utuh setelah naik kelas.
        $periode_lama = mysqli_query($koneksi, "SELECT id_periode FROM periode_akademik WHERE status = 'aktif' FOR UPDATE");
        if ($periode_lama && ($lama = mysqli_fetch_assoc($periode_lama))) {
            $id_lama = (int)$lama['id_periode'];
            $snapshot = "INSERT INTO rekap_poin_periode (id_periode, id_siswa, total_poin_reward, total_poin_punishment)
                         SELECT $id_lama, id_siswa, total_poin_reward, total_poin_punishment FROM siswa
                         ON DUPLICATE KEY UPDATE total_poin_reward = VALUES(total_poin_reward), total_poin_punishment = VALUES(total_poin_punishment)";
            if (!mysqli_query($koneksi, $snapshot)) throw new Exception('Gagal menyimpan rekap poin periode sebelumnya: ' . mysqli_error($koneksi));
        }
        if (!mysqli_query($koneksi, "UPDATE periode_akademik SET status = 'arsip' WHERE status = 'aktif'")) throw new Exception('Gagal mengarsipkan periode sebelumnya.');
        if (!mysqli_query($koneksi, "UPDATE periode_akademik SET status = 'aktif' WHERE id_periode = $id")) throw new Exception('Gagal mengaktifkan periode.');
        if (!mysqli_query($koneksi, 'UPDATE siswa SET total_poin_reward = 0, total_poin_punishment = 0')) throw new Exception('Gagal me-reset poin siswa.');
        mysqli_commit($koneksi);
        kembali_periode('sukses', 'Periode aktif berubah dan poin aktif seluruh siswa telah di-reset.');
    } catch (Exception $e) { mysqli_rollback($koneksi); kembali_periode('gagal', $e->getMessage()); }
}
kembali_periode('gagal', 'Aksi tidak dikenali.');
?>
