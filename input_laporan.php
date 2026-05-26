<?php
include 'config.php';
include 'nlp_engine.php';

// Simulasi session login user (Guru) jika belum ada sistem login lengkap
$id_user_login = 2; // Default ke id_user 2 (Guru1)

$notif_pesan = "";

if (isset($_POST['submit_laporan'])) {
    $id_siswa = $_POST['id_siswa'];
    $teks_laporan = $_POST['teks_laporan'];
    
    // --- Langkah 1 s.d 4: Menjalankan Klasifikasi Otomatis via Engine ---
    $label_hasil = klasifikasi_naive_bayes($conn, $teks_laporan);
    
    // --- Langkah 5: Cocokkan dengan tabel master poin terdekat ---
    // Pencarian sederhana: ambil poin default pertama berdasarkan label hasil klasifikasi
    $query_aturan = mysqli_query($conn, "SELECT * FROM master_poin WHERE jenis = '$label_hasil' LIMIT 1");
    $aturan = mysqli_fetch_assoc($query_aturan);
    
    $id_aturan = $aturan['id_aturan'];
    $poin_didapat = $aturan['poin'];
    
    // --- Langkah 6: Simpan Transaksi Laporan ---
    $insert = mysqli_query($conn, "INSERT INTO laporan_perilaku (id_siswa, id_user, teks_laporan, label_prediksi, id_aturan_tercocok, poin_didapat) 
               VALUES ('$id_siswa', '$id_user_login', '$teks_laporan', '$label_hasil', '$id_aturan', '$poin_didapat')");
    
    if ($insert) {
        // --- Langkah 7: Update Akumulasi Profil Poin Siswa ---
        if ($label_hasil == 'Reward') {
            mysqli_query($conn, "UPDATE siswa SET total_poin_reward = total_poin_reward + $poin_didapat WHERE id_siswa = '$id_siswa'");
        } else {
            mysqli_query($conn, "UPDATE siswa SET total_poin_punishment = total_poin_punishment + $poin_didapat WHERE id_siswa = '$id_siswa'");
        }
        
        // --- Langkah 8: Cek Ambang Batas Poin (Threshold Notification) ---
        $query_siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE id_siswa = '$id_siswa'");
        $data_siswa = mysqli_fetch_assoc($query_siswa);
        
        $notif_pesan = "<div class='alert success'><strong>Sistem Berhasil Memproses!</strong><br>
                        Hasil Analisis NLP: <strong>$label_hasil</strong><br>
                        Tindakan Terdeteksi: {$aturan['nama_perilaku']} (+$poin_didapat Poin).</div>";
                        
        // Threshold check: Jika poin punishment mencapai atau melebihi 50 poin
        if ($data_siswa['total_poin_punishment'] >= 50) {
            $notif_pesan .= "<div class='alert danger'><strong>⚠️ NOTIFIKASI TINDAK LANJUT:</strong> Poin pelanggaran siswa <strong>{$data_siswa['nama_siswa']}</strong> telah mencapai {$data_siswa['total_poin_punishment']} poin. Segera hubungi Guru BK!</div>";
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