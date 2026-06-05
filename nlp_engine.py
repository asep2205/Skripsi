import math
import re
import json
import sys
from collections import Counter

STOPWORDS = set([
    'dan', 'yang', 'di', 'ke', 'dari', 'saat', 'dengan', 'oleh', 'pada', 'ini', 'itu',
    'adalah', 'sangat',
])

dataset_training = []
master_poin = []

def preprocess_text(text):
    text = text.lower()
    text = re.sub(r'[^a-z\s]', '', text)
    tokens = text.split()
    filtered_tokens = [t for t in tokens if t not in STOPWORDS and len(t) > 1]
    stemmed_tokens = []
    for token in filtered_tokens:
        token = re.sub(r'^(mem|ber|di|ter|me)', '', token)
        token = re.sub(r'(an|kan|nya)$', '', token)
        stemmed_tokens.append(token)
    return stemmed_tokens

def naive_bayes(tokens_input, poin_reward_siswa=0, poin_punishment_siswa=0):
    if len(dataset_training) == 0:
        return 'Reward'

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
        return 'Reward'

    if count_positif == 0:
        return 'Punishment'
    if count_negatif == 0:
        return 'Reward'

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
        prob_w_pos = (count_w_pos + 1) / (total_word_positif + total_unique_vocab)
        score_positif += math.log(prob_w_pos)

        count_w_neg = freq_negatif.get(token, 0)
        prob_w_neg = (count_w_neg + 1) / (total_word_negatif + total_unique_vocab)
        score_negatif += math.log(prob_w_neg)

    total_historis = poin_reward_siswa + poin_punishment_siswa
    if total_historis > 0:
        selisih = poin_punishment_siswa - poin_reward_siswa
        normalized_bias = selisih / total_historis
        bobot_historis = normalized_bias * 0.05
        score_negatif += bobot_historis
        score_positif -= bobot_historis

    return 'Reward' if score_positif >= score_negatif else 'Punishment'

def _parse_master_poin(entry):
    if len(entry) == 4:
        return entry
    jenis, nama_perilaku, poin = entry
    return (0, jenis, nama_perilaku, poin)


def klasifikasi(teks_laporan, poin_reward_siswa=0, poin_punishment_siswa=0):
    tokens = preprocess_text(teks_laporan)

    total_poin_reward = 0
    total_poin_punishment = 0
    daftar_reward = []
    daftar_punishment = []
    reward_terbaik = None
    punishment_terbaik = None

    for entry in master_poin:
        id_aturan, jenis, nama_perilaku, poin = _parse_master_poin(entry)
        if nama_perilaku.lower() in teks_laporan.lower():
            if jenis == 'Reward':
                total_poin_reward += poin
                daftar_reward.append((id_aturan, nama_perilaku, poin))
                if reward_terbaik is None or poin > reward_terbaik[2]:
                    reward_terbaik = (id_aturan, nama_perilaku, poin)
            else:
                total_poin_punishment += poin
                daftar_punishment.append((id_aturan, nama_perilaku, poin))
                if punishment_terbaik is None or poin > punishment_terbaik[2]:
                    punishment_terbaik = (id_aturan, nama_perilaku, poin)

    tolak = False
    pesan = ""

    if total_poin_punishment > total_poin_reward:
        label = 'Punishment'
        aturan = punishment_terbaik
    elif total_poin_reward > total_poin_punishment:
        label = 'Reward'
        aturan = reward_terbaik
    else:
        label = naive_bayes(tokens, poin_reward_siswa, poin_punishment_siswa)
        aturan = None

        if label == 'Reward' and daftar_reward:
            aturan = reward_terbaik
        elif label == 'Punishment' and daftar_punishment:
            aturan = punishment_terbaik

        if aturan is None:
            for entry in sorted(master_poin, key=lambda x: _parse_master_poin(x)[3], reverse=True):
                id_aturan, jenis, nama_perilaku, poin = _parse_master_poin(entry)
                if jenis == label and nama_perilaku.lower() in teks_laporan.lower():
                    aturan = (id_aturan, nama_perilaku, poin)
                    break

        if aturan is None and tokens:
            best_score = 0
            best_sample = ''
            for teks, lb in dataset_training:
                if lb == label:
                    sample_tokens = preprocess_text(teks)
                    overlap = len(set(tokens) & set(sample_tokens))
                    if overlap > best_score:
                        best_score = overlap
                        best_sample = teks
            if best_score > 0:
                for entry in sorted(master_poin, key=lambda x: _parse_master_poin(x)[3], reverse=True):
                    id_aturan, jenis, nama_perilaku, poin = _parse_master_poin(entry)
                    if jenis == label and nama_perilaku.lower() in best_sample.lower():
                        aturan = (id_aturan, nama_perilaku, poin)
                        break

        if aturan is None:
            if not teks_dikenali(teks_laporan):
                tolak = True
                pesan = "Teks laporan tidak dikenali. Tidak ada aturan maupun data training yang cocok."
            else:
                for entry in sorted(master_poin, key=lambda x: _parse_master_poin(x)[3], reverse=True):
                    id_aturan, jenis, nama_perilaku, poin = _parse_master_poin(entry)
                    if jenis == label:
                        aturan = (id_aturan, nama_perilaku, poin)
                        break

    return {
        'label': label,
        'aturan': {'id': aturan[0], 'nama': aturan[1], 'poin': aturan[2]} if aturan else None,
        'total_poin_reward': total_poin_reward,
        'total_poin_punishment': total_poin_punishment,
        'daftar_reward': [[r[0], r[1], r[2]] for r in daftar_reward],
        'daftar_punishment': [[p[0], p[1], p[2]] for p in daftar_punishment],
        'tolak': tolak,
        'pesan': pesan,
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
        master_poin.append((d[0], d[1], d[2], d[3]))

    result = klasifikasi(
        input_data['teks_laporan'],
        input_data.get('poin_reward_siswa', 0),
        input_data.get('poin_punishment_siswa', 0),
    )

    print(json.dumps(result))
