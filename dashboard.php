<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Rekap Poin Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-row" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h2>Dashboard Monitoring Poin Perilaku Siswa</h2>
        <div>
            <a href="laporan_kelas.php" class="btn">Laporan Per Kelas</a>
            <a href="input_laporan.php" class="btn">+ Input Laporan Baru</a>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Poin Reward (+)</th>
                <th>Poin Punishment (-)</th>
                <th>Keputusan</th>
                <th>Status / Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = mysqli_query($conn, "SELECT * FROM siswa");
            while ($row = mysqli_fetch_assoc($query)) {
                $reward = $row['total_poin_reward'];
                $punishment = $row['total_poin_punishment'];
                
                // Keputusan berdasarkan perbandingan poin reward vs punishment
                if ($reward > $punishment) {
                    $keputusan = "<span class='badge badge-success'>Reward</span>";
                } elseif ($punishment > $reward) {
                    $keputusan = "<span class='badge badge-danger'>Punishment</span>";
                } else {
                    $keputusan = "<span class='badge badge-warning'>Seimbang</span>";
                }
                
                // Logika status ambang batas
                if ($punishment >= 50) {
                    $status = "<span class='badge badge-danger'>Butuh Tindak Lanjut BK</span>";
                } elseif ($punishment >= 20) {
                    $status = "<span class='badge badge-warning'>Peringatan Ringan</span>";
                } else {
                    $status = "<span class='badge badge-success'>Aman</span>";
                }
                
                echo "<tr>
                        <td>{$row['nis']}</td>
                        <td>{$row['nama_siswa']}</td>
                        <td>{$row['kelas']}</td>
                        <td><strong style='color:green;'>+{$reward}</strong></td>
                        <td><strong style='color:red;'>-{$punishment}</strong></td>
                        <td>$keputusan</td>
                        <td>$status</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>