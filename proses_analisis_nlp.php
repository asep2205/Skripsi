<?php
session_start();
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

include 'koneksi.php';

if (isset($_POST['narasi_laporan']) && isset($_POST['id_siswa'])) {
    $narasi_laporan = trim($_POST['narasi_laporan']);
    $id_siswa_input = mysqli_real_escape_string($koneksi, $_POST['id_siswa']);

    if ($id_siswa_input === '' || $narasi_laporan === '') {
        header("location:input_laporan?pesan=data_tidak_lengkap");
        exit();
    }

    // Ambil data siswa yang DIPILIH LEWAT DROPDOWN (bukan lagi ditebak dari teks)
    $q_cek_siswa = mysqli_query($koneksi, "SELECT id_siswa, nis, nama_siswa, kelas FROM siswa WHERE id_siswa = '$id_siswa_input'");
    if (!$q_cek_siswa || mysqli_num_rows($q_cek_siswa) == 0) {
        header("location:input_laporan?pesan=siswa_tidak_ditemukan");
        exit();
    }
    $siswa_terpilih = mysqli_fetch_assoc($q_cek_siswa);

    // =========================================================================
    // UPLOAD FOTO BUKTI (WAJIB) - untuk membuktikan laporan yang dimaksud benar
    // =========================================================================
    $foto_final = "";
    if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] == 0) {
        $folder_upload = 'uploads/bukti_laporan/';
        if (!is_dir($folder_upload)) {
            mkdir($folder_upload, 0755, true);
        }

        $ext_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
        $ext_file = strtolower(pathinfo($_FILES['foto_bukti']['name'], PATHINFO_EXTENSION));
        $ukuran_maks = 5 * 1024 * 1024; // 5 MB

        if (in_array($ext_file, $ext_diizinkan) && $_FILES['foto_bukti']['size'] <= $ukuran_maks) {
            $nama_file_baru = 'bukti_' . $id_siswa_input . '_' . time() . '_' . uniqid() . '.' . $ext_file;
            $path_tujuan = $folder_upload . $nama_file_baru;

            if (move_uploaded_file($_FILES['foto_bukti']['tmp_name'], $path_tujuan)) {
                $foto_final = $path_tujuan;
            }
        }
    }

    if (empty($foto_final)) {
        header("location:input_laporan?pesan=foto_wajib");
        exit();
    }

    // 1. Ambil Data Master Poin dari DB
    $master_data = [];
    $q_master = mysqli_query($koneksi, "SELECT id_aturan, jenis, nama_perilaku, poin FROM master_poin");
    while($r = mysqli_fetch_assoc($q_master)) {
        $master_data[] = [
            'ID' => $r['id_aturan'],
            'Tipe' => $r['jenis'],
            'Keterangan_Nominal' => $r['nama_perilaku'],
            'Poin' => (int)$r['poin']
        ];
    }

    // 2. Ambil Data Training dari DB
    $training_data = [];
    $q_training = mysqli_query($koneksi, "SELECT id_data, teks_sampel, label FROM dataset_training");
    if($q_training) {
        // Melewati baris pertama jika diperlukan seperti pada struktur kode asli Anda
        $r = mysqli_fetch_assoc($q_training);
        while($r = mysqli_fetch_assoc($q_training)) {
            $training_data[] = [
                'ID' => $r['id_data'],
                'Keterangan_Nominal' => $r['teks_sampel'],
                'Tipe' => $r['label']
            ];
        }
    }

    // 3. Bungkus payload untuk Python.
    // PERUBAHAN: data_siswa TIDAK dikirim lagi karena siswa sudah pasti
    // dari dropdown -> Python cukup fokus mengklasifikasikan narasi.
    $payload = [
        'kalimat_input' => $narasi_laporan,
        'master_point' => $master_data,
        'data_training' => $training_data
    ];

    $descriptorspec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];

    // Otomatisasi path Python (dipertahankan dari kode asli)
    $python_command = 'python';
    $user_profile = getenv('USERPROFILE');
    if ($user_profile) {
        $local_app_data_python = $user_profile . "\\AppData\\Local\\Programs\\Python";
        if (is_dir($local_app_data_python)) {
            $folders = scandir($local_app_data_python, SCANDIR_SORT_DESCENDING);
            foreach ($folders as $folder) {
                if (strpos($folder, 'Python') === 0) {
                    $exe_path = $local_app_data_python . "\\" . $folder . "\\python.exe";
                    if (file_exists($exe_path)) {
                        $python_command = '"' . $exe_path . '"';
                        break;
                    }
                }
            }
        }
    }

    $process = proc_open($python_command . ' nlp_engine.py', $descriptorspec, $pipes);

    if (is_resource($process)) {
        fwrite($pipes[0], json_encode($payload));
        fclose($pipes[0]);

        $result_json = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $error_output = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);

        $hasil_nlp = json_decode($result_json, true);

        if ($hasil_nlp === null || isset($hasil_nlp['error'])) {
            // Hapus foto yang sudah terlanjur di-upload karena proses NLP gagal
            if (!empty($foto_final) && file_exists($foto_final)) {
                @unlink($foto_final);
            }
            echo "<h3>Oops, Ada masalah di Python:</h3>";
            echo "<b>Pesan Error Python:</b> <pre>" . ($hasil_nlp['error'] ?? $error_output) . "</pre>";
            echo "<br><i>Saran: Jika error 'Sastrawi tidak ditemukan', buka CMD biasa lalu ketik: <b>pip install Sastrawi</b></i>";
            exit();
        }

        // Lengkapi hasil dengan data siswa (dari dropdown, sudah pasti valid) + foto bukti
        $hasil_nlp['input_guru']  = $narasi_laporan;
        $hasil_nlp['nis']         = $siswa_terpilih['nis'];
        $hasil_nlp['nama_siswa']  = $siswa_terpilih['nama_siswa'];
        $hasil_nlp['kelas']       = $siswa_terpilih['kelas'];
        $hasil_nlp['status_db']  = "TERVERIFIKASI";
        $hasil_nlp['foto']        = $foto_final;

        // Simpan ke session untuk ditampilkan di notifikasi halaman depan
        $_SESSION['hasil_analisis'] = $hasil_nlp;

        $total_reward = (int)$hasil_nlp['total_reward'];
        $total_punish = (int)$hasil_nlp['total_punish'];

        $id_user = mysqli_real_escape_string($koneksi, $_SESSION['id_user'] ?? 1);
        $teks_laporan = mysqli_real_escape_string($koneksi, $narasi_laporan);
        $foto_db = mysqli_real_escape_string($koneksi, $foto_final);

        // Aturan simpan salah satu (eksklusif): reward menang jika >= punishment dan > 0
        if ($total_reward >= $total_punish && $total_reward > 0) {
            $label_prediksi = 'Reward';
            $poin_didapat   = $total_reward;
        } elseif ($total_punish > $total_reward) {
            $label_prediksi = 'Punishment';
            $poin_didapat   = $total_punish;
        } else {
            // Tidak ada aksi terdeteksi -> tetap dicatat sebagai log, poin 0
            $label_prediksi = 'Reward';
            $poin_didapat   = 0;
        }

        $kecocokan_kata = !empty($hasil_nlp['analisa_master']) ? $hasil_nlp['analisa_master'] : '-';
        $kecocokan_kata = mysqli_real_escape_string($koneksi, $kecocokan_kata);

        $akurasi_map = isset($hasil_nlp['akurasi_map']) ? $hasil_nlp['akurasi_map'] : '0%';
        $akurasi_map = mysqli_real_escape_string($koneksi, $akurasi_map);

        // Simpan laporan lengkap dengan foto bukti ke tabel laporan_prilaku
        $query_laporan = "INSERT INTO laporan_prilaku 
            (id_siswa, id_user, teks_laporan, label_prediksi, kecocokan_kata, poin_didapat, akurasi_map, foto) 
            VALUES ('$id_siswa_input', '$id_user', '$teks_laporan', '$label_prediksi', '$kecocokan_kata', '$poin_didapat', '$akurasi_map', '$foto_db')";

        mysqli_query($koneksi, $query_laporan);

        // Update total poin sistem lama
        $query_update = "";
        if ($total_reward >= $total_punish && $total_reward > 0) {
            $query_update = "UPDATE siswa SET total_poin_reward = total_poin_reward + $total_reward WHERE id_siswa = '$id_siswa_input'";
        } elseif ($total_punish > $total_reward) {
            $query_update = "UPDATE siswa SET total_poin_punishment = total_poin_punishment + $total_punish WHERE id_siswa = '$id_siswa_input'";
        }
        if (!empty($query_update)) {
            mysqli_query($koneksi, $query_update);
        }

        header("location:input_laporan?pesan=analisis_sukses");
        exit();

    } else {
        echo "Gagal memanggil command Python lewat PHP (proc_open error).";
        exit();
    }
} else {
    header("location:input_laporan");
    exit();
}
?>
