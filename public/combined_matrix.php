<?php

/**
 * File untuk menghitung dan menampilkan confusion matrix gabungan
 * dari beberapa dataset yang dipilih
 */

// Muat konfigurasi database dan kelas confusion matrix
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/confusion_matrix.php';

// Inisialisasi database
$db = new Database();

// Ambil semua dataset
$datasets = $db->getDatasets();

// Inisialisasi variabel untuk menyimpan data gabungan
$combinedData = [];
$selectedDatasets = [];

// Proses form jika ada dataset yang dipilih
if (isset($_POST['calculate']) && isset($_POST['datasets']) && !empty($_POST['datasets'])) {
    $selectedDatasetIds = $_POST['datasets'];

    // Ambil data dari setiap dataset yang dipilih
    foreach ($selectedDatasetIds as $datasetId) {
        $dataset = $db->getDataset($datasetId);
        if ($dataset) {
            $selectedDatasets[] = $dataset;
            $rawData = $db->getRawData($datasetId);

            // Gabungkan data mentah
            foreach ($rawData as $row) {
                $combinedData[] = [
                    'actual' => $row['kelayakan_aktual'],
                    'predicted' => $row['kelayakan_sistem']
                ];
            }
        }
    }

    // Hitung confusion matrix gabungan jika ada data
    if (!empty($combinedData)) {
        // Temukan semua kelas unik
        $uniqueClasses = [];
        foreach ($combinedData as $item) {
            if (!in_array($item['actual'], $uniqueClasses)) {
                $uniqueClasses[] = $item['actual'];
            }
            if (!in_array($item['predicted'], $uniqueClasses)) {
                $uniqueClasses[] = $item['predicted'];
            }
        }

        // Hitung confusion matrix
        $confusionMatrix = new ConfusionMatrix();
        $confusionMatrix->calculateConfusionMatrix($combinedData, $uniqueClasses);
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confusion Matrix Gabungan - Sistem Perhitungan Confusion Matrix SPK</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Sistem Perhitungan Confusion Matrix SPK</h1>
            <p>Perhitungan Confusion Matrix Gabungan dari Beberapa Dataset</p>
        </header>

        <nav>
            <ul class="tabs">
                <li>
                    <a href="index.php?tab=upload">Upload Data</a>
                </li>
                <li>
                    <a href="index.php?tab=history">Riwayat</a>
                </li>
                <li class="active">
                    <a href="combined_matrix.php">Matrix Gabungan</a>
                </li>
            </ul>
        </nav>

        <main>
            <section>
                <h2>Confusion Matrix Gabungan</h2>

                <div class="dataset-selection">
                    <h3>Pilih Dataset untuk Digabungkan</h3>

                    <?php if (empty($datasets)) : ?>
                        <p>Belum ada dataset yang tersedia. Silakan upload data terlebih dahulu.</p>
                    <?php else : ?>
                        <form action="" method="post">
                            <div class="dataset-list">
                                <?php foreach ($datasets as $dataset) : ?>
                                    <div class="dataset-item">
                                        <label>
                                            <input type="checkbox" name="datasets[]" value="<?php echo $dataset['id']; ?>"
                                                <?php echo (isset($_POST['datasets']) && in_array($dataset['id'], $_POST['datasets'])) ? 'checked' : ''; ?>>
                                            <span><?php echo htmlspecialchars($dataset['name']); ?>
                                                <small>(Akurasi: <?php echo number_format($dataset['accuracy'], 2); ?>%,
                                                    Dibuat: <?php echo $dataset['created_at']; ?>)</small>
                                            </span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="select-actions">
                                <button type="button" id="select-all" style="background-color: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 16px; transition: background-color 0.3s ease;">Pilih Semua</button>
                                <button type="button" id="deselect-all" style="background-color: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 16px; transition: background-color 0.3s ease;">Batalkan Semua</button>
                                <button type="submit" name="calculate" class="btn" style="background-color: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 16px; transition: background-color 0.3s ease;">Hitung Confusion Matrix Gabungan</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (isset($confusionMatrix) && !empty($combinedData)) : ?>
                    <div class="combined-info">
                        <h3>Informasi Dataset Gabungan</h3>
                        <ul>
                            <li><strong>Jumlah Dataset yang Digabung:</strong> <?php echo count($selectedDatasets); ?></li>
                            <li><strong>Total Data:</strong> <?php echo count($combinedData); ?></li>
                            <li><strong>Dataset yang Digabung:</strong>
                                <ul>
                                    <?php foreach ($selectedDatasets as $dataset) : ?>
                                        <li><?php echo htmlspecialchars($dataset['name']); ?> (ID: <?php echo $dataset['id']; ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <li><strong>Akurasi Gabungan:</strong> <?php echo number_format($confusionMatrix->getAccuracy() * 100, 2); ?>%</li>
                        </ul>
                    </div>

                    <!-- Tampilkan confusion matrix -->
                    <div class="confusion-matrix-visualization">
                        <?php echo $confusionMatrix->getHtmlMatrix(); ?>
                    </div>

                    <!-- Tampilkan metrik -->
                    <div class="metrics-container">
                        <?php echo $confusionMatrix->getHtmlMetricsLayak(); ?>
                    </div>

                    <!-- Penjelasan cara perhitungan -->
                    <div class="explanation">
                        <h3>Cara Penghitungan Confusion Matrix (Kelas Layak)</h3>
                        <ul>
                            <li><b>TP (True Positive)</b>: Data <b>aktual</b> dan <b>prediksi</b> sama-sama <b>LAYAK</b>.</li>
                            <li><b>FP (False Positive)</b>: Data <b>aktual</b> TIDAK LAYAK, tapi <b>prediksi</b> LAYAK.</li>
                            <li><b>FN (False Negative)</b>: Data <b>aktual</b> LAYAK, tapi <b>prediksi</b> TIDAK LAYAK.</li>
                            <li><b>TN (True Negative)</b>: Data <b>aktual</b> dan <b>prediksi</b> sama-sama TIDAK LAYAK.</li>
                        </ul>

                        <h3>Cara Penghitungan Precision, Recall, dan F1-Score (Kelas Layak)</h3>
                        <ul>
                            <li><b>Precision</b> = TP / (TP + FP)<br>Menunjukkan seberapa banyak prediksi <b>LAYAK</b> yang benar-benar benar.</li>
                            <li><b>Recall</b> = TP / (TP + FN)<br>Menunjukkan seberapa banyak data <b>LAYAK aktual</b> yang berhasil diprediksi dengan benar.</li>
                            <li><b>F1-Score</b> = 2 × (Precision x Recall) / (Precision + Recall)<br>Rata-rata harmonik dari Precision dan Recall.</li>
                        </ul>

                        <?php if (isset($confusionMatrix) && !empty($combinedData)): ?>
                            <?php
                            // Cari kelas Layak
                            $layakFound = false;
                            foreach ($confusionMatrix->getConfusionMatrix() as $class => $row) {
                                if (strtolower($class) === 'layak') {
                                    $layakFound = true;
                                    $metrics = $confusionMatrix->getMetrics($class);
                                    break;
                                }
                            }
                            ?>
                            <?php if ($layakFound): ?>
                                <div class="calculation-example">
                                    <h3>Contoh Perhitungan untuk Kelas Layak</h3>
                                    <p>
                                        <b>Precision</b> = <?php echo $metrics['tp']; ?> / (<?php echo $metrics['tp']; ?> + <?php echo $metrics['fp']; ?>) = <b><?php echo number_format($metrics['precision'] * 100, 2); ?>%</b>
                                    </p>
                                    <p>
                                        <b>Recall</b> = <?php echo $metrics['tp']; ?> / (<?php echo $metrics['tp']; ?> + <?php echo $metrics['fn']; ?>) = <b><?php echo number_format($metrics['recall'] * 100, 2); ?>%</b>
                                    </p>
                                    <p>
                                        <b>F1-Score</b> = 2 × (<?php echo number_format($metrics['precision'], 2); ?> × <?php echo number_format($metrics['recall'], 2); ?>) / (<?php echo number_format($metrics['precision'], 2); ?> + <?php echo number_format($metrics['recall'], 2); ?>) = <b><?php echo number_format($metrics['f1_score'] * 100, 2); ?>%</b>
                                    </p>
                                </div>

                                <div class="accuracy-container">
                                    <h3>Akurasi (Accuracy)</h3>
                                    <p><b>Rumus:</b> (TP + TN) / (TP + TN + FP + FN)</p>
                                    <p>
                                        <b>Contoh Perhitungan:</b><br>
                                        Jumlah prediksi benar = TP(Layak) + TN(Layak) = <?php echo $metrics['tp']; ?> + <?php echo $metrics['tn']; ?> = <?php echo $metrics['tp'] + $metrics['tn']; ?><br>
                                        Total data = <?php echo count($combinedData); ?><br>
                                        Akurasi = <?php echo $metrics['tp'] + $metrics['tn']; ?> / <?php echo count($combinedData); ?> = <b><?php echo number_format($confusionMatrix->getAccuracy() * 100, 2); ?>%</b>
                                    </p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Tombol aksi -->
                    <div class="action-buttons">
                        <a href="combined_matrix.php" class="btn btn-danger" id="reset-btn">Reset</a>
                    </div>
                <?php elseif (isset($_POST['calculate'])) : ?>
                    <div class="alert alert-warning">
                        <p>Tidak ada data yang tersedia untuk dataset yang dipilih atau tidak ada dataset yang dipilih.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> Sistem Perhitungan Confusion Matrix SPK. Versi 2.0</p>
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="../js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi DataTable untuk daftar dataset jika ada
            if (jQuery().DataTable) {
                $('.dataset-list').addClass('table-data');
            }

            // Konfirmasi sebelum reset
            const resetBtn = document.getElementById('reset-btn');
            if (resetBtn) {
                resetBtn.addEventListener('click', function(e) {
                    if (!confirm('Apakah Anda yakin ingin mereset? Semua pilihan dataset dan hasil perhitungan akan dihapus.')) {
                        e.preventDefault();
                    }
                });
            }

            // Konfirmasi sebelum meninggalkan halaman jika ada dataset yang dipilih
            const checkboxes = document.querySelectorAll('input[name="datasets[]"]');
            if (checkboxes.length > 0) {
                let formChanged = false;

                // Deteksi perubahan pada checkbox
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        formChanged = true;
                    });
                });

                // Tampilkan konfirmasi ketika pengguna mencoba meninggalkan halaman
                window.addEventListener('beforeunload', function(e) {
                    if (formChanged) {
                        // Pesan standar akan ditampilkan oleh browser
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });

                // Reset flag ketika form disubmit
                const form = document.querySelector('form');
                if (form) {
                    form.addEventListener('submit', function() {
                        formChanged = false;
                    });
                }
            }
        });
    </script>
</body>

</html>