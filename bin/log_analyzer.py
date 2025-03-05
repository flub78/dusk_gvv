#!/usr/bin/env python3

import re
import sys
from collections import Counter

def process_log(file_path=None):
    
    # Lire depuis un fichier ou stdin
    if file_path:
        with open(file_path, 'r', encoding='utf-8') as f:
            lines = f.readlines()
    else:
        lines = sys.stdin.readlines()
    
    # Supprimer les timestamps et nettoyer les lignes
    processed_lines = []
    for line in lines:
        # Utiliser regex pour supprimer le timestamp entre crochets
        clean_line = re.sub(r'^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] ', '', line.strip())
        if clean_line:  # Ignorer les lignes vides
            processed_lines.append(clean_line)
    
    # Compter les occurrences
    counter = Counter(processed_lines)
    
    # Trier par texte
    sorted_items = sorted(counter.items(), key=lambda x: x[0])
    
    # Afficher les résultats
    for line, count in sorted_items:
        print(f"{count}\t{line}")

if __name__ == "__main__":
    if len(sys.argv) > 1:
        process_log(sys.argv[1])
    else:
        process_log()