<?php

/**
 * File setup.php untuk memudahkan pembuatan database dan tabel MySQL
 * untuk Sistem Penghitungan Confusion Matrix SPK
 */

// Konfigurasi Database MySQL
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'confusion_matrix');
define('DB_CHARSET', 'utf8mb4');

// Fungsi untuk menampilkan pesan
function showMessage($message, $isError = false)
{
    echo '<div style="padding: 10px; margin: 10px 0; background-color: ' . ($isError ? '#ffdddd' : '#ddffdd') . '; border-left: 5px solid ' . ($isError ? '#f44336' : '#4CAF50') . ';">';
    echo $message;
    echo '</div>';
}

// Tampilkan header
echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Sistem Penghitungan Confusion Matrix SPK</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-left: 5px solid #3498db;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Setup Sistem Penghitungan Confusion Matrix SPK</h1>
';

// Cek ekstensi PDO dan MySQL
if (!extension_loaded('pdo')) {
    showMessage('Ekstensi PDO tidak tersedia. Mohon aktifkan ekstensi PDO di php.ini Anda.', true);
    echo '</body></html>';
    exit;
}

if (!extension_loaded('pdo_mysql')) {
    showMessage('Ekstensi PDO MySQL tidak tersedia. Mohon aktifkan ekstensi pdo_mysql di php.ini Anda.', true);
    echo '</body></html>';
    exit;
}

try {
    // Koneksi ke server MySQL tanpa memilih database
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cek apakah database sudah ada
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    $dbExists = $stmt->fetchColumn();

    if (!$dbExists) {
        // Buat database jika belum ada
        $pdo->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET " . DB_CHARSET);
        showMessage("Database '" . DB_NAME . "' berhasil dibuat.");
    } else {
        showMessage("Database '" . DB_NAME . "' sudah ada.");
    }

    // Pilih database
    $pdo->exec("USE " . DB_NAME);

    // Buat tabel dataset
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS dataset (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            accuracy FLOAT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');
    showMessage("Tabel 'dataset' berhasil dibuat atau sudah ada.");

    // Buat tabel confusion_matrix
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS confusion_matrix (
            id INT AUTO_INCREMENT PRIMARY KEY,
            dataset_id INT,
            class_name VARCHAR(100),
            true_positive INT DEFAULT 0,
            false_positive INT DEFAULT 0,
            true_negative INT DEFAULT 0,
            false_negative INT DEFAULT 0,
            precision_val FLOAT DEFAULT 0,
            recall_val FLOAT DEFAULT 0,
            f1_score FLOAT DEFAULT 0,
            FOREIGN KEY (dataset_id) REFERENCES dataset(id) ON DELETE CASCADE
        )
    ');
    showMessage("Tabel 'confusion_matrix' berhasil dibuat atau sudah ada.");

    // Buat tabel raw_data untuk SPK
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS raw_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            dataset_id INT,
            data_id VARCHAR(50),
            nama_alternatif VARCHAR(255),
            nilai_vektor_v FLOAT,
            kelayakan_sistem VARCHAR(100),
            kelayakan_aktual VARCHAR(100),
            FOREIGN KEY (dataset_id) REFERENCES dataset(id) ON DELETE CASCADE
        )
    ');
    showMessage("Tabel 'raw_data' berhasil dibuat atau sudah ada.");

    // Tambahkan indeks untuk mempercepat query
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_raw_data_dataset_id ON raw_data (dataset_id)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_confusion_matrix_dataset_id ON confusion_matrix (dataset_id)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_datasets_created_at ON dataset (created_at)');
    showMessage("Indeks-indeks berhasil dibuat.");

    showMessage("Setup database berhasil! Sistem siap digunakan.");
} catch (PDOException $e) {
    showMessage('Error: ' . $e->getMessage(), true);
}

// Tampilkan footer dan link ke halaman utama
echo '
    <p>Konfigurasi database saat ini:</p>
    <pre>
Host: ' . DB_HOST . '
Database: ' . DB_NAME . '
User: ' . DB_USER . '
Charset: ' . DB_CHARSET . '
    </pre>
    
    <p>Jika Anda perlu mengubah konfigurasi database, silakan edit file config.php.</p>
    
    <a href="index.php" class="btn">Ke Halaman Utama</a>
</body>
</html>';
