<?php
// Fungsi Preprocessing
function preprocess_text($text) {
    // 1. Case Folding
    $text = strtolower($text);
    
    // Hapus tanda baca dan angka
    $text = preg_replace('/[^a-z\s]/', '', $text);
    
    // 2. Tokenisasi
    $tokens = explode(" ", $text);
    
    // List Stopwords bahasa Indonesia sederhana
    $stopwords = ['dan', 'yang', 'di', 'ke', 'dari', 'saat', 'dengan', 'oleh', 'pada', 'ini', 'itu', 'adalah', 'sangat',
    'alkohol', 'narkoba', 'psikotropika', 'zat terlarang', 'barang terlarang', 
    'judi online', 'taruhan', 'penggunaan', 'pengedaran', 'penjualan', 
    'membawa', 'menyimpan', 'mengakses', 'perjudian', 'mencuri', 
    'merampas', 'mengambil', 'barang', 'uang', 'dompet', 
    'tas', 'handphone', 'paksa', 'tanpa izin', 'milik teman', 
    'milik orang lain', 'merokok', 'vape', 'berkelahi', 'tawuran', 
    'membolos', 'kabur', 'bullying', 'mengancam', 'memukul', 
    'menendang', 'melawan', 'mengintimidasi', 'memalak', 'vandalisme', 
    'merusak', 'mencuri', 'mencontek', 'memalsukan', 'menghasut', 
    'menghina', 'kasar', 'ribut', 'kerusuhan', 'provokasi', 
    'membentak', 'senjata', 'benda_tajam', 'petasan', 'judi', 
    'minuman_keras', 'terlambat', 'alpha', 'pelanggaran', 'hukuman', 
    'punishment', 'tidak_disiplin', 'mengganggu', 'mencoret', 'membakar', 
    'menghapus', 'menyebarkan', 'ejekan', 'rumor', 'bohong', 
    'mengolok', 'mengunci', 'merekam', 'melanggar', 'keributan', 
    'kabur_kelas', 'merusak_fasilitas', 'keluar_tanpa_izin', 'terlambat', 'membolos', 
    'bolos', 'kabur', 'ribut', 'berisik', 'mengganggu', 
    'tidur', 'mencontek', 'melawan', 'membantah', 'berkata_kasar', 
    'berbohong', 'tidak_mengerjakan', 'tidak_masuk', 'alpha', 'izin_palsu', 
    'merusak', 'mencoret', 'membuang_sampah', 'merokok', 'bermain_hp', 
    'main_game', 'tidak_disiplin', 'keluar_kelas', 'makan_di_kelas', 'tidak_memakai_atribut', 
    'seragam_tidak_lengkap', 'rambut_panjang', 'nongkrong', 'pacaran', 'provokasi', 
    'mengancam', 'tidak_sopan', 'terlambat_upacara', 'mengabaikan_tugas', 'mangkir', 
    'tidak_piket', 'berkelahi_Ringan', 'mengotori_kelas', 'terlambat', 'telat', 
    'rambut', 'panjang', 'tidak rapi', 'atribut', 'seragam', 
    'sepatu', 'kaos kaki', 'lencana', 'dasi', 'tidur', 
    'ngobrol', 'ribut', 'berisik', 'main hp', 'hp', 
    'gaduh', 'izin', 'keluar', 'makan', 'minum', 
    'bercanda', 'tidak fokus', 'lalai', 'rame', 'tertidur', 
    'alpa', 'alpha', 'terlambat masuk', 'tidak lengkap', 'tidak sopan', 
    'coret', 'membuang sampah', 'sampah', 'terlambat upacara', 'bermain', 
    'dikelas', 'topi', 'kuku panjang', 'baju tidak dimasukkan', 'tidak memakai ikat pinggang', 
    'tanpa atribut', 'keluar kelas', 'berisik di kelas', 'mengobrol saat pelajaran', 'main game', 
    'earphone', 'tidak memperhatikan', 'bolpoin', 'tidak membawa buku', 'lupa tugas', 
    'tidak mengerjakan tugas', 'prestasi', 'juara', 'peringkat', 'ranking', 
    'nilai', 'terbaik', 'unggul', 'aktif', 'kreatif', 
    'inovatif', 'rajin', 'disiplin', 'teladan', 'sopan', 
    'bertanggungjawab', 'mandiri', 'cerdas', 'kompeten', 'hebat', 
    'berprestasi', 'mengharumkan', 'mewakili', 'lomba', 'olimpiade', 
    'kompetisi', 'kejuaraan', 'finalis', 'winner', 'champion', 
    'medali', 'sertifikat', 'piagam', 'penghargaan', 'reward', 
    'apresiasi', 'beasiswa', 'unggulan', 'terfavorit', 'aktif organisasi', 
    'osis', 'ekskul', 'kepemimpinan', 'leadership', 'hadir', 
    'tepat waktu', 'absensi baik', 'nilai tinggi', 'ranking kelas', 'hafalan', 
    'presentasi', 'public speaking', 'produktif', 'kolaboratif', 'kerjasama', 
    'sportif', 'percaya diri', 'juara', 'prestasi', 'penghargaan', 
    'teladan', 'disiplin', 'aktif', 'nilai tinggi', 'olimpiade', 
    'lomba', 'beasiswa', 'ranking', 'prestasi', 'juara', 
    'ranking', 'nilai tinggi', 'olimpiade', 'kompetisi', 'lomba', 
    'medali', 'sertifikat', 'piagam', 'penghargaan', 'beasiswa', 
    'finalis', 'winner', 'champion', 'unggul', 'cerdas', 
    'berprestasi', 'disiplin', 'tepat waktu', 'hadir', 'absensi baik', 
    'rapi', 'tertib', 'mematuhi', 'taat', 'patuh', 
    'lengkap', 'teratur', 'konsisten', 'sopan', 'ramah', 
    'jujur', 'bertanggungjawab', 'mandiri', 'percaya diri', 'aktif', 
    'peduli', 'membantu', 'kerjasama', 'kolaboratif', 'teladan', 
    'baik', 'inisiatif', 'kreatif', 'inovatif', 'osis', 
    'ketua', 'pemimpin', 'leadership', 'organisasi', 'panitia', 
    'koordinator', 'mentor', 'penggerak', 'aktif organisasi', 'hadir', 
    'aktif kelas', 'partisipatif', 'mengikuti', 'semangat belajar', 'produktif'];
    
    // 3. Stopword Removal
    $filtered_tokens = array_filter($tokens, function($token) use ($stopwords) {
        return !in_array($token, $stopwords) && strlen($token) > 1;
    });
    
    // 4. Stemming Sederhana (Pemotongan awalan/akhiran dasar)
    $stemmed_tokens = array_map(function($token) {
        // Aturan stemming teks indonesia sangat sederhana (untuk keperluan demo native)
        $token = preg_replace('/^(mem|ber|di|ter|me)/', '', $token);
        $token = preg_replace('/(an|kan|nya)$/', '', $token);
        return $token;
    }, $filtered_tokens);
    
    return array_values($stemmed_tokens);
}

// Klasifikasi Naive Bayes Berbasis Kemunculan Kata (Term Frequency Sederhana)
function klasifikasi_naive_bayes($conn, $teks_input, $poin_reward_siswa = 0, $poin_punishment_siswa = 0) {
    $tokens = preprocess_text($teks_input);
    
    // 1. AMBIL DATASET TRAINING (Proses Naive Bayes Standar)
    $query = mysqli_query($conn, "SELECT * FROM dataset_training");
    $total_dokumen = mysqli_num_rows($query);
    
    $count_positif = 0;
    $count_negatif = 0;
    $vocab_positif = [];
    $vocab_negatif = [];
    $all_vocab = [];
    
    while ($row = mysqli_fetch_assoc($query)) {
        $words = preprocess_text($row['teks_sampel']);
        if ($row['label'] == 'Reward') {
            $count_positif++;
            $vocab_positif = array_merge($vocab_positif, $words);
        } else {
            $count_negatif++;
            $vocab_negatif = array_merge($vocab_negatif, $words);
        }
        $all_vocab = array_merge($all_vocab, $words);
    }
    
    if ($total_dokumen == 0) return 'Reward';
    if ($count_positif == 0) return 'Punishment';
    if ($count_negatif == 0) return 'Reward';
    
    $prior_positif = $count_positif / $total_dokumen;
    $prior_negatif = $count_negatif / $total_dokumen;
    
    $freq_positif = array_count_values($vocab_positif);
    $freq_negatif = array_count_values($vocab_negatif);
    $total_unique_vocab = count(array_unique($all_vocab));
    
    $total_word_positif = count($vocab_positif);
    $total_word_negatif = count($vocab_negatif);
    
    $score_positif = log($prior_positif);
    $score_negatif = log($prior_negatif);
    
    foreach ($tokens as $token) {
        // Laplace Smoothing Positif
        $count_w_pos = isset($freq_positif[$token]) ? $freq_positif[$token] : 0;
        $prob_w_pos = ($count_w_pos + 1) / ($total_word_positif + $total_unique_vocab);
        $score_positif += log($prob_w_pos);
        
        // Laplace Smoothing Negatif
        $count_w_neg = isset($freq_negatif[$token]) ? $freq_negatif[$token] : 0;
        $prob_w_neg = ($count_w_neg + 1) / ($total_word_negatif + $total_unique_vocab);
        $score_negatif += log($prob_w_neg);
    }

    // =========================================================================
    // 2. INTEGRASI BOBOT POIN DARI TABEL MASTER ATURAN
    //    (Mencocokkan kata kunci dari teks input dengan aturan yang tersimpan)
    // =========================================================================
    
    $total_poin_positif = 0;
    $total_poin_negatif = 0;

    // Ambil semua aturan dari tabel aturan/master poin
    $query_aturan = mysqli_query($conn, "SELECT id_aturan, jenis, nama_perilaku, poin FROM master_poin");
    
    while ($aturan = mysqli_fetch_assoc($query_aturan)) {
        $nama_perilaku = strtolower($aturan['nama_perilaku']);
        
        // Cek 1: Apakah teks aturan lengkap muncul di dalam teks input (pencocokan eksak)
        if (strpos(strtolower($teks_input), $nama_perilaku) !== false) {
            if ($aturan['jenis'] == 'Reward') {
                $total_poin_positif += $aturan['poin'];
            } else if ($aturan['jenis'] == 'Punishment') {
                $total_poin_negatif += $aturan['poin'];
            }
        }
    }

    // Aturan Tambahan: Jika poin negatif ditemukan dan lebih berat/besar dari poin positif
    if ($total_poin_negatif > $total_poin_positif) {
        return 'Punishment'; 
    } 
    // Jika poin positif lebih besar, otomatis overrides ke Positif
    elseif ($total_poin_positif > $total_poin_negatif) {
        return 'Reward';
    }

    // =========================================================================
    // 3. PERTIMBANGAN HISTORIS POIN SISWA (Perbandingan Reward vs Punishment)
    // =========================================================================
    // Jika siswa memiliki riwayat poin, beri bias ringan pada skor Naive Bayes
    $total_historis = $poin_reward_siswa + $poin_punishment_siswa;
    if ($total_historis > 0) {
        $selisih = $poin_punishment_siswa - $poin_reward_siswa;
        $normalized_bias = $selisih / $total_historis;
        
        // Bias historis dibuat sangat kecil agar tidak mudah mengalahkan hasil NLP
        // Hanya sebagai sinyal pelengkap, bukan penentu utama
        $bobot_historis = $normalized_bias * 0.05;
        
        $score_negatif += $bobot_historis;
        $score_positif -= $bobot_historis;
    }
    
    // Jika tidak ada kecocokan poin di tabel master (atau poin seri), kembali ke hasil murni Naive Bayes
    // (sudah ditambah pertimbangan historis poin siswa yang ringan)
    return ($score_positif >= $score_negatif) ? 'Reward' : 'Punishment';
}

// Cek apakah teks dikenal oleh dataset training (minimal ada 1 token yang muncul di training)
function teks_dikenali_oleh_training($conn, $teks_input) {
    $tokens = preprocess_text($teks_input);
    if (empty($tokens)) return false;

    $query = mysqli_query($conn, "SELECT teks_sampel FROM dataset_training");
    $all_tokens_training = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $all_tokens_training = array_merge($all_tokens_training, preprocess_text($row['teks_sampel']));
    }
    $known_vocab = array_unique($all_tokens_training);

    foreach ($tokens as $token) {
        if (in_array($token, $known_vocab)) {
            return true;
        }
    }
    return false;
}

// Cari aturan master_poin berdasarkan training sample paling mirip dengan input
function cari_aturan_via_training($conn, $teks_input, $label_hasil) {
    $input_tokens = preprocess_text($teks_input);
    if (empty($input_tokens)) return null;

    // Cari training sample dengan word overlap tertinggi
    $query = mysqli_query($conn, "SELECT teks_sampel FROM dataset_training WHERE label = '$label_hasil'");
    $best_score = 0;
    $best_sample = '';

    while ($row = mysqli_fetch_assoc($query)) {
        $sample_tokens = preprocess_text($row['teks_sampel']);
        $overlap = count(array_intersect($input_tokens, $sample_tokens));
        if ($overlap > $best_score) {
            $best_score = $overlap;
            $best_sample = $row['teks_sampel'];
        }
    }

    if ($best_score == 0) return null;

    // Cari aturan master_poin yang cocok dengan training sample tsb, urut poin tertinggi
    $query_rules = mysqli_query($conn, "SELECT * FROM master_poin WHERE jenis = '$label_hasil' ORDER BY poin DESC");
    while ($rule = mysqli_fetch_assoc($query_rules)) {
        if (strpos(strtolower($best_sample), strtolower($rule['nama_perilaku'])) !== false) {
            return $rule;
        }
    }

    return null;
}
?>