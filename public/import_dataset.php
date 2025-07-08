<?php

/**
 * File import_dataset.php untuk mengimpor dataset
 * dari file JSON untuk Sistem Penghitungan Confusion Matrix
 */

// Muat utilitas database
require_once 'database_utilities.php';

// Tampilkan header
echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Dataset - Sistem Penghitungan Confusion Matrix</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Import Dataset</h1>';

// Fungsi untuk menampilkan pesan
function showMessage($message, $isError = false)
{
    echo '<div style="padding: 10px; margin: 10px 0; background-color: ' . ($isError ? '#ffdddd' : '#ddffdd') . '; border-left: 5px solid ' . ($isError ? '#f44336' : '#4CAF50') . ';">';
    echo $message;
    echo '</div>';
}

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Periksa apakah ada file yang diupload
    if (!isset($_FILES['json_file']) || $_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
        showMessage('Terjadi kesalahan saat upload file. Silakan coba lagi.', true);
    } else {
        // Periksa apakah file adalah JSON
        $fileInfo = pathinfo($_FILES['json_file']['name']);
        if ($fileInfo['extension'] !== 'json') {
            showMessage('File harus berformat JSON.', true);
        } else {
            // Baca file JSON
            $jsonFile = $_FILES['json_file']['tmp_name'];
            $jsonData = file_get_contents($jsonFile);

            if ($jsonData === false) {
                showMessage('Tidak dapat membaca file JSON.', true);
            } else {
                // Inisialisasi utilitas database
                $dbUtils = new DatabaseUtilities();

                // Import dataset
                $result = $dbUtils->importDatasetFromJson($jsonData);

                if ($result['status'] === 'success') {
                    showMessage($result['message'] . '. <a href="index.php?tab=results&dataset=' . $result['dataset_id'] . '">Lihat hasil</a>');
                } else {
                    showMessage($result['message'], true);
                }
            }
        }
    }
}

// Tampilkan form
echo '
        <p>Upload file JSON yang berisi data dataset untuk diimpor.</p>
        
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="json_file">Pilih File JSON:</label>
                <input type="file" name="json_file" id="json_file" accept=".json" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Import Dataset</button>
                <a href="index.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
        
        <div class="import-notes">
            <h3>Catatan Format JSON</h3>
            <p>File JSON harus memiliki struktur berikut:</p>
            <pre>
{
  "dataset": {
    "name": "Nama Dataset",
    "accuracy": 80.5
  },
  "raw_data": [
    {
      "data_id": "1",
      "actual_class": "positive",
      "predicted_class": "positive"
    },
    ...
  ],
  "confusion_matrix": [
    {
      "class_name": "positive",
      "true_positive": 8,
      "false_positive": 3,
      "true_negative": 7,
      "false_negative": 2,
      "precision": 0.727,
      "recall": 0.8,
      "f1_score": 0.762
    },
    ...
  ]
}
            </pre>
        </div>
    </div>
</body>
</html>';
