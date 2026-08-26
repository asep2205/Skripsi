<?php
session_start();
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

include 'koneksi.php';
include 'periode_helper.php';

if (isset($_POST['narasi_laporan']) && isset($_POST['id_siswa'])) {
    // Periode dicatat sekarang supaya laporan pending tidak ikut pindah periode.
    $id_periode = id_periode_aktif($koneksi);
    if ($id_periode <= 0) {
        header("location:input_laporan?pesan=periode_belum_aktif");
        exit();
    }
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
    $status_upload = 'foto_wajib';
    if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] == UPLOAD_ERR_OK) {
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
            } else {
                $status_upload = 'foto_gagal_simpan';
            }
        } else {
            $status_upload = 'foto_tidak_valid';
        }
    } elseif (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] == UPLOAD_ERR_INI_SIZE) {
        $status_upload = 'foto_terlalu_besar_server';
    }

    if (empty($foto_final)) {
        header("location:input_laporan?pesan=$status_upload");
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

    // Tentukan interpreter Python yang tersedia. PATH Apache sering kali
    // terbatas, terutama pada instalasi XAMPP di Windows.
    $python_command = null;
    $is_windows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    $candidates = $is_windows
        ? ['python', 'py']
        : ['/usr/local/bin/python3', '/opt/homebrew/bin/python3', 'python3', 'python'];
    foreach ($candidates as $candidate) {
        if ($candidate === 'python3' || $candidate === 'python' || is_executable($candidate)) {
            $python_command = $candidate;
            break;
        }
    }
    $user_profile = getenv('USERPROFILE');
    if ($user_profile) {
        $local_app_data_python = $user_profile . "\\AppData\\Local\\Programs\\Python";
        if (is_dir($local_app_data_python)) {
            $folders = scandir($local_app_data_python, SCANDIR_SORT_DESCENDING);
            foreach ($folders as $folder) {
                if (strpos($folder, 'Python') === 0) {
                    $exe_path = $local_app_data_python . "\\" . $folder . "\\python.exe";
                    if (file_exists($exe_path)) {
                        // Simpan path asli tanpa quote. Quote akan ditangani
                        // oleh proc_open; quote ganda membuat CMD Windows gagal.
                        $python_command = $exe_path;
                        break;
                    }
                }
            }
        }
    }

    $script_python = __DIR__ . DIRECTORY_SEPARATOR . 'nlp_engine.py';
    if ($python_command === null) {
        echo "Python tidak ditemukan. Instal Python lalu pastikan python.exe tersedia di PATH atau di folder instalasi Python pengguna.";
        exit();
    }

    // Array command (PHP >= 7.4) melewati shell, sehingga path Windows yang
    // memiliki spasi tidak perlu di-quote secara manual.
    $command = PHP_VERSION_ID >= 70400
        ? [$python_command, $script_python]
        : escapeshellarg($python_command) . ' ' . escapeshellarg($script_python);
    $process = proc_open($command, $descriptorspec, $pipes);

    if (is_resource($process)) {
        $payload_json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($payload_json === false) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            echo "Gagal menyiapkan data laporan untuk Python: " . json_last_error_msg();
            exit();
        }

        // Jika Python gagal start, stdin-nya sudah tertutup. Jangan tampilkan
        // Notice Broken pipe; stderr Python di bawah akan menjadi pesan error.
        $bytes_written = 0;
        $payload_length = strlen($payload_json);
        while ($bytes_written < $payload_length) {
            $written = @fwrite($pipes[0], substr($payload_json, $bytes_written));
            if ($written === false || $written === 0) {
                break;
            }
            $bytes_written += $written;
        }
        fclose($pipes[0]);

        $result_json = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $error_output = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exit_code = proc_close($process);

        $hasil_nlp = json_decode($result_json, true);

        if ($bytes_written !== $payload_length || $hasil_nlp === null || isset($hasil_nlp['error'])) {
            // Hapus foto yang sudah terlanjur di-upload karena proses NLP gagal
            if (!empty($foto_final) && file_exists($foto_final)) {
                @unlink($foto_final);
            }
            echo "<h3>Oops, Ada masalah di Python:</h3>";
            $python_error = $hasil_nlp['error'] ?? trim($error_output);
            if ($python_error === '') {
                $python_error = "Python berhenti sebelum mengirim hasil (kode proses: $exit_code).";
            }
            echo "<b>Pesan Error Python:</b> <pre>" . htmlspecialchars($python_error, ENT_QUOTES, 'UTF-8') . "</pre>";
            echo "<br><i>Pastikan modul dipasang pada interpreter yang sama: <b>\"$python_command\" -m pip install Sastrawi</b></i>";
            exit();
        }

        // Lengkapi hasil dengan data siswa (dari dropdown, sudah pasti valid) + foto bukti
        $hasil_nlp['input_guru']  = $narasi_laporan;
        $hasil_nlp['nis']         = $siswa_terpilih['nis'];
        $hasil_nlp['nama_siswa']  = $siswa_terpilih['nama_siswa'];
        $hasil_nlp['kelas']       = $siswa_terpilih['kelas'];
        // Setiap laporan harus diverifikasi terlebih dahulu. Poin baru akan
        // ditambahkan oleh proses persetujuan, bukan saat laporan dibuat.
        $hasil_nlp['status_db']  = "PENDING";
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
            (id_siswa, id_user, id_periode, teks_laporan, label_prediksi, kecocokan_kata, poin_didapat, akurasi_map, foto, status_verifikasi)
            VALUES ('$id_siswa_input', '$id_user', $id_periode, '$teks_laporan', '$label_prediksi', '$kecocokan_kata', '$poin_didapat', '$akurasi_map', '$foto_db', 'pending')";

        if (!mysqli_query($koneksi, $query_laporan)) {
            if (!empty($foto_final) && file_exists($foto_final)) {
                @unlink($foto_final);
            }
            header("location:input_laporan?pesan=gagal_simpan");
            exit();
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
