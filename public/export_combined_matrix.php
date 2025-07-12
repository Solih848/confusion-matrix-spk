<?php

/**
 * File untuk mengekspor confusion matrix gabungan ke Excel
 */

// Muat konfigurasi database dan kelas confusion matrix
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/confusion_matrix.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Inisialisasi database
$db = new Database();

// Periksa apakah ada dataset yang dipilih
if (!isset($_POST['datasets']) || empty($_POST['datasets'])) {
    die('
        <div class="error-message">
            <h2>Error</h2>
            <p>Tidak ada dataset yang dipilih. Silakan pilih dataset terlebih dahulu.</p>
            <a href="combined_matrix.php" class="btn">Kembali ke Matrix Gabungan</a>
        </div>
    ');
}

// Ambil dataset yang dipilih
$selectedDatasetIds = $_POST['datasets'];
$selectedDatasets = [];
$combinedData = [];

// Ambil data dari setiap dataset yang dipilih
foreach ($selectedDatasetIds as $datasetId) {
    $dataset = $db->getDataset($datasetId);
    if ($dataset) {
        $selectedDatasets[] = $dataset;
        $rawData = $db->getRawData($datasetId);

        // Gabungkan data mentah
        foreach ($rawData as $row) {
            $combinedData[] = [
                'data_id' => $row['data_id'],
                'nama_alternatif' => $row['nama_alternatif'],
                'nilai_vektor_v' => $row['nilai_vektor_v'],
                'actual' => $row['kelayakan_aktual'],
                'predicted' => $row['kelayakan_sistem']
            ];
        }
    }
}

// Hitung confusion matrix gabungan jika ada data
if (empty($combinedData)) {
    die('
        <div class="error-message">
            <h2>Error</h2>
            <p>Tidak ada data yang tersedia untuk dataset yang dipilih.</p>
            <a href="combined_matrix.php" class="btn">Kembali ke Matrix Gabungan</a>
        </div>
    ');
}

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

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();

// Sheet 1: Informasi Dataset Gabungan
$infoSheet = $spreadsheet->getActiveSheet();
$infoSheet->setTitle('Informasi Dataset');

// Judul
$infoSheet->setCellValue('A1', 'HASIL PERHITUNGAN CONFUSION MATRIX SPK GABUNGAN');
$infoSheet->mergeCells('A1:E1');
$infoSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$infoSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Informasi Dataset
$infoSheet->setCellValue('A3', 'Informasi Dataset Gabungan');
$infoSheet->getStyle('A3')->getFont()->setBold(true);

$infoSheet->setCellValue('A4', 'Jumlah Dataset yang Digabung:');
$infoSheet->setCellValue('B4', count($selectedDatasets));

$infoSheet->setCellValue('A5', 'Total Data:');
$infoSheet->setCellValue('B5', count($combinedData));

$infoSheet->setCellValue('A6', 'Akurasi Gabungan:');
$infoSheet->setCellValue('B6', number_format($confusionMatrix->getAccuracy() * 100, 2) . '%');

$infoSheet->setCellValue('A8', 'Dataset yang Digabung:');
$infoSheet->getStyle('A8')->getFont()->setBold(true);

$row = 9;
foreach ($selectedDatasets as $index => $dataset) {
    $infoSheet->setCellValue('A' . $row, ($index + 1) . '. ' . $dataset['name'] . ' (ID: ' . $dataset['id'] . ')');
    $infoSheet->setCellValue('B' . $row, 'Akurasi: ' . number_format($dataset['accuracy'], 2) . '%');
    $infoSheet->setCellValue('C' . $row, 'Dibuat: ' . $dataset['created_at']);
    $row++;
}

// Sheet 2: Data Alternatif
$dataSheet = $spreadsheet->createSheet();
$dataSheet->setTitle('Data Alternatif');

// Header
$dataSheet->setCellValue('A1', 'No');
$dataSheet->setCellValue('B1', 'Nama Alternatif');
$dataSheet->setCellValue('C1', 'Nilai Vektor V');
$dataSheet->setCellValue('D1', 'Kelayakan Sistem');
$dataSheet->setCellValue('E1', 'Kelayakan Aktual');
$dataSheet->setCellValue('F1', 'Status (Layak)');

$dataSheet->getStyle('A1:F1')->getFont()->setBold(true);
$dataSheet->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$dataSheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');

// Isi data
$row = 2;
foreach ($combinedData as $data) {
    $dataSheet->setCellValue('A' . $row, $data['data_id']);
    $dataSheet->setCellValue('B' . $row, $data['nama_alternatif']);
    $dataSheet->setCellValue('C' . $row, $data['nilai_vektor_v']);
    $dataSheet->setCellValue('D' . $row, $data['predicted']);
    $dataSheet->setCellValue('E' . $row, $data['actual']);

    // Status Confusion Matrix
    $status = '';
    if ($data['actual'] === 'Layak' && $data['predicted'] === 'Layak') {
        $status = 'TP';
        $dataSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FF27AE60'); // Hijau
    } elseif ($data['actual'] !== 'Layak' && $data['predicted'] === 'Layak') {
        $status = 'FP';
        $dataSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FFE67E22'); // Oranye
    } elseif ($data['actual'] === 'Layak' && $data['predicted'] !== 'Layak') {
        $status = 'FN';
        $dataSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FFE67E22'); // Oranye
    } elseif ($data['actual'] !== 'Layak' && $data['predicted'] !== 'Layak') {
        $status = 'TN';
        $dataSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FF95A5A6'); // Abu-abu
    }

    $dataSheet->setCellValue('F' . $row, $status);
    $dataSheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
}

// Auto-size kolom
foreach (range('A', 'F') as $col) {
    $dataSheet->getColumnDimension($col)->setAutoSize(true);
}

// Sheet 3: Confusion Matrix
$cmSheet = $spreadsheet->createSheet();
$cmSheet->setTitle('Confusion Matrix');

// Judul
$cmSheet->setCellValue('A1', 'CONFUSION MATRIX (KELAS LAYAK)');
$cmSheet->mergeCells('A1:D1');
$cmSheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
$cmSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Matrix header
$cmSheet->setCellValue('A3', '');
$cmSheet->setCellValue('B3', 'Aktual');
$cmSheet->mergeCells('B3:C3');
$cmSheet->getStyle('B3:C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$cmSheet->setCellValue('A4', 'Prediksi');
$cmSheet->setCellValue('B4', 'Layak');
$cmSheet->setCellValue('C4', 'Tidak Layak');

// Matrix data (FP dan FN sudah ditukar)
$layak = null;
foreach ($uniqueClasses as $class) {
    if (strtolower($class) === 'layak') {
        $metrics = $confusionMatrix->getMetrics($class);
        $layak = $metrics;
        break;
    }
}

if ($layak) {
    $cmSheet->setCellValue('A5', 'Layak');
    $cmSheet->setCellValue('B5', $layak['tp']);
    $cmSheet->setCellValue('C5', $layak['fp']);

    $cmSheet->setCellValue('A6', 'Tidak Layak');
    $cmSheet->setCellValue('B6', $layak['fn']);
    $cmSheet->setCellValue('C6', $layak['tn']);

    // Style matrix
    $cmSheet->getStyle('A3:C6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $cmSheet->getStyle('A4:A6')->getFont()->setBold(true);
    $cmSheet->getStyle('B4:C4')->getFont()->setBold(true);
    $cmSheet->getStyle('B5:B5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5F5E3'); // Hijau muda
    $cmSheet->getStyle('C6:C6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD5F5E3'); // Hijau muda
    $cmSheet->getStyle('B6:B6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFDEBD0'); // Oranye muda
    $cmSheet->getStyle('C5:C5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFDEBD0'); // Oranye muda

    // Metrik evaluasi
    $cmSheet->setCellValue('A8', 'METRIK EVALUASI (KELAS LAYAK)');
    $cmSheet->mergeCells('A8:H8');
    $cmSheet->getStyle('A8')->getFont()->setBold(true);
    $cmSheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $cmSheet->setCellValue('A9', 'Kelas');
    $cmSheet->setCellValue('B9', 'True Positive');
    $cmSheet->setCellValue('C9', 'False Positive');
    $cmSheet->setCellValue('D9', 'True Negative');
    $cmSheet->setCellValue('E9', 'False Negative');
    $cmSheet->setCellValue('F9', 'Precision');
    $cmSheet->setCellValue('G9', 'Recall');
    $cmSheet->setCellValue('H9', 'F1-Score');

    $cmSheet->setCellValue('A10', 'Layak');
    $cmSheet->setCellValue('B10', $layak['tp']);
    $cmSheet->setCellValue('C10', $layak['fp']);
    $cmSheet->setCellValue('D10', $layak['tn']);
    $cmSheet->setCellValue('E10', $layak['fn']);
    $cmSheet->setCellValue('F10', number_format($layak['precision'] * 100, 2) . '%');
    $cmSheet->setCellValue('G10', number_format($layak['recall'] * 100, 2) . '%');
    $cmSheet->setCellValue('H10', number_format($layak['f1_score'] * 100, 2) . '%');

    // Style metrik
    $cmSheet->getStyle('A9:H10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $cmSheet->getStyle('A9:H9')->getFont()->setBold(true);
    $cmSheet->getStyle('A9:H9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');

    // Akurasi
    $totalData = count($combinedData);
    $totalBenar = $layak['tp'] + $layak['tn'];
    $accuracy = ($totalData > 0) ? ($totalBenar / $totalData) : 0;

    $cmSheet->setCellValue('A12', 'AKURASI (ACCURACY)');
    $cmSheet->mergeCells('A12:D12');
    $cmSheet->getStyle('A12')->getFont()->setBold(true);

    $cmSheet->setCellValue('A13', 'Rumus:');
    $cmSheet->setCellValue('B13', '(TP + TN) / (TP + TN + FP + FN)');

    $cmSheet->setCellValue('A14', 'Perhitungan:');
    $cmSheet->setCellValue('B14', "({$layak['tp']} + {$layak['tn']}) / {$totalData} = " . number_format($accuracy * 100, 2) . '%');

    // Auto-size kolom
    foreach (range('A', 'H') as $col) {
        $cmSheet->getColumnDimension($col)->setAutoSize(true);
    }
}

// Buat sheet untuk setiap dataset yang dipilih
foreach ($selectedDatasets as $index => $dataset) {
    $datasetId = $dataset['id'];
    $rawData = $db->getRawData($datasetId);

    if (!empty($rawData)) {
        // Buat sheet baru
        $dsSheet = $spreadsheet->createSheet();
        $dsSheet->setTitle('Dataset ' . ($index + 1));

        // Judul
        $dsSheet->setCellValue('A1', 'DATASET: ' . $dataset['name']);
        $dsSheet->mergeCells('A1:F1');
        $dsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $dsSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Informasi dataset
        $dsSheet->setCellValue('A3', 'ID Dataset:');
        $dsSheet->setCellValue('B3', $dataset['id']);

        $dsSheet->setCellValue('A4', 'Nama Dataset:');
        $dsSheet->setCellValue('B4', $dataset['name']);

        $dsSheet->setCellValue('A5', 'Waktu Dibuat:');
        $dsSheet->setCellValue('B5', $dataset['created_at']);

        $dsSheet->setCellValue('A6', 'Akurasi:');
        $dsSheet->setCellValue('B6', number_format($dataset['accuracy'], 2) . '%');

        $dsSheet->setCellValue('A7', 'Jumlah Data:');
        $dsSheet->setCellValue('B7', count($rawData));

        // Header data
        $dsSheet->setCellValue('A9', 'No');
        $dsSheet->setCellValue('B9', 'Nama Alternatif');
        $dsSheet->setCellValue('C9', 'Nilai Vektor V');
        $dsSheet->setCellValue('D9', 'Kelayakan Sistem');
        $dsSheet->setCellValue('E9', 'Kelayakan Aktual');
        $dsSheet->setCellValue('F9', 'Status (Layak)');

        $dsSheet->getStyle('A9:F9')->getFont()->setBold(true);
        $dsSheet->getStyle('A9:F9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $dsSheet->getStyle('A9:F9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');

        // Isi data
        $row = 10;
        foreach ($rawData as $data) {
            $dsSheet->setCellValue('A' . $row, $data['data_id']);
            $dsSheet->setCellValue('B' . $row, $data['nama_alternatif']);
            $dsSheet->setCellValue('C' . $row, $data['nilai_vektor_v']);
            $dsSheet->setCellValue('D' . $row, $data['kelayakan_sistem']);
            $dsSheet->setCellValue('E' . $row, $data['kelayakan_aktual']);

            // Status Confusion Matrix
            $status = '';
            if ($data['kelayakan_aktual'] === 'Layak' && $data['kelayakan_sistem'] === 'Layak') {
                $status = 'TP';
                $dsSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FF27AE60'); // Hijau
            } elseif ($data['kelayakan_aktual'] !== 'Layak' && $data['kelayakan_sistem'] === 'Layak') {
                $status = 'FP';
                $dsSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FFE67E22'); // Oranye
            } elseif ($data['kelayakan_aktual'] === 'Layak' && $data['kelayakan_sistem'] !== 'Layak') {
                $status = 'FN';
                $dsSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FFE67E22'); // Oranye
            } elseif ($data['kelayakan_aktual'] !== 'Layak' && $data['kelayakan_sistem'] !== 'Layak') {
                $status = 'TN';
                $dsSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FF95A5A6'); // Abu-abu
            }

            $dsSheet->setCellValue('F' . $row, $status);
            $dsSheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Auto-size kolom
        foreach (range('A', 'F') as $col) {
            $dsSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Hitung confusion matrix untuk dataset ini
        $datasetData = [];
        foreach ($rawData as $dataRow) {
            $datasetData[] = [
                'actual' => $dataRow['kelayakan_aktual'],
                'predicted' => $dataRow['kelayakan_sistem']
            ];
        }

        // Temukan kelas unik dalam dataset ini
        $datasetClasses = [];
        foreach ($datasetData as $item) {
            if (!in_array($item['actual'], $datasetClasses)) {
                $datasetClasses[] = $item['actual'];
            }
            if (!in_array($item['predicted'], $datasetClasses)) {
                $datasetClasses[] = $item['predicted'];
            }
        }

        // Hitung confusion matrix untuk dataset ini
        $datasetCM = new ConfusionMatrix();
        $datasetCM->calculateConfusionMatrix($datasetData, $datasetClasses);

        // Tambahkan confusion matrix untuk dataset ini
        $dsSheet->setCellValue('A' . ($row + 2), 'CONFUSION MATRIX (KELAS LAYAK)');
        $dsSheet->mergeCells('A' . ($row + 2) . ':D' . ($row + 2));
        $dsSheet->getStyle('A' . ($row + 2))->getFont()->setBold(true);
        $dsSheet->getStyle('A' . ($row + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Matrix header
        $dsSheet->setCellValue('A' . ($row + 4), '');
        $dsSheet->setCellValue('B' . ($row + 4), 'Aktual');
        $dsSheet->mergeCells('B' . ($row + 4) . ':C' . ($row + 4));
        $dsSheet->getStyle('B' . ($row + 4) . ':C' . ($row + 4))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $dsSheet->setCellValue('A' . ($row + 5), 'Prediksi');
        $dsSheet->setCellValue('B' . ($row + 5), 'Layak');
        $dsSheet->setCellValue('C' . ($row + 5), 'Tidak Layak');

        // Matrix data untuk dataset ini
        $datasetLayak = null;
        foreach ($datasetClasses as $class) {
            if (strtolower($class) === 'layak') {
                $datasetMetrics = $datasetCM->getMetrics($class);
                $datasetLayak = $datasetMetrics;
                break;
            }
        }

        if ($datasetLayak) {
            $dsSheet->setCellValue('A' . ($row + 6), 'Layak');
            $dsSheet->setCellValue('B' . ($row + 6), $datasetLayak['tp']);
            $dsSheet->setCellValue('C' . ($row + 6), $datasetLayak['fp']);

            $dsSheet->setCellValue('A' . ($row + 7), 'Tidak Layak');
            $dsSheet->setCellValue('B' . ($row + 7), $datasetLayak['fn']);
            $dsSheet->setCellValue('C' . ($row + 7), $datasetLayak['tn']);

            // Style matrix
            $dsSheet->getStyle('A' . ($row + 4) . ':C' . ($row + 7))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $dsSheet->getStyle('A' . ($row + 5) . ':A' . ($row + 7))->getFont()->setBold(true);
            $dsSheet->getStyle('B' . ($row + 5) . ':C' . ($row + 5))->getFont()->setBold(true);

            // Metrik evaluasi
            $dsSheet->setCellValue('A' . ($row + 9), 'METRIK EVALUASI (KELAS LAYAK)');
            $dsSheet->mergeCells('A' . ($row + 9) . ':B' . ($row + 9));
            $dsSheet->getStyle('A' . ($row + 9))->getFont()->setBold(true);
            $dsSheet->getStyle('A' . ($row + 9))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $dsSheet->setCellValue('A' . ($row + 10), 'Precision');
            $dsSheet->setCellValue('B' . ($row + 10), number_format($datasetLayak['precision'] * 100, 2) . '%');

            $dsSheet->setCellValue('A' . ($row + 11), 'Recall');
            $dsSheet->setCellValue('B' . ($row + 11), number_format($datasetLayak['recall'] * 100, 2) . '%');

            $dsSheet->setCellValue('A' . ($row + 12), 'F1-Score');
            $dsSheet->setCellValue('B' . ($row + 12), number_format($datasetLayak['f1_score'] * 100, 2) . '%');

            $dsSheet->setCellValue('A' . ($row + 13), 'Akurasi');
            $dsSheet->setCellValue('B' . ($row + 13), number_format($datasetCM->getAccuracy() * 100, 2) . '%');
        }
    }
}

// Kembali ke sheet pertama
$spreadsheet->setActiveSheetIndex(0);

// Simpan ke file Excel dan download
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="confusion_matrix_gabungan.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
