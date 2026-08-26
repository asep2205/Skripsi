"""Mesin TF-IDF/Cosine untuk klasifikasi perilaku siswa.

Preprocessing dibuat terpusat agar teks laporan, data training, dan master
poin selalu melewati aturan stopword dan stemming yang sama.
"""
import json
import math
import re
import sys
import unicodedata
from collections import Counter

try:
    from Sastrawi.StopWordRemover.StopWordRemoverFactory import StopWordRemoverFactory
    from Sastrawi.Stemmer.StemmerFactory import StemmerFactory

    _sastrawi_stopwords = set(StopWordRemoverFactory().get_stop_words())
    _stemmer = StemmerFactory().create_stemmer()
except ImportError:
    # Fallback membuat aplikasi tetap berjalan ketika Python Apache belum
    # memiliki Sastrawi. Produksi tetap disarankan memasang Sastrawi.
    _sastrawi_stopwords = {
        'yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'dengan', 'pada', 'sebagai',
        'oleh', 'atau', 'karena', 'dalam', 'ini', 'itu', 'sebuah', 'para', 'saat',
        'telah', 'sudah', 'akan', 'sedang', 'adalah', 'agar', 'juga', 'kemudian',
    }
    _stemmer = None

# Kata negasi tidak boleh dihapus: "tidak membawa buku" tidak boleh menjadi
# "membawa buku", karena maknanya menjadi berlawanan.
NEGATION_WORDS = {'tidak', 'bukan', 'belum', 'jangan', 'tanpa'}
STOPWORDS = _sastrawi_stopwords - NEGATION_WORDS


def _fallback_stem(token):
    """Stemming ringan dan konservatif bila Sastrawi belum terpasang."""
    irregular = {'membantu': 'bantu', 'membawa': 'bawa', 'mengikuti': 'ikut'}
    if token in irregular:
        return irregular[token]
    word = token
    for suffix in ('lah', 'kah', 'tah', 'pun', 'nya', 'kan', 'an', 'i'):
        if word.endswith(suffix) and len(word) - len(suffix) >= 4:
            word = word[:-len(suffix)]
            break
    # Prefix yang tidak mengubah huruf awal akar kata secara agresif.
    for prefix in ('diper', 'memper', 'peng', 'peny', 'meng', 'meny', 'men', 'mem',
                   'ber', 'ter', 'per', 'ke', 'di'):
        if word.startswith(prefix) and len(word) - len(prefix) >= 4:
            word = word[len(prefix):]
            break
    return word


def stem_token(token):
    if token in NEGATION_WORDS:
        return token
    return _stemmer.stem(token) if _stemmer else _fallback_stem(token)


def preprocess_text(text):
    """Case folding, tokenisasi, stopword removal, lalu stemming Indonesia."""
    if not isinstance(text, str):
        return ''
    text = unicodedata.normalize('NFKC', text).lower()
    # Angka dan tanda baca bukan token perilaku; apostrof/strip juga dipisah.
    tokens = re.findall(r"[a-zA-Z]+", text)
    result = []
    for token in tokens:
        if len(token) <= 1 or token in STOPWORDS:
            continue
        stemmed = stem_token(token)
        if len(stemmed) > 1:
            result.append(stemmed)
    return ' '.join(result)


def split_clauses(text):
    """Pisahkan kalimat majemuk/bertingkat tanpa menghilangkan isi klausa.

    Konjungsi pertentangan dan sebab-akibat dipisah karena sering mengandung
    dua perilaku berbeda. `dan`/`serta` tetap dipisah untuk mendeteksi dua
    kejadian seperti "terlambat dan merokok".
    """
    if not isinstance(text, str):
        return []
    normalized = unicodedata.normalize('NFKC', text).lower()
    boundary = r'(?:[.!?;]+|,|\b(?:tetapi|tapi|namun|sedangkan|meskipun|walaupun|karena|sehingga|dan|serta)\b)'
    return [part.strip() for part in re.split(boundary, normalized) if len(part.strip()) >= 3]


def hitung_idf(corpus):
    count_documents = len(corpus)
    document_frequency = Counter()
    for document in corpus:
        for word in set(document.split()):
            document_frequency[word] += 1
    return {word: math.log((1 + count_documents) / (1 + count)) + 1
            for word, count in document_frequency.items()}


def transform_tfidf(text, idf):
    words = text.split()
    if not words:
        return {}
    term_frequency = Counter(words)
    vector = {word: count * idf[word] for word, count in term_frequency.items() if word in idf}
    magnitude = math.sqrt(sum(value ** 2 for value in vector.values()))
    return {word: value / magnitude for word, value in vector.items()} if magnitude else {}


def hitung_cosine_similarity(vector_one, vector_two):
    return sum(value * vector_two.get(word, 0) for word, value in vector_one.items())


def classify_payload(input_data):
    kalimat_input = input_data.get('kalimat_input', '')
    master_point = [dict(item) for item in input_data.get('master_point', [])]
    data_training = input_data.get('data_training', [])
    if not master_point:
        return {'error': 'Data master_poin kosong di database.'}

    map_poin = {str(item['ID']): item['Poin'] for item in master_point}
    for item in data_training:
        master_point.append({
            'ID': item['ID'], 'Tipe': item['Tipe'],
            'Keterangan_Nominal': item['Keterangan_Nominal'],
            'Poin': map_poin.get(str(item['ID']), 10),
        })

    corpus = []
    for item in master_point:
        item['Kata_Kunci_Bersih'] = preprocess_text(item['Keterangan_Nominal'])
        corpus.append(item['Kata_Kunci_Bersih'])
    idf_model = hitung_idf(corpus)
    for item in master_point:
        item['Vektor_TFIDF'] = transform_tfidf(item['Kata_Kunci_Bersih'], idf_model)

    total_reward = total_punish = 0
    details = []
    best_global_score = 0.0
    best_global_rule = ''
    for clause in split_clauses(kalimat_input):
        vector_input = transform_tfidf(preprocess_text(clause), idf_model)
        if not vector_input:
            continue
        best_index, best_score = -1, -1.0
        for index, item in enumerate(master_point):
            score = hitung_cosine_similarity(vector_input, item['Vektor_TFIDF'])
            if score > best_score:
                best_index, best_score = index, score
        if best_index == -1 or best_score <= 0.1:
            continue
        item = master_point[best_index]
        point = int(item['Poin'])
        category = str(item['Tipe']).strip().lower()
        if best_score > best_global_score:
            best_global_score, best_global_rule = best_score, item['Keterangan_Nominal']
        sign = '+' if category == 'reward' else '-'
        details.append(f"-> [{item['Tipe']}] {item['Keterangan_Nominal']} ({sign}{point} Poin) | Tingkat Kecocokan: {min(100, int(round(best_score * 100)))}%")
        if category == 'reward':
            total_reward += point
        elif category == 'punishment':
            total_punish += point

    return {
        'teks_proses': kalimat_input.strip(), 'rincian': details,
        'total_reward': total_reward, 'total_punish': total_punish,
        'analisa_master': best_global_rule,
        'akurasi_map': f"{int(round(best_global_score * 100))}%",
    }


def main():
    try:
        input_data = json.loads(sys.stdin.read())
    except Exception as error:
        print(json.dumps({'error': f'Gagal membaca payload data: {error}'}))
        return
    print(json.dumps(classify_payload(input_data)))


if __name__ == '__main__':
    main()
