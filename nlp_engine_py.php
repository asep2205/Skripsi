<?php
// PHP wrapper untuk Python NLP Engine
// Membaca data training & master poin dari CSV (source of truth),
// database hanya untuk data operasional (laporan, siswa, users)

define('PYTHON_SCRIPT', __DIR__ . '/nlp_engine.py');
define('PYTHON_BIN', 'python3');

define('DATASETS_CSV', __DIR__ . '/datasets.csv');
define('MASTER_POIN_CSV', __DIR__ . '/master_point.csv');

function load_datasets_csv() {
    $data = [];
    if (!file_exists(DATASETS_CSV)) return $data;
    $f = fopen(DATASETS_CSV, 'r');
    while (($row = fgetcsv($f, 0, ',', '"', '\\')) !== false) {
        if (count($row) >= 3) {
            $data[] = [$row[1], $row[2]];
        }
    }
    fclose($f);
    return $data;
}

function load_master_poin_csv() {
    $data = [];
    if (!file_exists(MASTER_POIN_CSV)) return $data;
    $f = fopen(MASTER_POIN_CSV, 'r');
    while (($row = fgetcsv($f, 0, ',', '"', '\\')) !== false) {
        if (count($row) >= 4) {
            $data[] = [(int)$row[0], $row[1], $row[2], (int)$row[3]];
        }
    }
    fclose($f);
    return $data;
}

function klasifikasi_python($conn, $teks_laporan, $poin_reward_siswa = 0, $poin_punishment_siswa = 0) {
    $training_data = load_datasets_csv();
    $master_poin = load_master_poin_csv();

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
?>
