<?php
session_start();
if($_SESSION['status'] != "login"){
    header("location:index.php");
    exit();
}

include 'koneksi.php';

$nama_user = $_SESSION['nama_lengkap'];
$role_user = $_SESSION['role'];

// Ambil daftar siswa untuk dropdown
$daftar_siswa = [];
$q_siswa = mysqli_query($koneksi, "SELECT id_siswa, nis, nama_siswa, kelas FROM siswa ORDER BY nama_siswa ASC");
if ($q_siswa) {
    while ($r = mysqli_fetch_assoc($q_siswa)) {
        $daftar_siswa[] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Input Laporan NLP - SMKS DB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- CSS tambahan untuk Select2 Dropdown Search -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        body { background-color: #f0f4f8; font-family: 'Poppins', sans-serif; -webkit-tap-highlight-color: transparent; }
        .mobile-header { background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%); color: white; padding: 15px 20px; position: sticky; top: 0; z-index: 100; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .btn-back { color: white; font-size: 22px; text-decoration: none; }
        
        .premium-card { background: white; border-radius: 24px; border: none; box-shadow: 0 8px 20px rgba(0,0,0,0.02); padding: 24px; }
        .form-label-premium { font-weight: 600; color: #1e293b; font-size: 14px; margin-bottom: 8px; display: block; }
        .form-control-premium { border-radius: 14px; background-color: #f8fafc; border: 1.5px solid #e2e8f0; padding: 14px 16px; font-size: 14px; transition: all 0.2s; line-height: 1.6; }
        .form-control-premium:focus { background-color: #fff; border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }
        select.form-control-premium { appearance: auto; }

        .info-siswa-box { font-size: 13px; margin-top: 8px; padding: 10px 14px; border-radius: 12px; background: #eef2ff; color: #3730a3; display: none; }

        .upload-foto-box { border: 2px dashed #cbd5e1; border-radius: 16px; padding: 20px; text-align: center; background-color: #f8fafc; cursor: pointer; transition: all 0.2s; }
        .upload-foto-box:hover { border-color: #4f46e5; background-color: #eef2ff; }
        .upload-foto-box i { font-size: 28px; color: #64748b; }

        .preview-foto-wrapper { display: none; margin-top: 14px; text-align: center; position: relative; }
        .preview-foto-wrapper img { max-width: 100%; max-height: 320px; border-radius: 14px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); }
        .btn-hapus-foto { position: absolute; top: 8px; right: 8px; background: rgba(15,23,42,0.75); color: #fff; border: none; border-radius: 50%; width: 30px; height: 30px; }

        .btn-submit-premium {
            background: linear-gradient(135deg, #1e3a8a 0%, #4f46e5 100%);
            color: white !important;
            padding: 14px;
            font-weight: 600;
            border-radius: 16px;
            border: none;
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 16px;
            transition: all 0.2s ease;
            width: 100%;
        }
        .btn-submit-premium:active { transform: scale(0.98); opacity: 0.95; }
        
        .nlp-step-box { background: #0f172a; color: #38bdf8; font-family: 'Courier New', Courier, monospace; border-radius: 16px; padding: 16px; font-size: 12px; display: none; margin-top: 15px; border-left: 4px solid #38bdf8; }
        
        /* Terminal Log Style Notification */
        .terminal-box {
            background-color: #1e1e1e;
            color: #ffffff;
            font-family: 'Consolas', 'Courier New', Courier, monospace;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 25px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .terminal-foto-preview { max-width: 100%; max-height: 260px; border-radius: 10px; margin-top: 10px; display: block; }

        /* Custom Override styling untuk Select2 agar selaras dengan desain Premium UI */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 14px !important;
            background-color: #f8fafc !important;
            border: 1.5px solid #e2e8f0 !important;
            padding: 10px 14px !important;
            min-height: 50px !important;
            font-size: 14px !important;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            line-height: 1.6 !important;
        }

        @media (max-width: 576px) {
            .premium-card { padding: 18px; border-radius: 20px; }
            .container-main { padding-bottom: 40px; }
        }
    </style>
</head>
<body>

<div class="mobile-header">
    <div class="container d-flex align-items-center gap-3 p-0">
        <a href="dashboard" class="btn-back"><i class="bi bi-arrow-left"></i></a>
        <h5 class="fw-bold mb-0" style="font-size: 18px;">Input Laporan Perilaku</h5>
    </div>
</div>

<div class="container container-main px-3 mt-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">
            
            <div class="d-flex p-3 mb-3" style="background: #e0f2fe; border-radius: 16px; border-left: 4px solid #0284c7;">
                <i class="bi bi-info-circle-fill text-info fs-5 me-2"></i>
                <div class="small text-dark">
                    <strong class="d-block mb-1">Panduan Penginputan Laporan (NLP):</strong>
                    Pilih siswa dari daftar, lalu tuliskan deskripsi kejadian/perilakunya saja secara natural (tanpa perlu menyebut nama atau kelas lagi), dan sertakan foto bukti kejadian.
                </div>
            </div>

            <?php if (isset($_SESSION['hasil_analisis'])): 
                $data = $_SESSION['hasil_analisis'];

                $total_reward = (int)$data['total_reward'];
                $total_punish = (int)$data['total_punish'];
                
                // Hasil klasifikasi hanya berupa calon poin; poin dihitung setelah disetujui.
                if ($total_reward > $total_punish) {
                    $kesimpulan_hasil = "REWARD | CALON POIN: +$total_reward (Menunggu persetujuan)";
                } elseif ($total_reward == $total_punish && $total_reward > 0) {
                    $kesimpulan_hasil = "REWARD (Skor Imbang - Diprioritaskan) | CALON POIN: +$total_reward (Menunggu persetujuan)";
                } elseif ($total_punish > $total_reward) {
                    $kesimpulan_hasil = "PUNISHMENT | CALON POIN: -$total_punish (Menunggu persetujuan)";
                } else {
                    $kesimpulan_hasil = "TIDAK ADA AKSI | POIN: 0";
                }
            ?>
                <div class="terminal-box">======================================================================
INPUT DARI GURU : '<?= htmlspecialchars($data['input_guru']) ?>'
----------------------------------------------------------------------
🆔 NIS          : <?= htmlspecialchars($data['nis']) ?> 
👤 NAMA SISWA   : <?= htmlspecialchars($data['nama_siswa']) ?> (<?= htmlspecialchars($data['status_db']) ?>)
🏫 KELAS        : <?= htmlspecialchars($data['kelas']) ?> 
📝 TEKS PROSES  : '<?= htmlspecialchars($data['teks_proses']) ?>'
----------------------------------------------------------------------
--- RINCIAN DETEKSI NLP ---
<?php if (!empty($data['rincian'])): ?>
<?php foreach ($data['rincian'] as $rincian): ?>
<?= htmlspecialchars($rincian) . "\n" ?>
<?php endforeach; ?>
<?php else: ?>
 - Tidak ada aktivitas pelanggaran/prestasi yang cocok.
<?php endif; ?>
----------------------------------------------------------------------
--- KESIMPULAN AKHIR SISTEM ---
🎯 HASIL AKHIR : <?= $kesimpulan_hasil . "\n" ?>
======================================================================<?php if (!empty($data['foto'])): ?>
<img class="terminal-foto-preview" src="<?= htmlspecialchars($data['foto']) ?>" alt="Foto Bukti Laporan">
<?php endif; ?></div>
            <?php 
                // Hapus session setelah ditampilkan agar tidak muncul terus-menerus saat di-refresh
                unset($_SESSION['hasil_analisis']); 
            endif; 
            ?>

            <div class="card premium-card">
                <form action="proses_analisis_nlp.php" method="POST" id="formLaporan" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label-premium">Pilih Siswa</label>
                        <select name="id_siswa" id="id_siswa" class="form-control-premium form-select w-100" required>
                            <option value="" selected disabled>-- Pilih Nama Siswa --</option>
                            <?php foreach ($daftar_siswa as $s): ?>
                            <option value="<?= (int)$s['id_siswa'] ?>"
                                    data-nis="<?= htmlspecialchars($s['nis']) ?>"
                                    data-kelas="<?= htmlspecialchars($s['kelas']) ?>">
                                <?= htmlspecialchars($s['nama_siswa']) ?> — <?= htmlspecialchars($s['kelas']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="info-siswa-box" id="infoSiswaTerpilih"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-premium">Deskripsi Kejadian / Perilaku</label>
                        <textarea name="narasi_laporan" id="narasi_laporan" rows="5" class="form-control-premium w-100" 
                                  placeholder="Contoh: tadi pagi telat masuk kelas karena kesiangan tapi sempat membantu temannya membawakan buku" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-premium">Foto Bukti (Real-time)</label>
                        <div class="upload-foto-box" id="uploadFotoBox">
                            <i class="bi bi-camera-fill"></i>
                            <div class="mt-2 small text-muted">Ketuk untuk ambil foto atau pilih dari galeri</div>
                            <input type="file" name="foto_bukti" id="foto_bukti" accept="image/*" capture="environment" class="d-none" required>
                        </div>
                        <div class="preview-foto-wrapper" id="previewWrapper">
                            <button type="button" class="btn-hapus-foto" id="btnHapusFoto"><i class="bi bi-x"></i></button>
                            <img id="previewFoto" src="" alt="Preview foto bukti">
                        </div>
                    </div>

                    <div class="nlp-step-box" id="nlpLiveBox">
                        <div class="text-white fw-bold mb-1"><i class="bi bi-cpu me-1"></i> PIPELINE PREPROCESSING (LIVE)</div>
                        <div id="stepCaseFolding">Case Folding: -</div>
                        <div id="stepFiltering" class="mt-1">Filtering/Stopwords: -</div>
                    </div>

                    <button type="submit" name="proses_nlp" class="btn-submit-premium mt-4">
                        <i class="bi bi-magic"></i> Analisis & Simpan Laporan
                    </button>

                </form>
            </div>
           
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Vendor Library Tambahan: jQuery dan Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // ==== Inisialisasi Select2 Dropdown Search (Tanpa Merusak Script yang Ada) ====
    $(document).ready(function() {
        $('#id_siswa').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Pilih Nama Siswa --',
            allowClear: true,
            width: '100%'
        });

        // Hubungkan event change Select2 ke trigger native JS agar listener di bawah tetap berjalan lancar
        $('#id_siswa').on('select2:select select2:unselect', function (e) {
            this.dispatchEvent(new Event('change'));
        });
    });

    // ==== Info Siswa Terpilih (Kode Utama Anda Tetap Utuh) ====
    const selectSiswa = document.getElementById('id_siswa');
    const infoSiswaBox = document.getElementById('infoSiswaTerpilih');
    selectSiswa.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            infoSiswaBox.innerHTML = `<i class="bi bi-person-badge me-1"></i> NIS: <b>${opt.dataset.nis}</b> &nbsp;|&nbsp; Kelas: <b>${opt.dataset.kelas}</b>`;
            infoSiswaBox.style.display = 'block';
        } else {
            infoSiswaBox.style.display = 'none';
        }
    });

    // ==== Upload Foto Bukti + Preview Real-time ====
    const uploadFotoBox = document.getElementById('uploadFotoBox');
    const fotoInput = document.getElementById('foto_bukti');
    const previewWrapper = document.getElementById('previewWrapper');
    const previewFoto = document.getElementById('previewFoto');
    const btnHapusFoto = document.getElementById('btnHapusFoto');

    uploadFotoBox.addEventListener('click', () => fotoInput.click());

    fotoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const batasUkuran = 5 * 1024 * 1024;
            if (file.size > batasUkuran) {
                fotoInput.value = '';
                Swal.fire('Ukuran Foto Terlalu Besar', 'Ukuran foto bukti maksimal 5 MB.', 'warning');
                return;
            }
            const reader = new FileReader();
            reader.onload = e => {
                previewFoto.src = e.target.result;
                previewWrapper.style.display = 'block';
                uploadFotoBox.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

    btnHapusFoto.addEventListener('click', function() {
        fotoInput.value = '';
        previewFoto.src = '';
        previewWrapper.style.display = 'none';
        uploadFotoBox.style.display = 'block';
    });

    // ==== Fitur Live Preprocessing Teks untuk Demonstrasi ====
    const textInput = document.getElementById('narasi_laporan');
    const nlpBox = document.getElementById('nlpLiveBox');
    const stepCF = document.getElementById('stepCaseFolding');
    const stepFilter = document.getElementById('stepFiltering');

    textInput.addEventListener('input', function() {
        const text = this.value;
        if(text.length > 5) {
            nlpBox.style.display = 'block';
            
            const caseFolding = text.toLowerCase();
            stepCF.innerHTML = `<span class='text-warning'>[1] Case Folding:</span> "${caseFolding}"`;
            
            const cleanText = caseFolding.replace(/[^\w\s]/gi, '');
            stepFilter.innerHTML = `<span class='text-warning'>[2] Filtering:</span> "${cleanText}"`;
        } else {
            nlpBox.style.display = 'none';
        }
    });

    // ==== Validasi & Efek Loading saat Form di-Submit ====
    document.getElementById('formLaporan').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!fotoInput.files[0]) {
            Swal.fire('Foto Wajib Diunggah', 'Silakan unggah foto bukti kejadian terlebih dahulu.', 'warning');
            return;
        }
        
        Swal.fire({
            title: 'Memproses Teks NLP...',
            html: 'Sistem sedang mengekstraksi entitas & menghitung kemiripan dokumen menggunakan pembobotan <b>TF-IDF & Cosine Similarity</b>...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                setTimeout(() => {
                    document.getElementById('formLaporan').submit();
                }, 1200);
            }
        });
    });

    <?php if (isset($_GET['pesan'])): ?>
        <?php if ($_GET['pesan'] == 'siswa_tidak_ditemukan'): ?>
        Swal.fire('Gagal', 'Siswa yang dipilih tidak valid, silakan coba lagi.', 'error');
        <?php elseif ($_GET['pesan'] == 'foto_wajib'): ?>
        Swal.fire('Gagal', 'Foto bukti wajib diunggah (format JPG/PNG/WEBP, maks 5MB).', 'error');
        <?php elseif ($_GET['pesan'] == 'foto_tidak_valid'): ?>
        Swal.fire('Gagal', 'Format foto harus JPG, JPEG, PNG, atau WEBP dan ukurannya maksimal 5MB.', 'error');
        <?php elseif ($_GET['pesan'] == 'foto_gagal_simpan'): ?>
        Swal.fire('Gagal', 'Foto berhasil diterima tetapi gagal disimpan di server. Silakan coba kembali.', 'error');
        <?php elseif ($_GET['pesan'] == 'foto_terlalu_besar_server'): ?>
        Swal.fire('Gagal', 'Ukuran foto melebihi batas upload server.', 'error');
        <?php elseif ($_GET['pesan'] == 'data_tidak_lengkap'): ?>
        Swal.fire('Gagal', 'Siswa dan deskripsi kejadian wajib diisi.', 'error');
        <?php endif; ?>
    <?php endif; ?>
</script>
</body>
</html>
