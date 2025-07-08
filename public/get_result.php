<?php

/**
 * File untuk mendapatkan dan menampilkan hasil confusion matrix
 * dari sistem SPK yang dipilih
 */

// Muat konfigurasi database
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/confusion_matrix.php';

// Inisialisasi database
$db = new Database();

// Periksa apakah ID dataset diberikan
if (!isset($_GET['dataset']) || empty($_GET['dataset'])) {
    die('
        <div class="error-message">
            <h2>Error</h2>
            <p>ID dataset tidak ditemukan. Silakan pilih dataset dari daftar.</p>
            <a href="index.php" class="btn">Kembali ke Beranda</a>
        </div>
    ');
}

$datasetId = intval($_GET['dataset']);

// Ambil data dataset
$dataset = $db->getDataset($datasetId);
if (!$dataset) {
    die('
        <div class="error-message">
            <h2>Error</h2>
            <p>Dataset tidak ditemukan. Silakan pilih dataset dari daftar.</p>
            <a href="index.php" class="btn">Kembali ke Beranda</a>
        </div>
    ');
}

// Ambil hasil confusion matrix
$confusionMatrixData = $db->getConfusionMatrix($datasetId);

// Ambil data mentah
$rawData = $db->getRawData($datasetId);

// Format data untuk ditampilkan
$confusionMatrix = [];
if (isset($confusionMatrixData)) {
    foreach ($confusionMatrixData as $row) {
        if ($row['class_name'] === 'Layak') {
            $confusionMatrix['Layak'] = [
                'tp' => $row['true_positive'],
                'fp' => $row['false_positive'],
                'tn' => $row['true_negative'],
                'fn' => $row['false_negative'],
                'precision' => $row['precision_val'],
                'recall' => $row['recall_val'],
                'f1_score' => $row['f1_score'],
            ];
        }
    }
}

// Buat breakdown confusion matrix per data
function getCMStatus($actual, $predicted)
{
    if ($actual === 'Layak' && $predicted === 'Layak') return 'TP';
    if ($actual !== 'Layak' && $predicted === 'Layak') return 'FP';
    if ($actual === 'Layak' && $predicted !== 'Layak') return 'FN';
    if ($actual !== 'Layak' && $predicted !== 'Layak') return 'TN';
    return '';
}

// Tampilkan hasil
echo '
<div class="results-container">
    <h2>Hasil Perhitungan Confusion Matrix SPK</h2>
    <div class="dataset-info">
        <h3>Informasi Dataset</h3>
        <p><strong>Nama Dataset:</strong> ' . htmlspecialchars($dataset['name']) . '</p>
        <p><strong>Waktu Dibuat:</strong> ' . htmlspecialchars($dataset['created_at']) . '</p>
        <p><strong>Akurasi:</strong> ' . number_format($dataset['accuracy'], 2) . '%</p>
    </div>';

// Tampilkan tabel data mentah dengan status perhitungan confusion matrix
// Hanya untuk kelas Layak
$kelasCM = ['Layak'];
echo '<div class="raw-data-container">';
echo '<h3>Data Alternatif dan Status Confusion Matrix</h3>';
echo '<table class="raw-data-table">';
echo '<thead><tr><th>No</th><th>Nama Alternatif</th><th>Nilai Vektor V</th><th>Kelayakan Sistem</th><th>Kelayakan Aktual</th><th>Status (Layak)</th></tr></thead><tbody>';
foreach ($rawData as $row) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['data_id']) . '</td>';
    echo '<td>' . htmlspecialchars($row['nama_alternatif']) . '</td>';
    echo '<td>' . htmlspecialchars($row['nilai_vektor_v']) . '</td>';
    echo '<td>' . htmlspecialchars($row['kelayakan_sistem']) . '</td>';
    echo '<td>' . htmlspecialchars($row['kelayakan_aktual']) . '</td>';
    $status = getCMStatus($row['kelayakan_aktual'], $row['kelayakan_sistem']);
    $color = ($status === 'TP') ? '#27ae60' : (($status === 'FP' || $status === 'FN') ? '#e67e22' : '#bdc3c7');
    echo '<td style="text-align:center; color:' . $color . '"><b>' . $status . '</b></td>';
    echo '</tr>';
}
echo '</tbody></table>';
echo '</div>';

// Penjelasan cara perhitungan confusion matrix
// Hanya untuk kelas Layak
echo '<div class="explanation">';
echo '<h3>Cara Penghitungan Confusion Matrix (Kelas Layak)</h3>';
echo '<ul>';
echo '<li><b>TP (True Positive)</b>: Data <b>aktual</b> dan <b>prediksi</b> sama-sama <b>LAYAK</b>.</li>';
echo '<li><b>FP (False Positive)</b>: Data <b>aktual</b> TIDAK LAYAK, tapi <b>prediksi</b> LAYAK.</li>';
echo '<li><b>FN (False Negative)</b>: Data <b>aktual</b> LAYAK, tapi <b>prediksi</b> TIDAK LAYAK.</li>';
echo '<li><b>TN (True Negative)</b>: Data <b>aktual</b> dan <b>prediksi</b> sama-sama TIDAK LAYAK.</li>';
echo '</ul>';

// Penjelasan Precision, Recall, F1-Score
echo '<h3>Cara Penghitungan Precision, Recall, dan F1-Score (Kelas Layak)</h3>';
echo '<ul>';
echo '<li><b>Precision</b> = TP / (TP + FP)<br>Menunjukkan seberapa banyak prediksi <b>LAYAK</b> yang benar-benar benar.</li>';
echo '<li><b>Recall</b> = TP / (TP + FN)<br>Menunjukkan seberapa banyak data <b>LAYAK aktual</b> yang berhasil diprediksi dengan benar.</li>';
echo '<li><b>F1-Score</b> = 2 x (Precision x Recall) / (Precision + Recall)<br>Rata-rata harmonik dari Precision dan Recall.</li>';
echo '</ul>';

// Contoh perhitungan aktual dari data untuk kelas Layak saja
if (isset($confusionMatrix['Layak'])) {
    $metrics = $confusionMatrix['Layak'];
    echo '<div style="margin-bottom:10px;">';
    echo '<b>Contoh untuk kelas: Layak</b><br>';
    echo 'TP = ' . $metrics['tp'] . ', FP = ' . $metrics['fp'] . ', FN = ' . $metrics['fn'] . '<br>';
    echo 'Precision = ' . $metrics['tp'] . ' / (' . $metrics['tp'] . ' + ' . $metrics['fp'] . ') = <b>' . number_format($metrics['precision'] * 100, 2) . '%</b><br>';
    echo 'Recall = ' . $metrics['tp'] . ' / (' . $metrics['tp'] . ' + ' . $metrics['fn'] . ') = <b>' . number_format($metrics['recall'] * 100, 2) . '%</b><br>';
    echo 'F1-Score = 2 x (' . number_format($metrics['precision'], 2) . ' x ' . number_format($metrics['recall'], 2) . ') / (' . number_format($metrics['precision'], 2) . ' + ' . number_format($metrics['recall'], 2) . ') = <b>' . number_format($metrics['f1_score'] * 100, 2) . '%</b>';
    echo '</div>';
}
echo '</div>';

// Tampilkan confusion matrix hanya untuk kelas Layak
echo '<div class="confusion-matrix-visualization">';
echo '<h3>Confusion Matrix (Kelas Layak)</h3>';
echo '<table class="confusion-matrix">';
echo '<tr><th></th><th colspan="2">Prediksi</th></tr>';
echo '<tr><th>Aktual</th><th>Layak</th><th>Tidak Layak</th></tr>';
$layak = isset($confusionMatrix['Layak']) ? $confusionMatrix['Layak'] : ['tp' => 0, 'fn' => 0];
echo '<tr>';
echo '<th>Layak</th>';
echo '<td class="correct">' . $layak['tp'] . '</td>';
echo '<td class="incorrect">' . $layak['fn'] . '</td>';
echo '</tr>';
echo '<tr>';
echo '<th>Tidak Layak</th>';
echo '<td class="incorrect">' . $layak['fp'] . '</td>';
echo '<td class="correct">' . $layak['tn'] . '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';

echo '<div class="metrics-container">';
echo '<h3>Metrik Evaluasi (Kelas Layak)</h3>';
echo '<table class="metrics-table">';
echo '<tr><th>Kelas</th><th>True Positive</th><th>False Positive</th><th>True Negative</th><th>False Negative</th><th>Precision</th><th>Recall</th><th>F1-Score</th></tr>';
if (isset($confusionMatrix['Layak'])) {
    $metrics = $confusionMatrix['Layak'];
    echo '<tr>';
    echo '<th>Layak</th>';
    echo '<td>' . $metrics['tp'] . '</td>';
    echo '<td>' . $metrics['fp'] . '</td>';
    echo '<td>' . $metrics['tn'] . '</td>';
    echo '<td>' . $metrics['fn'] . '</td>';
    echo '<td>' . number_format($metrics['precision'] * 100, 2) . '%</td>';
    echo '<td>' . number_format($metrics['recall'] * 100, 2) . '%</td>';
    echo '<td>' . number_format($metrics['f1_score'] * 100, 2) . '%</td>';
    echo '</tr>';
}
echo '</table>';
echo '</div>';

// Akurasi hanya berdasarkan prediksi benar kelas Layak
$totalData = count($rawData);
$totalBenar = isset($confusionMatrix['Layak']) ? $confusionMatrix['Layak']['tp'] + $confusionMatrix['Layak']['tn'] : 0;
$accuracy = ($totalData > 0) ? ($totalBenar / $totalData) : 0;
echo '<div class="accuracy-container">';
echo '<h3>Akurasi (Accuracy)</h3>';
echo '<p><b>Rumus:</b> (TP + TN) / (TP + TN + FP + FN)</p>';
echo '<p><b>Contoh Perhitungan:</b><br>';
echo 'Jumlah prediksi benar = TP(Layak) + TN(Layak) = ' . (isset($confusionMatrix['Layak']) ? $confusionMatrix['Layak']['tp'] : 0) . ' + ' . (isset($confusionMatrix['Layak']) ? $confusionMatrix['Layak']['tn'] : 0) . ' = ' . $totalBenar . '<br>';
echo 'Total data = ' . $totalData . '<br>';
echo 'Akurasi = ' . $totalBenar . ' / ' . $totalData . ' = <b>' . number_format($accuracy * 100, 2) . '%</b>';
echo '</p>';
echo '</div>';

// Tampilkan tombol aksi
echo '
    <div class="action-buttons">
        <a href="export_dataset.php?id=' . $datasetId . '&format=json" class="btn">Export JSON</a>
        <a href="edit_data.php?dataset=' . $datasetId . '" class="btn btn-primary">Edit Data</a>
        <a href="index.php" class="btn btn-secondary">Kembali ke Beranda</a>
    </div>
</div>';
