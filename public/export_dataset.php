<?php

/**
 * File untuk mengekspor dataset dari Sistem Perhitungan Confusion Matrix SPK
 */

// Muat utilitas database
require_once __DIR__ . '/../src/database_utilities.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Periksa apakah ID dataset dan format diberikan
if ((!isset($_GET['id']) && !isset($_POST['id'])) || (empty($_GET['id']) && empty($_POST['id']))) {
    die('
        <div class="error-message">
            <h2>Error</h2>
            <p>ID dataset tidak ditemukan. Silakan pilih dataset dari daftar.</p>
            <a href="index.php?tab=history" class="btn">Kembali ke Riwayat</a>
        </div>
    ');
}

$datasetId = isset($_POST['id']) ? intval($_POST['id']) : intval($_GET['id']);
$format = isset($_POST['format']) ? $_POST['format'] : (isset($_GET['format']) ? $_GET['format'] : 'json');

// Ambil data urutan dari POST jika ada
$orderedData = null;
if (isset($_POST['ordered_data']) && !empty($_POST['ordered_data'])) {
    $orderedData = json_decode($_POST['ordered_data'], true);
}

// Inisialisasi utilitas database
$dbUtils = new DatabaseUtilities();

// Ekspor berdasarkan format yang diminta
if ($format === 'json') {
    // Ekspor ke JSON
    $jsonData = $dbUtils->exportDatasetToJson($datasetId);

    // Set header untuk download
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="dataset_' . $datasetId . '.json"');
    header('Content-Length: ' . strlen($jsonData));

    // Output data
    echo $jsonData;
    exit;
} elseif ($format === 'sql') {
    // Ekspor ke SQL
    $sqlData = $dbUtils->exportDatasetToSql($datasetId);

    // Set header untuk download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="dataset_' . $datasetId . '.sql"');
    header('Content-Length: ' . strlen($sqlData));

    // Output data
    echo $sqlData;
    exit;
} elseif ($format === 'csv') {
    // Ekspor ke CSV
    $csvData = $dbUtils->exportDatasetToCsv($datasetId);

    // Set header untuk download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="dataset_' . $datasetId . '.csv"');
    header('Content-Length: ' . strlen($csvData));

    // Output data
    echo $csvData;
    exit;
} elseif ($format === 'excel') {
    // Ekspor ke Excel
    try {
        // Dapatkan spreadsheet
        $spreadsheet = $dbUtils->exportDatasetToExcel($datasetId, $orderedData);

        // Buat writer
        $writer = new Xlsx($spreadsheet);

        // Set header untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="dataset_' . $datasetId . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Simpan ke output
        $writer->save('php://output');
        exit;
    } catch (Exception $e) {
        die('
            <div class="error-message">
                <h2>Error</h2>
                <p>Gagal mengekspor dataset ke Excel: ' . $e->getMessage() . '</p>
                <a href="index.php?tab=history" class="btn">Kembali ke Riwayat</a>
            </div>
        ');
    }
} else {
    die('
        <div class="error-message">
            <h2>Error</h2>
            <p>Format ekspor tidak didukung. Format yang didukung: json, sql, csv, excel.</p>
            <a href="index.php?tab=history" class="btn">Kembali ke Riwayat</a>
        </div>
    ');
}
