import sys
sys.path.insert(0, '.')

from nlp_engine import (
    preprocess_text, naive_bayes, klasifikasi, match_rules,
    dataset_training, master_poin, load_datasets_csv, load_master_poin_csv
)

# ============================================================
# LOAD DATA DARI CSV
# ============================================================
dataset_training.extend(load_datasets_csv('datasets.csv'))
master_poin.extend(load_master_poin_csv('master_point.csv'))

# ============================================================
# EVALUASI
# ============================================================
if __name__ == '__main__':
    print("=" * 80)
    print("SKRIPSI NLP - KLASIFIKASI PERILAKU SISWA (REWARD/PUNISHMENT)")
    print("Data dari datasets.csv & master_point.csv")
    print("=" * 80)

    print(f"\nTotal data training: {len(dataset_training)} samples")
    print(f"Total aturan master_poin: {len(master_poin)} rules")

    # Test Cases
    print("\n" + "=" * 80)
    print("UJI COBA KLASIFIKASI")
    print("=" * 80)

    test_cases = [
        "Siswa telat masuk sekolah tetapi membantu guru untuk membersihkan lingkunan sekolah",
        "Siswa telat masuk sekolah",
        "Siswa merokok di lingkungan sekolah",
        "Siswa berkelahi dengan teman di kelas",
        "Siswa selalu mengerjakan tugas tepat waktu dan disiplin",
        "Siswa meraih juara 1 lomba olahraga tingkat provinsi",
        "Siswa ikut merapikan buku di perpustakaan sekolah",
        "Siswa membawa minuman keras ke sekolah",
        "Siswa tidak memakai seragam lengkap",
        "Siswa rajin belajar dan aktif di kelas",
        "siswa bolos saat jam pelajaran",
        "siswa membantu petugas merapikan buku perpustakaan",
    ]

    for teks in test_cases:
        hasil = klasifikasi(teks)
        print(f"\nTeks  : {teks}")
        print(f"{'─' * 70}")
        label = hasil['label']
        aturan = hasil.get('aturan')
        if aturan:
            print(f"  KEPUTUSAN AKHIR : {label}")
            print(f"  {label}         : {aturan['nama']} ({aturan['poin']} poin)")
        else:
            print(f"  KEPUTUSAN AKHIR : {label} (Naive Bayes)")
        print(f"  Akurasi         : {hasil['akurasi_persen']}")
        print(f"  Total Poin      : Reward={hasil['total_poin_reward']}, Punishment={hasil['total_poin_punishment']}")
        print(f"  Daftar Reward   : {[[r[1], r[2]] for r in hasil['daftar_reward']]}")
        print(f"  Daftar Punishment: {[[p[1], p[2]] for p in hasil['daftar_punishment']]}")

    # EVALUASI AKURASI
    print("\n" + "=" * 80)
    print("EVALUASI AKURASI MENGGUNAKAN TRAINING DATA")
    print("=" * 80)

    y_true = []
    y_pred = []

    for teks, label_asli in dataset_training:
        hasil = klasifikasi(teks)
        y_true.append(label_asli)
        y_pred.append(hasil['label'])

    tp = sum(1 for t, p in zip(y_true, y_pred) if t == 'Reward' and p == 'Reward')
    tn = sum(1 for t, p in zip(y_true, y_pred) if t == 'Punishment' and p == 'Punishment')
    fp = sum(1 for t, p in zip(y_true, y_pred) if t == 'Punishment' and p == 'Reward')
    fn = sum(1 for t, p in zip(y_true, y_pred) if t == 'Reward' and p == 'Punishment')

    accuracy = (tp + tn) / len(y_true)
    precision = tp / (tp + fp) if (tp + fp) > 0 else 0
    recall = tp / (tp + fn) if (tp + fn) > 0 else 0
    f1 = 2 * precision * recall / (precision + recall) if (precision + recall) > 0 else 0

    print(f"\nTotal Data Training: {len(y_true)}")
    print(f"Accuracy : {accuracy:.4f} ({accuracy*100:.2f}%)")
    print(f"Precision: {precision:.4f} ({precision*100:.2f}%)")
    print(f"Recall   : {recall:.4f} ({recall*100:.2f}%)")
    print(f"F1-Score : {f1:.4f} ({f1*100:.2f}%)")

    print(f"\nConfusion Matrix:")
    print(f"                Predicted")
    print(f"                Reward  Punishment")
    print(f"Actual Reward   {tp:6d}  {fn:6d}")
    print(f"       Punishment {fp:6d}  {tn:6d}")

    reward_total = tp + fn
    punishment_total = tn + fp
    print(f"\nAkurasi per Kelas:")
    print(f"Reward    : {tp}/{reward_total} ({tp/reward_total*100:.2f}%)")
    print(f"Punishment: {tn}/{punishment_total} ({tn/punishment_total*100:.2f}%)")

    # DETAIL 20 SAMPLE PERTAMA
    print("\n" + "=" * 80)
    print("DETAIL 20 SAMPLE PERTAMA")
    print("=" * 80)

    salah = 0
    benar = 0
    detail = []

    for i, (teks, label_asli) in enumerate(dataset_training[:20]):
        hasil = klasifikasi(teks)
        status = "OK" if hasil['label'] == label_asli else "FAIL"
        if hasil['label'] == label_asli:
            benar += 1
        else:
            salah += 1
        detail.append((teks[:60], label_asli, hasil['label'], status))

    print(f"{'No':<4} {'Teks':<62} {'Asli':<12} {'Pred':<12} Status")
    print("-" * 100)
    for i, (teks, asli, pred, status) in enumerate(detail, 1):
        print(f"{i:<4} {teks:<62} {asli:<12} {pred:<12} {status}")

    print(f"\nBenar: {benar}, Salah: {salah}, Akurasi: {benar/(benar+salah)*100:.2f}%")

    # HYPERPARAMETER TUNING
    print("\n" + "=" * 80)
    print("HYPERPARAMETER TUNING - GRID SEARCH (alpha smoothing)")
    print("=" * 80)

    alpha_values = [0.1, 0.5, 1.0, 2.0]

    reward_samples = [(t, l) for t, l in dataset_training if l == 'Reward'][:100]
    punishment_samples = [(t, l) for t, l in dataset_training if l == 'Punishment'][:100]
    tuning_sample = reward_samples + punishment_samples

    hasil_tuning = []
    for alpha in alpha_values:
        y_true_hp = []
        y_pred_hp = []
        for teks, label_asli in tuning_sample:
            hasil = klasifikasi(teks, alpha=alpha)
            y_true_hp.append(label_asli)
            y_pred_hp.append(hasil['label'])

        tp_hp = sum(1 for t, p in zip(y_true_hp, y_pred_hp) if t == 'Reward' and p == 'Reward')
        tn_hp = sum(1 for t, p in zip(y_true_hp, y_pred_hp) if t == 'Punishment' and p == 'Punishment')
        fp_hp = sum(1 for t, p in zip(y_true_hp, y_pred_hp) if t == 'Punishment' and p == 'Reward')
        fn_hp = sum(1 for t, p in zip(y_true_hp, y_pred_hp) if t == 'Reward' and p == 'Punishment')

        acc_hp = (tp_hp + tn_hp) / len(y_true_hp)
        prec_hp = tp_hp / (tp_hp + fp_hp) if (tp_hp + fp_hp) > 0 else 0
        rec_hp = tp_hp / (tp_hp + fn_hp) if (tp_hp + fn_hp) > 0 else 0
        f1_hp = 2 * prec_hp * rec_hp / (prec_hp + rec_hp) if (prec_hp + rec_hp) > 0 else 0

        hasil_tuning.append((alpha, acc_hp, prec_hp, rec_hp, f1_hp))

    print(f"\n{'Alpha':<8} {'Accuracy':<12} {'Precision':<12} {'Recall':<12} {'F1-Score':<12}")
    print("-" * 56)
    for alpha, acc, prec, rec, f1 in hasil_tuning:
        print(f"{alpha:<8} {acc:<12.4f} {prec:<12.4f} {rec:<12.4f} {f1:<12.4f}")

    best = max(hasil_tuning, key=lambda x: x[4])
    print(f"\nBEST HYPERPARAMETER:")
    print(f"  Alpha  : {best[0]}")
    print(f"  F1-Score: {best[4]:.4f} ({best[4]*100:.2f}%)")

    print("\n" + "=" * 80)
    print("SELESAI")
    print("=" * 80)
