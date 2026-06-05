<?php
include 'config.php';

// ============================================================
// ENGINE SELECTION
// ============================================================
define('PYTHON_BIN', 'python3');
define('PYTHON_ENGINE_SCRIPT', __DIR__ . '/nlp_engine_web.py');

$id_user_login = 2;

$notif_pesan = "";

if (isset($_POST['submit_laporan'])) {
    $id_siswa = $_POST['id_siswa'];
    $teks_laporan = trim($_POST['teks_laporan']);

    // --- Validasi input ---
    $errors = [];
    if (strlen($teks_laporan) < 15) {
        $errors[] = "Deskripsi terlalu pendek (minimal 15 karakter).";
    }
    if (strlen($teks_laporan) > 500) {
        $errors[] = "Deskripsi terlalu panjang (maksimal 500 karakter).";
    }
    $word_count = str_word_count($teks_laporan);
    if ($word_count < 3) {
        $errors[] = "Deskripsi harus mengandung minimal 3 kata.";
    }
    $non_alpha_ratio = 0;
    if (strlen($teks_laporan) > 0) {
        $alpha = preg_match_all('/[a-zA-Z]/', $teks_laporan);
        $non_alpha_ratio = 1 - ($alpha / strlen($teks_laporan));
    }
    if ($non_alpha_ratio > 0.5) {
        $errors[] = "Deskripsi terlalu banyak mengandung angka/simbol, gunakan kalimat yang wajar.";
    }
    if (preg_match('/(.)\1{4,}/', $teks_laporan)) {
        $errors[] = "Deskripsi mengandung karakter berulang tidak wajar.";
    }

    if (!empty($errors)) {
        $tolak_laporan = true;
        $notif_pesan = "<div class='alert danger'><strong>Input Tidak Valid!</strong><br>" . implode("<br>", $errors) . "</div>";
    } else {
    
    // --- Ambil riwayat poin siswa sebelum diproses ---
    $query_siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE id_siswa = '$id_siswa'");
    $data_siswa = mysqli_fetch_assoc($query_siswa);
    $poin_reward_lama = $data_siswa['total_poin_reward'];
    $poin_punishment_lama = $data_siswa['total_poin_punishment'];
    
    // =====================================================================
    // PANGGIL PYTHON ENGINE
    // =====================================================================

    $cmd = PYTHON_BIN . ' ' . escapeshellarg(PYTHON_ENGINE_SCRIPT) . ' ' . escapeshellarg($teks_laporan);
    $output = shell_exec($cmd);
    $py_result = json_decode($output, true);

    if (!$py_result) {
        $tolak_laporan = true;
        $notif_pesan = "<div class='alert danger'><strong>Gagal Memproses!</strong> Python engine tidak merespon. Silakan coba lagi.</div>";
        $label_hasil = '';
        $nama_perilaku = '';
        $poin_didapat = 0;
    } elseif (isset($py_result['dikenali']) && $py_result['dikenali'] === false) {
        $tolak_laporan = true;
        $notif_pesan = "<div class='alert danger'><strong>Teks Tidak Dikenali!</strong> Deskripsi yang Anda masukkan tidak mengandung kata yang dikenali oleh sistem. Gunakan kalimat yang mendeskripsikan perilaku siswa dengan jelas (contoh: siswa membantu membersihkan kelas, siswa terlambat masuk sekolah, dll).</div>";
        $label_hasil = '';
        $nama_perilaku = '';
        $poin_didapat = 0;
    } elseif (
        (isset($py_result['garbage_flags']['consecutive_no_vowel']) && $py_result['garbage_flags']['consecutive_no_vowel'] >= 2) ||
        (isset($py_result['garbage_flags']['long_consonant_run']) && $py_result['garbage_flags']['long_consonant_run'] >= 1)
    ) {
        $tolak_laporan = true;
        $notif_pesan = "<div class='alert danger'><strong>Teks Tidak Valid!</strong> Deskripsi mengandung kata-kata yang tidak wajar (bukan bahasa Indonesia). Harap masukkan deskripsi perilaku siswa yang benar.</div>";
        $label_hasil = '';
        $nama_perilaku = '';
        $poin_didapat = 0;
    } else {
        $tolak_laporan = false;
        $label_hasil = $py_result['label'];
        $total_poin_cocok_reward = $py_result['total_poin_reward'];
        $total_poin_cocok_punishment = $py_result['total_poin_punishment'];

        $daftar_reward_tercocok = [];
        foreach ($py_result['daftar_reward'] as $r) {
            $daftar_reward_tercocok[] = [
                'id_aturan' => $r[0], 'nama_perilaku' => $r[1], 'poin' => $r[2], 'jenis' => 'Reward'
            ];
        }
        $daftar_punishment_tercocok = [];
        foreach ($py_result['daftar_punishment'] as $p) {
            $daftar_punishment_tercocok[] = [
                'id_aturan' => $p[0], 'nama_perilaku' => $p[1], 'poin' => $p[2], 'jenis' => 'Punishment'
            ];
        }

        $aturan = null;
        $nama_perilaku = '';
        if ($py_result['aturan']) {
            $aturan = [
                'id_aturan' => $py_result['aturan']['id'],
                'nama_perilaku' => $py_result['aturan']['nama'],
                'poin' => $py_result['aturan']['poin'],
            ];
            $nama_perilaku = $aturan['nama_perilaku'];
        }

        $id_aturan = $aturan ? $aturan['id_aturan'] : null;
        $poin_didapat = $aturan ? $aturan['poin'] : 0;
    }

    if ($tolak_laporan) {
        // Laporan ditolak, tidak disimpan
    } else {
        // --- Simpan Transaksi Laporan ---
        $insert = mysqli_query($conn, "INSERT INTO laporan_perilaku (id_siswa, id_user, teks_laporan, label_prediksi, nama_perilaku, poin_didapat) 
                   VALUES ('$id_siswa', '$id_user_login', '$teks_laporan', '$label_hasil', '$nama_perilaku', '$poin_didapat')");

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

            $tindakan_label = $aturan ? $aturan['nama_perilaku'] : 'Naive Bayes';
            $notif_pesan = "<div class='alert success'><strong>Sistem Berhasil Memproses!</strong><br>
                            Hasil Analisis: <strong>$label_hasil</strong><br>
                            Tindakan: {$tindakan_label} ($poin_didapat Poin)
                            <hr>
                            <strong>Keputusan Akhir: $label_hasil</strong></div>";
        } else {
            $notif_pesan = "<div class='alert danger'>Gagal memproses laporan.</div>";
        }
    }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Laporan Perilaku - Sistem Reward & Punishment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css" />
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 700px; background: #fff; padding: 25px; margin: auto; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        textarea, button { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
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
    
    <a href="laporan_kelas.php" class="nav-link">Laporan Per Kelas →</a><br>
    <a href="dashboard.php" class="nav-link">← Lihat Rekap & Dashboard Siswa</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>
<script>
    new Choices('#id_siswa', {
        searchEnabled: true,
        searchPlaceholderValue: 'Ketik nama siswa...',
        noResultsText: 'Siswa tidak ditemukan',
        noChoicesText: 'Tidak ada data siswa',
        itemSelectText: ''
    });

    // Cegah double-click — jangan disable button, pakai flag saja
    document.querySelector('form').addEventListener('submit', function() {
        var btn = this.querySelector('button[type="submit"]');
        if (btn.dataset.submitted === '1') {
            return false; // ignore double-click
        }
        btn.dataset.submitted = '1';
        btn.textContent = 'Memproses...';
    });
</script>
</body>
</html>