<?php
session_start();
if (($_SESSION['status'] ?? '') !== 'login' || !in_array($_SESSION['role'] ?? '', ['Admin', 'BK', 'Kepsek'], true)) {
    header('location:index.php');
    exit();
}

include 'koneksi.php';
include 'periode_helper.php';

$id_laporan = (int)($_POST['id_laporan'] ?? 0);
$aksi = $_POST['aksi'] ?? '';
$catatan = trim($_POST['catatan_verifikasi'] ?? '');

if ($id_laporan <= 0 || !in_array($aksi, ['disetujui', 'ditolak'], true)) {
    $_SESSION['status_verifikasi'] = 'gagal';
    $_SESSION['pesan_verifikasi'] = 'Permintaan verifikasi tidak valid.';
    header('location:verifikasi_laporan.php');
    exit();
}

$id_verifikator = (int)($_SESSION['id_user'] ?? 0);
$catatan_db = mysqli_real_escape_string($koneksi, $catatan);

mysqli_begin_transaction($koneksi);
try {
    $q_laporan = mysqli_query($koneksi, "SELECT id_siswa, id_periode, label_prediksi, poin_didapat, status_verifikasi FROM laporan_prilaku WHERE id_laporan = $id_laporan FOR UPDATE");
    $laporan = $q_laporan ? mysqli_fetch_assoc($q_laporan) : null;

    if (!$laporan) {
        throw new Exception('Laporan tidak ditemukan.');
    }

    if ($laporan['status_verifikasi'] !== 'pending') {
        throw new Exception('Laporan ini sudah diproses sebelumnya.');
    }

    $sql_verifikasi = "UPDATE laporan_prilaku
                       SET status_verifikasi = '$aksi', id_verifikator = $id_verifikator,
                           tgl_verifikasi = NOW(), catatan_verifikasi = '$catatan_db'
                       WHERE id_laporan = $id_laporan AND status_verifikasi = 'pending'";
    if (!mysqli_query($koneksi, $sql_verifikasi) || mysqli_affected_rows($koneksi) !== 1) {
        throw new Exception('Status laporan gagal diperbarui.');
    }

    // Poin pada siswa adalah saldo periode aktif. Persetujuan laporan arsip
    // tetap dicatat, namun tidak mengubah saldo tahun ajaran yang baru.
    if ($aksi === 'disetujui' && (int)$laporan['poin_didapat'] > 0 && (int)$laporan['id_periode'] === id_periode_aktif($koneksi)) {
        $kolom_poin = $laporan['label_prediksi'] === 'Reward' ? 'total_poin_reward' : 'total_poin_punishment';
        $poin = (int)$laporan['poin_didapat'];
        $id_siswa = (int)$laporan['id_siswa'];
        if (!mysqli_query($koneksi, "UPDATE siswa SET $kolom_poin = $kolom_poin + $poin WHERE id_siswa = $id_siswa")) {
            throw new Exception('Poin siswa gagal diperbarui.');
        }
    }

    mysqli_commit($koneksi);
    $_SESSION['status_verifikasi'] = 'sukses';
    $_SESSION['pesan_verifikasi'] = $aksi === 'disetujui'
        ? 'Laporan disetujui dan poin siswa telah dihitung.'
        : 'Laporan telah ditolak. Poin siswa tidak berubah.';
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $_SESSION['status_verifikasi'] = 'gagal';
    $_SESSION['pesan_verifikasi'] = $e->getMessage();
}

header('location:verifikasi_laporan.php');
exit();
?>
