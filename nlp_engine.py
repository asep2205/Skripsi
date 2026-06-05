import math
import re
import csv
import json
import sys
from collections import Counter

STOPWORDS = set([
    'dan', 'yang', 'di', 'ke', 'dari', 'saat', 'dengan', 'oleh', 'pada', 'ini', 'itu',
    'adalah', 'sangat',
])

UMUM_WORDS = {
    'sekolah', 'siswa', 'kelas', 'guru', 'teman', 'selalu', 'tidak',
    'sudah', 'pernah', 'dapat', 'lebih', 'semakin', 'seluruh', 'setiap',
    'antara', 'lain', 'baik', 'sangat', 'untuk', 'atau', 'bisa',
    'masuk', 'waktu', 'lingkungan', 'buang', 'sampah', 'bantu', 'pelajar',
    'belajar', 'tugas', 'hari', 'rapi', 'bersih', 'sopan', 'dengan',
    'telah', 'akan', 'sedang', 'ketika', 'serta', 'ujian', 'pelajaran',
    'kegiatan', 'buku', 'kelas',
}

dataset_training = []
master_poin = []

def load_datasets_csv(filepath='datasets.csv'):
    data = []
    with open(filepath, 'r', encoding='utf-8') as f:
        reader = csv.reader(f)
        for row in reader:
            if len(row) >= 3:
                _, teks, label = row
                data.append((teks.strip(), label.strip()))
    return data

def load_master_poin_csv(filepath='master_point.csv'):
    data = []
    with open(filepath, 'r', encoding='utf-8') as f:
        reader = csv.reader(f)
        for row in reader:
            if len(row) >= 4:
                _, jenis, keywords_str, poin = row
                keywords = [k.strip().lower() for k in keywords_str.split(',') if k.strip()]
                data.append((jenis.strip(), keywords, int(poin), None))
    return data

def preprocess_text(text):
    text = text.lower()
    text = re.sub(r'[^a-z\s]', '', text)
    tokens = text.split()
    filtered_tokens = [t for t in tokens if t not in STOPWORDS and len(t) > 1]
    stemmed_tokens = []
    for token in filtered_tokens:
        if token.startswith('ber') and len(token) >= 7:
            token = token[3:]
        else:
            token = re.sub(r'^(peng|pem|pen|peny|per|mem|di|ter|me)', '', token)
        token = re.sub(r'(i|an|kan|nya)$', '', token)
        stemmed_tokens.append(token)
    return stemmed_tokens

def naive_bayes(tokens_input, alpha=1.0):
    if len(dataset_training) == 0:
        return 'Reward', 0.0

    count_positif = 0
    count_negatif = 0
    vocab_positif = []
    vocab_negatif = []
    all_vocab = []

    for teks, label in dataset_training:
        words = preprocess_text(teks)
        if label == 'Reward':
            count_positif += 1
            vocab_positif.extend(words)
        else:
            count_negatif += 1
            vocab_negatif.extend(words)
        all_vocab.extend(words)

    total_dokumen = count_positif + count_negatif
    if total_dokumen == 0:
        return 'Reward', 0.0
    if count_positif == 0:
        return 'Punishment', 0.0
    if count_negatif == 0:
        return 'Reward', 0.0

    prior_positif = count_positif / total_dokumen
    prior_negatif = count_negatif / total_dokumen

    freq_positif = Counter(vocab_positif)
    freq_negatif = Counter(vocab_negatif)
    total_unique_vocab = len(set(all_vocab))
    total_word_positif = len(vocab_positif)
    total_word_negatif = len(vocab_negatif)

    score_positif = math.log(prior_positif)
    score_negatif = math.log(prior_negatif)

    for token in tokens_input:
        count_w_pos = freq_positif.get(token, 0)
        prob_w_pos = (count_w_pos + alpha) / (total_word_positif + alpha * total_unique_vocab)
        score_positif += math.log(prob_w_pos)

        count_w_neg = freq_negatif.get(token, 0)
        prob_w_neg = (count_w_neg + alpha) / (total_word_negatif + alpha * total_unique_vocab)
        score_negatif += math.log(prob_w_neg)

    max_score = max(score_positif, score_negatif)
    prob_positif = math.exp(score_positif - max_score)
    prob_negatif = math.exp(score_negatif - max_score)
    total_prob = prob_positif + prob_negatif

    if score_positif >= score_negatif:
        confidence = prob_positif / total_prob
        return 'Reward', round(confidence, 4)
    else:
        confidence = prob_negatif / total_prob
        return 'Punishment', round(confidence, 4)

def _ekstrak_kata_kunci(entry_keywords):
    tokens = set()
    for kw in entry_keywords:
        for t in kw.strip().lower().split():
            t = t.strip('(),./\\')
            if len(t) >= 6 and t not in UMUM_WORDS:
                tokens.add(t)
    return tokens

def match_rules(teks_laporan):
    teks_lower = teks_laporan.lower()
    teks_tokens_stemmed = preprocess_text(teks_laporan)
    teks_stemmed = ' ' .join(teks_tokens_stemmed)

    matched_reward = []
    matched_punishment = []

    for idx, entry in enumerate(master_poin):
        if len(entry) == 4:
            jenis, keywords, poin, id_aturan = entry
        else:
            jenis, keywords, poin = entry
            id_aturan = None

        matched_keywords = []
        for keyword in keywords:
            kw = keyword.strip().lower()
            if not kw:
                continue

            kw_match = False

            # Step 1: word boundary pada teks asli (min 5 karakter)
            if not kw_match and len(kw) >= 5:
                pattern = r'(?<!\w)' + re.escape(kw) + r'(?!\w)'
                if re.search(pattern, teks_lower):
                    kw_match = True

            # Step 2: single-token stemmed match (untuk kata berimbuhan)
            if not kw_match:
                kw_stemmed = preprocess_text(kw)
                if len(kw_stemmed) == 1:
                    stem_pattern = r'(?<!\w)' + re.escape(kw_stemmed[0]) + r'(?!\w)'
                    if re.search(stem_pattern, teks_stemmed):
                        kw_match = True

            # Step 3: sub-keyword raw match (untuk kata dalam frase panjang)
            if not kw_match:
                kata_kunci_entry = _ekstrak_kata_kunci([kw])
                for token in kata_kunci_entry:
                    token_pattern = r'(?<!\w)' + re.escape(token) + r'(?!\w)'
                    if re.search(token_pattern, teks_lower):
                        kw_match = True
                        break

            if kw_match:
                matched_keywords.append(kw)

        # AND logic: semua keyword harus cocok
        if len(matched_keywords) == len([k for k in keywords if k.strip()]):
            matched_name = ', '.join(matched_keywords) if len(matched_keywords) > 1 else matched_keywords[0]
            entry = (id_aturan or (idx + 1), matched_name, poin)
            if jenis == 'Reward':
                matched_reward.append(entry)
            else:
                matched_punishment.append(entry)

    return matched_reward, matched_punishment

def klasifikasi(teks_laporan, alpha=1.0):
    tokens = preprocess_text(teks_laporan)
    matched_reward, matched_punishment = match_rules(teks_laporan)

    total_poin_reward = sum(p for _, _, p in matched_reward)
    total_poin_punishment = sum(p for _, _, p in matched_punishment)

    reward_terbaik = max(matched_reward, key=lambda x: x[2]) if matched_reward else None
    punishment_terbaik = max(matched_punishment, key=lambda x: x[2]) if matched_punishment else None

    if matched_reward and not matched_punishment:
        label = 'Reward'
        aturan = reward_terbaik
        confidence = 1.0
    elif matched_punishment and not matched_reward:
        label = 'Punishment'
        aturan = punishment_terbaik
        confidence = 1.0
    elif matched_reward and matched_punishment:
        if reward_terbaik[2] > punishment_terbaik[2]:
            label, _ = naive_bayes(tokens, alpha)
            aturan = reward_terbaik if label == 'Reward' else punishment_terbaik
            confidence = 1.0 if label == 'Reward' else _  # keep NB confidence for punishment
            # re-get confidence
            label, confidence = naive_bayes(tokens, alpha)
        elif punishment_terbaik[2] > reward_terbaik[2]:
            label, confidence = naive_bayes(tokens, alpha)
            aturan = reward_terbaik if label == 'Reward' else punishment_terbaik
        else:
            label = 'Reward'
            aturan = reward_terbaik
            confidence = 1.0
    else:
        label, confidence = naive_bayes(tokens, alpha)
        aturan = None

    aturan_dict = None
    if aturan:
        aturan_dict = {
            'id': aturan[0],
            'nama': aturan[1],
            'poin': aturan[2],
        }

    daftar_reward = [[r[0], r[1], r[2]] for r in matched_reward]
    daftar_punishment = [[p[0], p[1], p[2]] for p in matched_punishment]

    return {
        'label': label,
        'aturan': aturan_dict,
        'total_poin_reward': total_poin_reward,
        'total_poin_punishment': total_poin_punishment,
        'daftar_reward': daftar_reward,
        'daftar_punishment': daftar_punishment,
        'confidence': confidence,
        'akurasi_persen': f'{confidence * 100:.2f}%',
    }

def teks_dikenali(teks_input):
    tokens = preprocess_text(teks_input)
    if not tokens:
        return False
    all_tokens = []
    for teks, _ in dataset_training:
        all_tokens.extend(preprocess_text(teks))
    known_vocab = set(all_tokens)
    return any(t in known_vocab for t in tokens)


if __name__ == '__main__':
    input_data = json.loads(sys.stdin.read())

    dataset_training.clear()
    for d in input_data.get('dataset_training', []):
        dataset_training.append((d[0], d[1]))

    master_poin.clear()
    for d in input_data.get('master_poin', []):
        keywords = [k.strip().lower() for k in d[2].split(',') if k.strip()]
        master_poin.append((d[1].strip(), keywords, int(d[3]), int(d[0])))

    result = klasifikasi(
        input_data['teks_laporan'],
        input_data.get('alpha', 1.0),
    )

    print(json.dumps(result))
