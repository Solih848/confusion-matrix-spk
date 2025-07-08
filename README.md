# Sistem Penghitungan Confusion Matrix SPK

Sistem ini digunakan untuk menghitung confusion matrix dari hasil Sistem Pendukung Keputusan (SPK) menggunakan PHP native dan MySQL.

## Fitur

- Upload file CSV dengan format: id, nama_alternatif, nilai_vektor_v, kelayakan_sistem, kelayakan_aktual
- Penghitungan confusion matrix otomatis
- Perhitungan metrik: True Positive, False Positive, True Negative, False Negative
- Perhitungan precision, recall, dan F1-score untuk setiap kelas
- Perhitungan akurasi keseluruhan
- Penyimpanan riwayat perhitungan
- Visualisasi hasil dalam bentuk tabel
- Ekspor data dalam format JSON, SQL, dan CSV
- Responsif untuk berbagai ukuran layar

## Persyaratan Sistem

- PHP 7.0 atau lebih tinggi
- Ekstensi PDO dengan driver MySQL untuk PHP
- Server MySQL
- Web server (Apache, Nginx, dll.)
- Browser modern (Chrome, Firefox, Safari, Edge)

## Cara Instalasi

1. Clone atau download repositori ini
2. Pastikan web server dan MySQL server sudah berjalan
3. Buat database baru dengan nama 'confusion_matrix'
   ```sql
   CREATE DATABASE confusion_matrix;
   ```
4. Pastikan ekstensi PDO dengan driver MySQL untuk PHP sudah diaktifkan
   - Pada php.ini, pastikan extension=pdo_mysql sudah tidak dikomentari (tidak ada tanda ; di depannya)
5. Sesuaikan konfigurasi database di file config.php jika diperlukan:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'confusion_matrix');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_CHARSET', 'utf8mb4');
   ```
6. Buka aplikasi melalui browser

## Struktur Direktori

```
/
├── index.php                # Halaman utama
├── config.php               # Konfigurasi database dan kelas Database
├── confusion_matrix.php     # Kelas ConfusionMatrix untuk perhitungan
├── process.php              # Pemrosesan file CSV
├── get_result.php           # Mengambil hasil confusion matrix
├── delete_dataset.php       # Menghapus dataset
├── export_dataset.php       # Mengekspor dataset
├── database_utilities.php   # Utilitas database untuk ekspor dan impor
├── css/
│   └── style.css            # File CSS
└── js/
    └── script.js            # File JavaScript
```

## Format File CSV & Excel

File CSV yang diupload harus memiliki format sebagai berikut:

```
id,nama_alternatif,nilai_vektor_v,kelayakan_sistem
1,Alternatif A,0.85,Layak
2,Alternatif B,0.75,Layak
3,Alternatif C,0.45,Tidak Layak
...
```

Anda juga dapat mengisi data pada file Excel berikut:
- [Download Template Excel (sample_data.xlsx)](sample_data.xlsx)

Setelah mengisi data di Excel, simpan sebagai CSV (Comma delimited) sebelum upload ke sistem.

Dimana:
- `id`: Pengenal unik untuk setiap data (nomor urut)
- `nama_alternatif`: Nama alternatif yang dinilai
- `nilai_vektor_v`: Nilai vektor V dari perhitungan SPK
- `kelayakan_sistem`: Kelayakan menurut sistem (Layak/Tidak Layak)
- `kelayakan_aktual`: Kelayakan aktual atau sebenarnya (Layak/Tidak Layak)

## Cara Penggunaan

1. Buka halaman utama aplikasi
2. Pada tab "Upload Data", masukkan nama dataset dan pilih file excel dengan format yang sesuai
3. Klik tombol "Proses Data"
4. Lihat hasil perhitungan confusion matrix pada tab "Hasil"
5. Lihat riwayat perhitungan pada tab "Riwayat"
6. Untuk mengekspor data, pilih format yang diinginkan (JSON, SQL, atau CSV)

## Penjelasan Metrik

- **True Positive (TP)**: Jumlah alternatif yang diprediksi LAYAK dan sebenarnya memang LAYAK
- **False Positive (FP)**: Jumlah alternatif yang diprediksi LAYAK tetapi sebenarnya TIDAK LAYAK
- **True Negative (TN)**: Jumlah alternatif yang diprediksi TIDAK LAYAK dan sebenarnya memang TIDAK LAYAK
- **False Negative (FN)**: Jumlah alternatif yang diprediksi TIDAK LAYAK tetapi sebenarnya LAYAK
- **Precision**: Persentase prediksi LAYAK yang benar (TP / (TP + FP))
- **Recall**: Persentase alternatif LAYAK yang diprediksi dengan benar (TP / (TP + FN))
- **F1-Score**: Rata-rata harmonik dari precision dan recall (2 * (precision * recall) / (precision + recall))
- **Akurasi**: Persentase prediksi yang benar dari total data ((TP + TN) / (TP + TN + FP + FN))

## Catatan Teknis

Sistem ini menggunakan PDO (PHP Data Objects) untuk koneksi database MySQL. PDO memberikan lapisan abstraksi akses database yang konsisten, yang memungkinkan kode untuk bekerja dengan berbagai jenis database tanpa perubahan besar. Database MySQL digunakan untuk penyimpanan data yang lebih robust dan skalabel.

## Contoh Data

Berikut adalah contoh data CSV yang dapat digunakan untuk pengujian:

```
id,nama_alternatif,nilai_vektor_v,kelayakan_sistem,kelayakan_aktual
1,Alternatif A,0.85,Layak,Layak
2,Alternatif B,0.75,Layak,Tidak Layak
3,Alternatif C,0.45,Tidak Layak,Tidak Layak
4,Alternatif D,0.92,Layak,Layak
5,Alternatif E,0.38,Tidak Layak,Tidak Layak
```

## Lisensi

Sistem ini dibuat sebagai proyek open source dan dapat digunakan secara bebas. 
