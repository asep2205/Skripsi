import sys
import json
import re
import math
from collections import Counter
from Sastrawi.StopWordRemover.StopWordRemoverFactory import StopWordRemoverFactory

def hitung_idf(corpus):
    N = len(corpus)
    df = Counter()
    for doc in corpus:
        words = set(doc.split())
        for w in words:
            df[w] += 1
    idf = {}
    for w, count in df.items():
        idf[w] = math.log((1 + N) / (1 + count)) + 1
    return idf

def transform_tfidf(teks, idf):
    words = teks.split()
    if not words: return {}
    tf = Counter(words)
    vec = {}
    v_sum_squares = 0
    for w, c in tf.items():
        if w in idf:
            val = c * idf[w]
            vec[w] = val
            v_sum_squares += val ** 2
    mag = math.sqrt(v_sum_squares)
    if mag > 0:
        for w in vec: vec[w] /= mag
    return vec

def hitung_cosine_similarity(vec1, vec2):
    dot_product = 0
    for w in vec1:
        if w in vec2: dot_product += vec1[w] * vec2[w]
    return dot_product

def main():
    try:
        input_data = json.loads(sys.stdin.read())
    except Exception as e:
        print(json.dumps({"error": f"Gagal membaca payload data: {str(e)}"}))
        return

    # PERUBAHAN: kalimat_input sekarang MURNI deskripsi kejadian.
    # Nama & kelas siswa TIDAK lagi diekstrak dari teks karena sudah
    # dipilih langsung lewat dropdown di sisi PHP (proses_analisis_nlp.php).
    kalimat_input = input_data.get('kalimat_input', '')
    master_point = input_data.get('master_point', [])
    data_training = input_data.get('data_training', [])

    if not master_point:
        print(json.dumps({"error": "Data master_poin kosong di database."}))
        return

    map_poin = {str(item['ID']): item['Poin'] for item in master_point}
    for item in data_training:
        poin_terpetakan = map_poin.get(str(item['ID']), 10)
        master_point.append({
            'ID': item['ID'],
            'Tipe': item['Tipe'],
            'Keterangan_Nominal': item['Keterangan_Nominal'],
            'Poin': poin_terpetakan
        })

    factory = StopWordRemoverFactory()
    stopword_remover = factory.create_stop_word_remover()

    def bersihkan_teks(teks):
        if not isinstance(teks, str): return ""
        teks = teks.lower()
        teks = stopword_remover.remove(teks)
        return teks

    corpus_bersih = []
    for item in master_point:
        item['Kata_Kunci_Bersih'] = bersihkan_teks(item['Keterangan_Nominal'])
        corpus_bersih.append(item['Kata_Kunci_Bersih'])

    idf_model = hitung_idf(corpus_bersih)

    for item in master_point:
        item['Vektor_TFIDF'] = transform_tfidf(item['Kata_Kunci_Bersih'], idf_model)

    # Seluruh kalimat dianggap deskripsi perilaku (tidak ada lagi pemisahan nama/kelas)
    laporan_perilaku = kalimat_input.strip()

    klausa_list = re.split(r'\btapi\b|\btetapi\b|\bnamun\b|\bdan\b|\bserta\b|,', laporan_perilaku.lower())
    total_reward = 0
    total_punish = 0
    rincian_deteksi = []

    skor_tertinggi_global = 0.0
    analisa_master_teks = ""

    for klausa in klausa_list:
        klausa = klausa.strip()
        if len(klausa) < 3: continue

        klausa_clean = bersihkan_teks(klausa)
        vektor_input = transform_tfidf(klausa_clean, idf_model)

        best_score = -1
        best_match_idx = -1

        for idx, item in enumerate(master_point):
            score = hitung_cosine_similarity(vektor_input, item['Vektor_TFIDF'])
            if score > best_score:
                best_score = score
                best_match_idx = idx

        if best_score > 0.1 and best_match_idx != -1:
            tipe = master_point[best_match_idx]['Tipe']
            poin = master_point[best_match_idx]['Poin']
            nominal_text = master_point[best_match_idx]['Keterangan_Nominal']

            persentase_cocok = int(round(best_score * 100))
            if persentase_cocok > 100: persentase_cocok = 100

            if best_score > skor_tertinggi_global:
                skor_tertinggi_global = best_score
                analisa_master_teks = nominal_text

            simbol = "+" if str(tipe).strip().lower() == 'reward' else "-"

            rincian_deteksi.append(f"-> [{tipe}] {nominal_text} ({simbol}{poin} Poin) | Tingkat Kecocokan: {persentase_cocok}%")

            if str(tipe).strip().lower() == 'reward':
                total_reward += int(poin)
            elif str(tipe).strip().lower() == 'punishment':
                total_punish += int(poin)

    persentase_global_str = f"{int(round(skor_tertinggi_global * 100))}%"

    output_response = {
        "teks_proses": laporan_perilaku,
        "rincian": rincian_deteksi,
        "total_reward": total_reward,
        "total_punish": total_punish,
        "analisa_master": analisa_master_teks,
        "akurasi_map": persentase_global_str
    }

    print(json.dumps(output_response))

if __name__ == '__main__':
    main()
