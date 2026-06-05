import sys
import json
import os

from nlp_engine import (
    klasifikasi, dataset_training, master_poin,
    load_datasets_csv, load_master_poin_csv, teks_dikenali
)

os.chdir(os.path.dirname(os.path.abspath(__file__)))

dataset_training.extend(load_datasets_csv('datasets.csv'))
master_poin.extend(load_master_poin_csv('master_point.csv'))

if len(sys.argv) > 1:
    teks_laporan = ' '.join(sys.argv[1:])
else:
    teks_laporan = sys.stdin.read().strip()

result = klasifikasi(teks_laporan)

# Cek apakah teks memiliki kata yang dikenali dari dataset training
result['dikenali'] = teks_dikenali(teks_laporan)

# Cek apakah teks mengandung pola keyboard mash / karakter acak
import re
tokens = teks_laporan.lower().split()
consecutive_no_vowel = 0
long_consonant_run = 0
for t in tokens:
    if len(t) <= 3:
        continue
    # Kata tanpa huruf hidup sama sekali
    if all(c not in 'aiueo' for c in t):
        consecutive_no_vowel += 1
    # Kata dengan 5+ konsonan beruntun (pola keyboard mash)
    runs = re.findall(r'[bcdfghjklmnpqrstvwxyz]{5,}', t)
    if runs:
        long_consonant_run += len(runs)
result['garbage_flags'] = {
    'consecutive_no_vowel': consecutive_no_vowel,
    'long_consonant_run': long_consonant_run,
}

print(json.dumps(result))
