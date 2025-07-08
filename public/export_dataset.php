<?php

/**
 * File untuk mengekspor dataset dari Sistem Perhitungan Confusion Matrix SPK
 */

// Muat utilitas database
require_once __DIR__ . '/../src/database_utilities.php';

// Periksa apakah ID dataset dan format diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('
        <div class="error-message">
            <h2>Error</h2>
            <p>ID dataset tidak ditemukan. Silakan pilih dataset dari daftar.</p>
            <a href="index.php?tab=history" class="btn">Kembali ke Riwayat</a>
        </div>
    ');
}

$datasetId = intval($_GET['id']);
$format = isset($_GET['format']) ? $_GET['format'] : 'json';

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
} else {
    die('
        <div class="error-message">
            <h2>Error</h2>
            <p>Format ekspor tidak didukung. Format yang didukung: json, sql, csv.</p>
            <a href="index.php?tab=history" class="btn">Kembali ke Riwayat</a>
        </div>
    ');
}
