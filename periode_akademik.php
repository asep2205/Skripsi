<?php
session_start();
if (($_SESSION['status'] ?? '') !== 'login' || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('location:index.php'); exit();
}
include 'koneksi.php';
include 'periode_helper.php';

$pesan = $_SESSION['pesan_periode'] ?? '';
$status = $_SESSION['status_periode'] ?? '';
unset($_SESSION['pesan_periode'], $_SESSION['status_periode']);
$periodeAktif = periode_aktif($koneksi);
$periodeList = mysqli_query($koneksi, 'SELECT * FROM periode_akademik ORDER BY tanggal_mulai DESC, id_periode DESC');
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Periode Akademik</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"></head>
<body class="bg-light"><header class="bg-primary text-white py-3 shadow-sm"><div class="container d-flex align-items-center gap-3"><a href="dashboard" class="text-white fs-4"><i class="bi bi-arrow-left"></i></a><h5 class="mb-0 fw-bold">Periode Tahun Ajaran</h5></div></header>
<main class="container py-4" style="max-width:850px">
<?php if ($pesan): ?><div class="alert alert-<?= $status === 'sukses' ? 'success' : 'danger' ?>"><?= htmlspecialchars($pesan) ?></div><?php endif; ?>
<div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>Aktivasi periode baru akan mengarsipkan periode sebelumnya dan me-reset total poin aktif seluruh siswa menjadi 0. Riwayat transaksi tidak dihapus.</div>
<div class="card border-0 shadow-sm mb-4"><div class="card-body"><h6 class="fw-bold">Buat tahun ajaran baru</h6><form method="post" action="proses_periode_akademik.php" class="row g-3"><input type="hidden" name="aksi" value="buat"><div class="col-md-5"><label class="form-label">Tahun ajaran</label><input name="tahun_ajaran" class="form-control" placeholder="2026/2027" pattern="[0-9]{4}/[0-9]{4}" required></div><div class="col-md-7"><label class="form-label">Rentang tahun ajaran</label><div class="input-group"><input type="date" name="tanggal_mulai" class="form-control" required><input type="date" name="tanggal_selesai" class="form-control" required></div></div><div class="col-12"><button class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Simpan sebagai arsip</button></div></form></div></div>
<div class="card border-0 shadow-sm"><div class="card-body"><h6 class="fw-bold">Daftar tahun ajaran</h6><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Tahun ajaran</th><th>Rentang</th><th>Status</th><th></th></tr></thead><tbody><?php while ($p = mysqli_fetch_assoc($periodeList)): ?><tr><td><?= htmlspecialchars($p['tahun_ajaran']) ?></td><td><?= date('d-m-Y', strtotime($p['tanggal_mulai'])) ?> — <?= date('d-m-Y', strtotime($p['tanggal_selesai'])) ?></td><td><span class="badge text-bg-<?= $p['status'] === 'aktif' ? 'success' : 'secondary' ?>"><?= ucfirst($p['status']) ?></span></td><td><?php if ($p['status'] !== 'aktif'): ?><form method="post" action="proses_periode_akademik.php" onsubmit="return confirm('Aktifkan tahun ajaran ini? Total poin aktif seluruh siswa akan di-reset menjadi 0.');"><input type="hidden" name="aksi" value="aktifkan"><input type="hidden" name="id_periode" value="<?= (int)$p['id_periode'] ?>"><button class="btn btn-sm btn-outline-primary">Aktifkan & reset poin</button></form><?php endif; ?></td></tr><?php endwhile; ?></tbody></table></div></div></div></main></body></html>
