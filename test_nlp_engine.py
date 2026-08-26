import unittest

from nlp_engine import preprocess_text, split_clauses, classify_payload


MASTER = [
    {'ID': 1, 'Tipe': 'Punishment', 'Keterangan_Nominal': 'siswa terlambat masuk kelas', 'Poin': 10},
    {'ID': 2, 'Tipe': 'Punishment', 'Keterangan_Nominal': 'siswa tidak membawa buku', 'Poin': 5},
    {'ID': 3, 'Tipe': 'Reward', 'Keterangan_Nominal': 'siswa membantu guru', 'Poin': 5},
]


class PreprocessTest(unittest.TestCase):
    def test_stemming_menyamakan_kata_berimbuhan(self):
        self.assertIn('bantu', preprocess_text('Siswa membantu gurunya'))

    def test_stopword_tidak_menghapus_negasi(self):
        tokens = preprocess_text('Siswa tidak membawa buku di kelas').split()
        self.assertIn('tidak', tokens)
        self.assertIn('bawa', tokens)

    def test_kalimat_bertingkat_dipecah_menjadi_klausa(self):
        clauses = split_clauses('Siswa terlambat karena hujan, tetapi kemudian membantu guru.')
        self.assertEqual(clauses, ['siswa terlambat', 'hujan', 'kemudian membantu guru'])

    def test_klasifikasi_tetap_mendeteksi_dua_perilaku(self):
        result = classify_payload({
            'kalimat_input': 'Siswa terlambat tetapi kemudian membantu guru.',
            'master_point': MASTER, 'data_training': [],
        })
        self.assertEqual(result['total_punish'], 10)
        self.assertEqual(result['total_reward'], 5)

    def test_negasi_tetap_mendukung_aturan_pelanggaran(self):
        result = classify_payload({
            'kalimat_input': 'Siswa tidak membawa buku.',
            'master_point': MASTER, 'data_training': [],
        })
        self.assertEqual(result['total_punish'], 5)


if __name__ == '__main__':
    unittest.main()
