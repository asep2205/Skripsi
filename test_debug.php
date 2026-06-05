<?php
include 'config.php';

// Simulasi proses seperti di input_laporan.php
$teks_laporan = 'Siswa kedapatan memiliki narkotika berdasarkan pemeriksaan';

$cmd = 'python3 ' . escapeshellarg(__DIR__ . '/nlp_engine_web.py') . ' ' . escapeshellarg($teks_laporan);
$output = shell_exec($cmd);
$py_result = json_decode($output, true);

echo "=== PYTHON OUTPUT ===\n";
echo $output . "\n\n";

echo "=== aturan key ===\n";
var_dump($py_result['aturan']);
echo "\n";

echo "=== aturan['nama'] ===\n";
var_dump($py_result['aturan']['nama'] ?? 'KEY NOT FOUND');
echo "\n";

// Cek INSERT
$id_siswa = 1;
$id_user_login = 2;
$label_hasil = $py_result['label'];
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
$poin_didapat = $aturan ? $aturan['poin'] : 0;

echo "=== \$nama_perilaku ===\n";
var_dump($nama_perilaku);
echo "\n";

echo "=== SQL yang akan dijalankan ===\n";
$sql = "INSERT INTO laporan_perilaku (id_siswa, id_user, teks_laporan, label_prediksi, nama_perilaku, poin_didapat) 
        VALUES ('$id_siswa', '$id_user_login', '$teks_laporan', '$label_hasil', '$nama_perilaku', '$poin_didapat')";
echo $sql . "\n\n";

// Eksekusi untuk test
$insert = mysqli_query($conn, $sql);
if ($insert) {
    $id = mysqli_insert_id($conn);
    echo "SUKSES! ID: $id\n";
    
    // Cek hasil
    $q = mysqli_query($conn, "SELECT id_laporan, nama_perilaku, poin_didapat FROM laporan_perilaku WHERE id_laporan = $id");
    $r = mysqli_fetch_assoc($q);
    echo json_encode($r) . "\n";
    
    // Hapus test data
    mysqli_query($conn, "DELETE FROM laporan_perilaku WHERE id_laporan = $id");
    echo "Test data cleaned up.\n";
} else {
    echo "GAGAL: " . mysqli_error($conn) . "\n";
}
