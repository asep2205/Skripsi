<?php
// PHP wrapper untuk Python NLP Engine
// Menyediakan fungsi yang sama seperti nlp_engine.php tetapi memanggil Python di belakang layar

define('PYTHON_SCRIPT', __DIR__ . '/nlp_engine.py');
define('PYTHON_BIN', 'python3');

function klasifikasi_python($conn, $teks_laporan, $poin_reward_siswa = 0, $poin_punishment_siswa = 0) {
    $training_data = [];
    $query = mysqli_query($conn, "SELECT teks_sampel, label FROM dataset_training");
    while ($row = mysqli_fetch_assoc($query)) {
        $training_data[] = [$row['teks_sampel'], $row['label']];
    }

    $master_poin = [];
    $query = mysqli_query($conn, "SELECT id_aturan, jenis, nama_perilaku, poin FROM master_poin");
    while ($row = mysqli_fetch_assoc($query)) {
        $master_poin[] = [(int)$row['id_aturan'], $row['jenis'], $row['nama_perilaku'], (int)$row['poin']];
    }

    $input_data = json_encode([
        'teks_laporan' => $teks_laporan,
        'poin_reward_siswa' => $poin_reward_siswa,
        'poin_punishment_siswa' => $poin_punishment_siswa,
        'dataset_training' => $training_data,
        'master_poin' => $master_poin,
    ]);

    $descriptorspec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"],
    ];

    $process = proc_open(PYTHON_BIN . ' ' . escapeshellarg(PYTHON_SCRIPT), $descriptorspec, $pipes);

    if (!is_resource($process)) {
        error_log("nlp_engine_py.php: Gagal menjalankan Python");
        return fallback_result($teks_laporan, $poin_reward_siswa, $poin_punishment_siswa);
    }

    fwrite($pipes[0], $input_data);
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $return_value = proc_close($process);

    if ($return_value !== 0 || empty($output)) {
        error_log("nlp_engine_py.php: Python error: " . $stderr);
        return fallback_result($teks_laporan, $poin_reward_siswa, $poin_punishment_siswa);
    }

    $result = json_decode($output, true);
    if (!$result) {
        return fallback_result($teks_laporan, $poin_reward_siswa, $poin_punishment_siswa);
    }

    return $result;
}

function fallback_result($teks_laporan, $poin_reward_siswa = 0, $poin_punishment_siswa = 0) {
    return [
        'label' => 'Reward',
        'aturan' => null,
        'total_poin_reward' => 0,
        'total_poin_punishment' => 0,
        'daftar_reward' => [],
        'daftar_punishment' => [],
        'tolak' => true,
        'pesan' => 'Python engine tidak tersedia. Gunakan engine PHP.',
    ];
}

function teks_dikenali_python($conn, $teks_laporan) {
    $training_data = [];
    $query = mysqli_query($conn, "SELECT teks_sampel, label FROM dataset_training");
    while ($row = mysqli_fetch_assoc($query)) {
        $training_data[] = [$row['teks_sampel'], $row['label']];
    }

    $input_data = json_encode([
        'mode' => 'teks_dikenali',
        'teks_laporan' => $teks_laporan,
        'dataset_training' => $training_data,
        'master_poin' => [],
        'poin_reward_siswa' => 0,
        'poin_punishment_siswa' => 0,
    ]);

    $descriptorspec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"],
    ];

    $process = proc_open(PYTHON_BIN . ' ' . escapeshellarg(PYTHON_SCRIPT), $descriptorspec, $pipes);
    if (!is_resource($process)) return true;

    fwrite($pipes[0], $input_data);
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    proc_close($process);

    $result = json_decode($output, true);
    return $result ? !$result['tolak'] : true;
}
?>
