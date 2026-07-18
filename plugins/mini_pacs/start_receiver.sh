#!/bin/bash
# Dicom Receiver SCP Starter

# Setup
PLUGIN_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "${PLUGIN_DIR}/../../" && pwd)"
UPLOAD_DIR="${ROOT_DIR}/uploads/pacs"
PORT=11112
AET="MINIPACS"

# Bikin folder pacs jika belum ada
mkdir -p "$UPLOAD_DIR"

# Cek apakah DCMTK terinstall
if ! command -v storescp &> /dev/null
then
    echo "DCMTK belum terinstall! Jalankan 'brew install dcmtk' atau 'apt install dcmtk' atau 'yum install dcmtk' terlebih dahulu."
    exit 1
fi

echo "=========================================="
echo "Memulai Mini PACS DICOM Receiver..."
echo "Port: $PORT"
echo "AET : $AET"
echo "Dir : $UPLOAD_DIR"
echo "=========================================="
echo "Menunggu file DICOM... (tekan Ctrl+C untuk berhenti)"

# Jalankan storescp listener
# -od : output directory
# -aet : application entity title
# --exec-on-reception : hook command saat file diterima
# "#p" = path folder, "#f" = filename (argument dari storescp)
storescp -v $PORT -od "$UPLOAD_DIR" -aet "$AET" --exec-on-reception "php $PLUGIN_DIR/receiver.php \"#p\" \"#f\""
