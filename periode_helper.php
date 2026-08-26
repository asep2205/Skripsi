<?php
/** Mengambil periode tahun ajaran yang sedang dipakai transaksi poin. */
function periode_aktif(mysqli $koneksi): ?array
{
    $query = mysqli_query($koneksi, "SELECT id_periode, tahun_ajaran, tanggal_mulai, tanggal_selesai
                                    FROM periode_akademik WHERE status = 'aktif' LIMIT 1");
    return $query ? (mysqli_fetch_assoc($query) ?: null) : null;
}

function id_periode_aktif(mysqli $koneksi): int
{
    $periode = periode_aktif($koneksi);
    return $periode ? (int)$periode['id_periode'] : 0;
}
?>
