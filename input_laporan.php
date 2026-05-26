<?php
include 'config.php';
include 'nlp_engine.php';

// Simulasi session login user (Guru) jika belum ada sistem login lengkap
$id_user_login = 2; // Default ke id_user 2 (Guru1)

$notif_pesan = "";

if (isset($_POST['submit_laporan'])) {
    $id_siswa = $_POST['id_siswa'];
    $teks_laporan = $_POST['teks_laporan'];
    
    // --- Ambil riwayat poin siswa sebelum diproses ---
    $query_siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE id_siswa = '$id_siswa'");
    $data_siswa = mysqli_fetch_assoc($query_siswa);
    $poin_reward_lama = $data_siswa['total_poin_reward'];
    $poin_punishment_lama = $data_siswa['total_poin_punishment'];
    
    // =====================================================================
    // KOMBINASI REWARD + PUNISHMENT: Cocokkan SEMUA aturan dari master_poin
    // =====================================================================
    $query_all = mysqli_query($conn, "SELECT * FROM master_poin");
    $total_poin_cocok_reward = 0;
    $total_poin_cocok_punishment = 0;
    $daftar_reward_tercocok = [];
    $daftar_punishment_tercocok = [];
    $reward_terbaik = null;
    $punishment_terbaik = null;

    while ($row = mysqli_fetch_assoc($query_all)) {
        if (strpos(strtolower($teks_laporan), strtolower($row['nama_perilaku'])) !== false) {
            if ($row['jenis'] == 'Reward') {
                $total_poin_cocok_reward += $row['poin'];
                $daftar_reward_tercocok[] = $row;
                if (!$reward_terbaik || $row['poin'] > $reward_terbaik['poin']) {
                    $reward_terbaik = $row;
                }
            } else {
                $total_poin_cocok_punishment += $row['poin'];
                $daftar_punishment_tercocok[] = $row;
                if (!$punishment_terbaik || $row['poin'] > $punishment_terbaik['poin']) {
                    $punishment_terbaik = $row;
                }
            }
        }
    }

    // Tentukan label berdasarkan perbandingan poin dari aturan yang cocok
    if ($total_poin_cocok_punishment > $total_poin_cocok_reward) {
        $label_hasil = 'Punishment';
        $aturan = $punishment_terbaik;
    } elseif ($total_poin_cocok_reward > $total_poin_cocok_punishment) {
        $label_hasil = 'Reward';
        $aturan = $reward_terbaik;
    } else {
        // Jika seri atau tidak ada yang cocok → fallback ke NLP
        $label_hasil = klasifikasi_naive_bayes($conn, $teks_laporan, $poin_reward_lama, $poin_punishment_lama);
        // Cari aturan sesuai label hasil NLP
        $query_label = mysqli_query($conn, "SELECT * FROM master_poin WHERE jenis = '$label_hasil'");
        $aturan = null;
        while ($row = mysqli_fetch_assoc($query_label)) {
            if (!$aturan) $aturan = $row;
            if (strpos(strtolower($teks_laporan), strtolower($row['nama_perilaku'])) !== false) {
                $aturan = $row;
                break;
            }
        }
    }

    $id_aturan = $aturan ? $aturan['id_aturan'] : null;
    $poin_didapat = $aturan ? $aturan['poin'] : 0;
    
    // --- Simpan Transaksi Laporan ---
    $insert = mysqli_query($conn, "INSERT INTO laporan_perilaku (id_siswa, id_user, teks_laporan, label_prediksi, id_aturan_tercocok, poin_didapat) 
               VALUES ('$id_siswa', '$id_user_login', '$teks_laporan', '$label_hasil', '$id_aturan', '$poin_didapat')");
    
    if ($insert) {
        // --- Update Akumulasi Profil Poin Siswa ---
        if ($label_hasil == 'Reward') {
            mysqli_query($conn, "UPDATE siswa SET total_poin_reward = total_poin_reward + $poin_didapat WHERE id_siswa = '$id_siswa'");
        } else {
            mysqli_query($conn, "UPDATE siswa SET total_poin_punishment = total_poin_punishment + $poin_didapat WHERE id_siswa = '$id_siswa'");
        }
        
        // --- Ambil data siswa setelah update ---
        $query_siswa_baru = mysqli_query($conn, "SELECT * FROM siswa WHERE id_siswa = '$id_siswa'");
        $data_siswa_baru = mysqli_fetch_assoc($query_siswa_baru);
        
        // --- Bangun notifikasi detail ---
        $detail_reward = '';
        if (count($daftar_reward_tercocok) > 0) {
            $detail_reward = '<div style="margin-top:8px;"><strong>Reward:</strong> ';
            $reward_items = [];
            foreach ($daftar_reward_tercocok as $r) {
                $reward_items[] = "{$r['nama_perilaku']} (+{$r['poin']} poin)";
            }
            $detail_reward .= implode('<br>', $reward_items);
            $detail_reward .= "<br><em>Total Reward: {$total_poin_cocok_reward} poin</em></div>";
        }
        
        $detail_punishment = '';
        if (count($daftar_punishment_tercocok) > 0) {
            $detail_punishment = '<div style="margin-top:5px;"><strong>Punishment:</strong> ';
            $puni_items = [];
            foreach ($daftar_punishment_tercocok as $p) {
                $puni_items[] = "{$p['nama_perilaku']} (-{$p['poin']} poin)";
            }
            $detail_punishment .= implode('<br>', $puni_items);
            $detail_punishment .= "<br><em>Total Punishment: {$total_poin_cocok_punishment} poin</em></div>";
        }
        
        // Tentukan keputusan akhir dari perbandingan
        $reward_total = $data_siswa_baru['total_poin_reward'];
        $punishment_total = $data_siswa_baru['total_poin_punishment'];
        if ($reward_total > $punishment_total) {
            $keputusan = "<strong style='color:green;'>REWARD</strong> — Poin Reward ({$reward_total}) > Poin Punishment ({$punishment_total})";
        } elseif ($punishment_total > $reward_total) {
            $keputusan = "<strong style='color:red;'>PUNISHMENT</strong> — Poin Punishment ({$punishment_total}) > Poin Reward ({$reward_total})";
        } else {
            $keputusan = "<strong style='color:orange;'>SEIMBANG</strong> — Poin Reward ({$reward_total}) = Poin Punishment ({$punishment_total})";
        }
        
        $notif_pesan = "<div class='alert success'><strong>Sistem Berhasil Memproses!</strong><br>
                        Hasil Analisis: <strong>$label_hasil</strong><br>
                        Tindakan: {$aturan['nama_perilaku']} ($poin_didapat Poin)
                        {$detail_reward}
                        {$detail_punishment}
                        <hr>
                        <strong>Keputusan Akhir: {$keputusan}</strong></div>";
                        
        // Threshold check: Jika poin punishment mencapai atau melebihi 50 poin
        if ($punishment_total >= 50) {
            $notif_pesan .= "<div class='alert danger'><strong>⚠️ NOTIFIKASI TINDAK LANJUT:</strong> Poin pelanggaran siswa <strong>{$data_siswa_baru['nama_siswa']}</strong> telah mencapai {$punishment_total} poin. Segera hubungi Guru BK!</div>";
        }
    } else {
        $notif_pesan = "<div class='alert danger'>Gagal memproses laporan.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Laporan Perilaku - Sistem Reward & Punishment</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 700px; background: #fff; padding: 25px; margin: auto; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        select, textarea, button { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 120px; resize: vertical; }
        button { background-color: #4CAF50; color: white; font-size: 16px; border: none; cursor: pointer; font-weight: bold; margin-top: 10px; }
        button:hover { background-color: #45a049; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; line-height: 1.6; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .nav-link { display: inline-block; margin-top: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>

<div class="container">
    <h2>Input Laporan Perilaku Siswa</h2>
    
    <?php echo $notif_pesan; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="id_siswa">Pilih Siswa:</label>
            <select name="id_siswa" id="id_siswa" required>
                <option value="">-- Pilih Siswa --</option>
                <?php
                $siswa_query = mysqli_query($conn, "SELECT * FROM siswa");
                while($row = mysqli_fetch_assoc($siswa_query)) {
                    echo "<option value='{$row['id_siswa']}'>{$row['nama_siswa']} ({$row['kelas']})</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="teks_laporan">Deskripsi Perilaku (Teks Bebas):</label>
            <textarea name="teks_laporan" id="teks_laporan" placeholder="Contoh: Siswa tadi siang ikut membantu merapikan buku di perpustakaan bersama wali kelas..." required></textarea>
        </div>

        <button type="submit" name="submit_laporan">Proses & Simpan Laporan</button>
    </form>
    
    <a href="dashboard.php" class="nav-link">← Lihat Rekap & Dashboard Siswa</a>
</div>

</body>
</html>