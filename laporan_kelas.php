<?php
include 'config.php';

$view = $_GET['view'] ?? 'kelas';
$kelas = $_GET['kelas'] ?? '';
$siswa_id = $_GET['siswa'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Per Kelas - Sistem Reward & Punishment</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-info { background-color: #17a2b8; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 8px 14px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 13px; }
        .btn:hover { background: #0056b3; }
        .btn-sm { padding: 4px 10px; font-size: 12px; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-warning:hover { background: #e0a800; }
        .nav { margin-bottom: 15px; padding: 10px 0; border-bottom: 1px solid #ddd; }
        .nav a { margin-right: 12px; }
        .text-green { color: #28a745; font-weight: bold; }
        .text-red { color: #dc3545; font-weight: bold; }
        .text-muted { color: #666; font-size: 13px; }
        .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-row">
        <h2>
            <?php if ($view == 'siswa' && $siswa_id): ?>
                Detail Laporan Siswa
            <?php elseif ($view == 'kelas' && $kelas): ?>
                Siswa Kelas: <?= htmlspecialchars($kelas) ?>
            <?php else: ?>
                Rekap Poin Per Kelas
            <?php endif; ?>
        </h2>
        <div>
            <?php if ($view == 'kelas' && $kelas): ?>
                <a href="laporan_kelas.php" class="btn">&larr; Kembali ke Kelas</a>
            <?php elseif ($view == 'siswa'): ?>
                <a href="laporan_kelas.php?kelas=<?= urlencode($kelas) ?>" class="btn">&larr; Kembali ke Kelas</a>
            <?php endif; ?>
            <a href="dashboard.php" class="btn btn-success">Dashboard Siswa</a>
            <a href="input_laporan.php" class="btn btn-warning">Input Laporan</a>
        </div>
    </div>

    <?php if ($view == 'siswa' && $siswa_id): ?>
        <?php
        $q = mysqli_query($conn, "SELECT * FROM siswa WHERE id_siswa = '$siswa_id'");
        $siswa = mysqli_fetch_assoc($q);
        if (!$siswa) { echo "<p>Siswa tidak ditemukan.</p>"; exit; }
        $kelas = $siswa['kelas'];
        ?>
        <div style="margin-bottom:15px;padding:12px;background:#e9f7fe;border-radius:4px;">
            <strong><?= htmlspecialchars($siswa['nama_siswa']) ?></strong> (<?= htmlspecialchars($siswa['nis']) ?>) - <?= htmlspecialchars($siswa['kelas']) ?><br>
            <span class="text-green">Total Reward: +<?= $siswa['total_poin_reward'] ?></span> |
            <span class="text-red">Total Punishment: -<?= $siswa['total_poin_punishment'] ?></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Teks Laporan</th>
                    <th>Label</th>
                    <th>Aturan Tercocok</th>
                    <th>Poin</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($conn, "
                    SELECT lp.*, mp.nama_perilaku 
                    FROM laporan_perilaku lp 
                    LEFT JOIN master_poin mp ON lp.id_aturan_tercocok = mp.id_aturan 
                    WHERE lp.id_siswa = '$siswa_id' 
                    ORDER BY lp.tgl_input DESC
                ");
                $no = 1;
                while ($r = mysqli_fetch_assoc($q)):
                    $label_class = $r['label_prediksi'] == 'Reward' ? 'badge-success' : 'badge-danger';
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($r['tgl_input'])) ?></td>
                    <td><?= htmlspecialchars($r['teks_laporan']) ?></td>
                    <td><span class="badge <?= $label_class ?>"><?= $r['label_prediksi'] ?></span></td>
                    <td><?= htmlspecialchars($r['nama_perilaku'] ?? '-') ?></td>
                    <td><strong><?= $r['label_prediksi'] == 'Reward' ? '+' : '-' ?><?= $r['poin_didapat'] ?></strong></td>
                </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($q) == 0): ?>
                <tr><td colspan="6" style="text-align:center;color:#999;">Belum ada laporan</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($view == 'kelas' && $kelas): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Poin Reward (+)</th>
                    <th>Poin Punishment (-)</th>
                    <th>Keputusan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($conn, "SELECT * FROM siswa WHERE kelas = '$kelas' ORDER BY total_poin_punishment DESC, total_poin_reward DESC");
                $no = 1;
                while ($r = mysqli_fetch_assoc($q)):
                    $reward = $r['total_poin_reward'];
                    $punishment = $r['total_poin_punishment'];
                    if ($reward > $punishment) {
                        $keputusan = "<span class='badge badge-success'>Reward</span>";
                    } elseif ($punishment > $reward) {
                        $keputusan = "<span class='badge badge-danger'>Punishment</span>";
                    } else {
                        $keputusan = "<span class='badge badge-warning'>Seimbang</span>";
                    }
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($r['nis']) ?></td>
                    <td><?= htmlspecialchars($r['nama_siswa']) ?></td>
                    <td class="text-green">+<?= $reward ?></td>
                    <td class="text-red">-<?= $punishment ?></td>
                    <td><?= $keputusan ?></td>
                    <td><a href="laporan_kelas.php?view=siswa&siswa=<?= $r['id_siswa'] ?>&kelas=<?= urlencode($kelas) ?>" class="btn btn-sm">Detail</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php else: ?>
        <?php
        $q = mysqli_query($conn, "
            SELECT kelas, 
                   COUNT(*) as jumlah_siswa, 
                   SUM(total_poin_reward) as total_reward, 
                   SUM(total_poin_punishment) as total_punishment,
                   ROUND(AVG(total_poin_reward), 1) as avg_reward,
                   ROUND(AVG(total_poin_punishment), 1) as avg_punishment
            FROM siswa 
            GROUP BY kelas 
            ORDER BY total_punishment DESC, total_reward DESC
        ");
        ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kelas</th>
                    <th>Jumlah Siswa</th>
                    <th>Total Poin Reward (+)</th>
                    <th>Total Poin Punishment (-)</th>
                    <th>Rata-rata Reward</th>
                    <th>Rata-rata Punishment</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while ($r = mysqli_fetch_assoc($q)):
                    $reward_class = $r['total_reward'] >= $r['total_punishment'] ? 'text-green' : '';
                    $punish_class = $r['total_punishment'] > $r['total_reward'] ? 'text-red' : '';
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($r['kelas']) ?></strong></td>
                    <td><?= $r['jumlah_siswa'] ?> orang</td>
                    <td class="<?= $reward_class ?>">+<?= $r['total_reward'] ?></td>
                    <td class="<?= $punish_class ?>">-<?= $r['total_punishment'] ?></td>
                    <td>+<?= $r['avg_reward'] ?></td>
                    <td>-<?= $r['avg_punishment'] ?></td>
                    <td><a href="laporan_kelas.php?kelas=<?= urlencode($r['kelas']) ?>" class="btn btn-sm">Lihat Siswa</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
