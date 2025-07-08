<?php

/**
 * File untuk memproses data CSV SPK dan menghitung confusion matrix
 */

// Muat konfigurasi database
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/confusion_matrix.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['dataset_name']) || !isset($_POST['data'])) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Data tidak lengkap.'
    ]));
}

// Inisialisasi database dan confusion matrix
$db = new Database();
$cm = new ConfusionMatrix();

// Simpan dataset
$datasetName = $_POST['dataset_name'];
$datasetId = $db->saveDataset($datasetName);

$dataInput = $_POST['data'];
$data = [];
$classes = ['Layak', 'Tidak Layak']; // Kelas yang digunakan dalam SPK

foreach ($dataInput as $row) {
    $db->saveRawDataSpk($datasetId, [
        $row['id'],
        $row['nama_alternatif'],
        $row['nilai_vektor_v'],
        $row['kelayakan_sistem'],
        $row['kelayakan_aktual']
    ]);
    $data[] = [
        'actual' => $row['kelayakan_aktual'],
        'predicted' => $row['kelayakan_sistem']
    ];
}

// Hitung confusion matrix
$cm->calculateConfusionMatrix($data, $classes);

// Simpan hasil confusion matrix ke database
$overallAccuracy = 0;
$totalInstances = count($data);
$totalCorrect = 0;

foreach ($classes as $class) {
    $metrics = $cm->getMetrics($class);
    $db->saveConfusionMatrix($datasetId, $class, $metrics);

    // Hitung total instance yang diprediksi dengan benar
    $totalCorrect += $metrics['tp'];
}

// Hitung akurasi keseluruhan
if ($totalInstances > 0) {
    $overallAccuracy = ($totalCorrect / $totalInstances) * 100;
}

// Perbarui akurasi dataset
$db->updateAccuracy($datasetId, $overallAccuracy);

// Redirect ke halaman hasil
header('Location: index.php?tab=results&dataset=' . $datasetId);
exit;
