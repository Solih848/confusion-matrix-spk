<?php

/**
 * File database_utilities.php untuk operasi database lanjutan
 * seperti export dan import data untuk Sistem Penghitungan Confusion Matrix
 */

// Muat konfigurasi database
require_once 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Kelas DatabaseUtilities untuk operasi database lanjutan
 */
class DatabaseUtilities
{
    private $db;

    /**
     * Konstruktor - Inisialisasi koneksi database
     */
    public function __construct()
    {
        try {
            $this->db = new Database();
        } catch (PDOException $e) {
            die('Error connecting to database: ' . $e->getMessage());
        }
    }

    /**
     * Export data dataset ke format JSON
     * 
     * @param int $datasetId ID dataset
     * @return string Data JSON
     */
    public function exportDatasetToJson($datasetId)
    {
        // Validasi parameter
        $datasetId = intval($datasetId);
        if ($datasetId <= 0) {
            return json_encode(['error' => 'ID dataset tidak valid']);
        }

        // Ambil data dataset
        $dataset = $this->db->getDataset($datasetId);
        if (!$dataset) {
            return json_encode(['error' => 'Dataset tidak ditemukan']);
        }

        // Ambil data confusion matrix
        $confusionMatrix = $this->db->getConfusionMatrix($datasetId);

        // Transformasi nama kolom dari database ke format yang sesuai dengan kelas ConfusionMatrix
        foreach ($confusionMatrix as &$matrix) {
            // Ubah precision_val menjadi precision dan recall_val menjadi recall
            $matrix['precision'] = $matrix['precision_val'];
            $matrix['recall'] = $matrix['recall_val'];
            // Hapus kolom asli untuk menghindari duplikasi
            unset($matrix['precision_val']);
            unset($matrix['recall_val']);
        }

        // Ambil data mentah
        $rawData = $this->db->getRawData($datasetId);

        // Gabungkan semua data
        $exportData = [
            'dataset' => $dataset,
            'confusion_matrix' => $confusionMatrix,
            'raw_data' => $rawData
        ];

        // Kembalikan data dalam format JSON
        return json_encode($exportData, JSON_PRETTY_PRINT);
    }

    /**
     * Export data dataset ke format SQL
     * 
     * @param int $datasetId ID dataset
     * @return string Data SQL
     */
    public function exportDatasetToSql($datasetId)
    {
        // Validasi parameter
        $datasetId = intval($datasetId);
        if ($datasetId <= 0) {
            return "-- Error: ID dataset tidak valid";
        }

        // Ambil data dataset
        $dataset = $this->db->getDataset($datasetId);
        if (!$dataset) {
            return "-- Error: Dataset tidak ditemukan";
        }

        // Ambil data confusion matrix
        $confusionMatrix = $this->db->getConfusionMatrix($datasetId);

        // Ambil data mentah
        $rawData = $this->db->getRawData($datasetId);

        // Buat SQL untuk dataset
        $sql = "-- SQL Export untuk Dataset: " . $dataset['name'] . "\n";
        $sql .= "-- Tanggal Export: " . date('Y-m-d H:i:s') . "\n\n";

        $sql .= "-- Masukkan dataset\n";
        $sql .= "INSERT INTO datasets (name, accuracy, created_at) VALUES (";
        $sql .= "'" . addslashes($dataset['name']) . "', ";
        $sql .= ($dataset['accuracy'] !== null ? $dataset['accuracy'] : "NULL") . ", ";
        $sql .= "'" . $dataset['created_at'] . "'";
        $sql .= ");\n\n";

        $sql .= "-- Ambil ID dataset yang baru saja dimasukkan\n";
        $sql .= "SET @dataset_id = LAST_INSERT_ID();\n\n";

        // Buat SQL untuk data mentah
        if (!empty($rawData)) {
            $sql .= "-- Masukkan data mentah\n";
            $sql .= "INSERT INTO raw_data (dataset_id, data_id, actual_class, predicted_class) VALUES\n";

            $values = [];
            foreach ($rawData as $row) {
                $values[] = "(@dataset_id, '" . addslashes($row['data_id']) . "', '" .
                    addslashes($row['actual_class']) . "', '" .
                    addslashes($row['predicted_class']) . "')";
            }

            $sql .= implode(",\n", $values) . ";\n\n";
        }

        // Buat SQL untuk confusion matrix
        if (!empty($confusionMatrix)) {
            $sql .= "-- Masukkan hasil confusion matrix\n";
            foreach ($confusionMatrix as $row) {
                $sql .= "INSERT INTO confusion_matrix (";
                $sql .= "dataset_id, class_name, true_positive, false_positive, ";
                $sql .= "true_negative, false_negative, precision_val, recall_val, f1_score";
                $sql .= ") VALUES (";
                $sql .= "@dataset_id, ";
                $sql .= "'" . addslashes($row['class_name']) . "', ";
                $sql .= $row['true_positive'] . ", ";
                $sql .= $row['false_positive'] . ", ";
                $sql .= $row['true_negative'] . ", ";
                $sql .= $row['false_negative'] . ", ";
                $sql .= ($row['precision_val'] !== null ? $row['precision_val'] : "NULL") . ", ";
                $sql .= ($row['recall_val'] !== null ? $row['recall_val'] : "NULL") . ", ";
                $sql .= ($row['f1_score'] !== null ? $row['f1_score'] : "NULL");
                $sql .= ");\n";
            }
        }

        return $sql;
    }

    /**
     * Import dataset dari file JSON
     * 
     * @param string $jsonData Data JSON
     * @return array Status import
     */
    public function importDatasetFromJson($jsonData)
    {
        try {
            // Decode JSON
            $data = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['status' => 'error', 'message' => 'Format JSON tidak valid: ' . json_last_error_msg()];
            }

            // Validasi struktur data
            if (!isset($data['dataset']) || !isset($data['raw_data'])) {
                return ['status' => 'error', 'message' => 'Format data tidak valid: dataset atau raw_data tidak ditemukan'];
            }

            // Simpan dataset
            $datasetName = $data['dataset']['name'];
            $datasetId = $this->db->saveDataset($datasetName);

            // Simpan data mentah
            foreach ($data['raw_data'] as $row) {
                $rawData = [
                    $row['data_id'],
                    $row['actual_class'],
                    $row['predicted_class']
                ];
                $this->db->saveRawData($datasetId, $rawData);
            }

            // Simpan hasil confusion matrix jika ada
            if (isset($data['confusion_matrix']) && !empty($data['confusion_matrix'])) {
                foreach ($data['confusion_matrix'] as $row) {
                    // Jika data menggunakan format lama (precision dan recall), konversi ke format baru
                    if (isset($row['precision']) && !isset($row['precision_val'])) {
                        $row['precision_val'] = $row['precision'];
                    }
                    if (isset($row['recall']) && !isset($row['recall_val'])) {
                        $row['recall_val'] = $row['recall'];
                    }

                    $metrics = [
                        'tp' => $row['true_positive'],
                        'fp' => $row['false_positive'],
                        'tn' => $row['true_negative'],
                        'fn' => $row['false_negative'],
                        'precision' => $row['precision_val'] ?? $row['precision'],
                        'recall' => $row['recall_val'] ?? $row['recall'],
                        'f1_score' => $row['f1_score']
                    ];
                    $this->db->saveConfusionMatrix($datasetId, $row['class_name'], $metrics);
                }
            }

            // Update akurasi dataset
            if (isset($data['dataset']['accuracy'])) {
                $this->db->updateAccuracy($datasetId, $data['dataset']['accuracy']);
            }

            return [
                'status' => 'success',
                'message' => 'Dataset berhasil diimport',
                'dataset_id' => $datasetId
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error saat import data: ' . $e->getMessage()];
        }
    }

    /**
     * Mendapatkan statistik database
     * 
     * @return array Statistik database
     */
    public function getDatabaseStats()
    {
        try {
            // Inisialisasi statistik
            $stats = [
                'total_datasets' => 0,
                'total_raw_data' => 0,
                'total_classes' => 0,
                'average_accuracy' => 0,
                'last_updated' => null
            ];

            // Ambil jumlah dataset
            $datasets = $this->db->getDatasets();
            $stats['total_datasets'] = count($datasets);

            if ($stats['total_datasets'] > 0) {
                // Ambil last updated
                $stats['last_updated'] = $datasets[0]['created_at'];

                // Hitung rata-rata akurasi
                $totalAccuracy = 0;
                $datasetWithAccuracy = 0;

                foreach ($datasets as $dataset) {
                    if ($dataset['accuracy'] !== null) {
                        $totalAccuracy += $dataset['accuracy'];
                        $datasetWithAccuracy++;
                    }
                }

                if ($datasetWithAccuracy > 0) {
                    $stats['average_accuracy'] = $totalAccuracy / $datasetWithAccuracy;
                }

                // Ambil jumlah kelas unik
                $uniqueClasses = [];
                foreach ($datasets as $dataset) {
                    $matrices = $this->db->getConfusionMatrix($dataset['id']);
                    foreach ($matrices as $matrix) {
                        $uniqueClasses[$matrix['class_name']] = true;
                    }
                }

                $stats['total_classes'] = count($uniqueClasses);

                // Hitung total data mentah
                $totalRawData = 0;
                foreach ($datasets as $dataset) {
                    $rawData = $this->db->getRawData($dataset['id']);
                    $totalRawData += count($rawData);
                }

                $stats['total_raw_data'] = $totalRawData;
            }

            return $stats;
        } catch (Exception $e) {
            return ['error' => 'Error saat mengambil statistik: ' . $e->getMessage()];
        }
    }

    /**
     * Export data dataset ke format CSV
     * 
     * @param int $datasetId ID dataset
     * @return string Data CSV
     */
    public function exportDatasetToCsv($datasetId)
    {
        // Validasi parameter
        $datasetId = intval($datasetId);
        if ($datasetId <= 0) {
            return "Error: ID dataset tidak valid";
        }

        // Ambil data dataset
        $dataset = $this->db->getDataset($datasetId);
        if (!$dataset) {
            return "Error: Dataset tidak ditemukan";
        }

        // Ambil data mentah
        $rawData = $this->db->getRawData($datasetId);
        if (empty($rawData)) {
            return "Error: Tidak ada data untuk dataset ini";
        }

        // Buat output CSV
        $output = fopen('php://temp', 'r+');

        // Tulis header
        fputcsv($output, ['id', 'nama_alternatif', 'nilai_vektor_v', 'kelayakan_sistem', 'kelayakan_aktual']);

        // Tulis data
        foreach ($rawData as $row) {
            fputcsv($output, [
                $row['data_id'],
                $row['nama_alternatif'],
                $row['nilai_vektor_v'],
                $row['kelayakan_sistem'],
                $row['kelayakan_aktual']
            ]);
        }

        // Ambil konten CSV
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export data dataset ke format Excel
     * 
     * @param int $datasetId ID dataset
     * @return Spreadsheet objek Spreadsheet
     */
    public function exportDatasetToExcel($datasetId)
    {
        // Validasi parameter
        $datasetId = intval($datasetId);
        if ($datasetId <= 0) {
            throw new Exception('ID dataset tidak valid');
        }

        // Ambil data dataset
        $dataset = $this->db->getDataset($datasetId);
        if (!$dataset) {
            throw new Exception('Dataset tidak ditemukan');
        }

        // Ambil data mentah
        $rawData = $this->db->getRawData($datasetId);
        if (empty($rawData)) {
            throw new Exception('Tidak ada data untuk dataset ini');
        }

        // Ambil data confusion matrix
        $confusionMatrixData = $this->db->getConfusionMatrix($datasetId);

        // Buat spreadsheet baru
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Informasi Dataset
        $infoSheet = $spreadsheet->getActiveSheet();
        $infoSheet->setTitle('Informasi Dataset');

        // Judul
        $infoSheet->setCellValue('A1', 'HASIL PERHITUNGAN CONFUSION MATRIX SPK');
        $infoSheet->mergeCells('A1:E1');
        $infoSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $infoSheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Informasi Dataset
        $infoSheet->setCellValue('A3', 'Informasi Dataset');
        $infoSheet->getStyle('A3')->getFont()->setBold(true);

        $infoSheet->setCellValue('A4', 'Nama Dataset:');
        $infoSheet->setCellValue('B4', $dataset['name']);

        $infoSheet->setCellValue('A5', 'Waktu Dibuat:');
        $infoSheet->setCellValue('B5', $dataset['created_at']);

        $infoSheet->setCellValue('A6', 'Akurasi:');
        $infoSheet->setCellValue('B6', number_format($dataset['accuracy'], 2) . '%');

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
        foreach ($rawData as $data) {
            $dataSheet->setCellValue('A' . $row, $data['data_id']);
            $dataSheet->setCellValue('B' . $row, $data['nama_alternatif']);
            $dataSheet->setCellValue('C' . $row, $data['nilai_vektor_v']);
            $dataSheet->setCellValue('D' . $row, $data['kelayakan_sistem']);
            $dataSheet->setCellValue('E' . $row, $data['kelayakan_aktual']);

            // Status Confusion Matrix
            $status = '';
            if ($data['kelayakan_aktual'] === 'Layak' && $data['kelayakan_sistem'] === 'Layak') {
                $status = 'TP';
                $dataSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FF27AE60'); // Hijau
            } elseif ($data['kelayakan_aktual'] !== 'Layak' && $data['kelayakan_sistem'] === 'Layak') {
                $status = 'FP';
                $dataSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FFE67E22'); // Oranye
            } elseif ($data['kelayakan_aktual'] === 'Layak' && $data['kelayakan_sistem'] !== 'Layak') {
                $status = 'FN';
                $dataSheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FFE67E22'); // Oranye
            } elseif ($data['kelayakan_aktual'] !== 'Layak' && $data['kelayakan_sistem'] !== 'Layak') {
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
        $cmSheet->setCellValue('B3', 'Prediksi');
        $cmSheet->mergeCells('B3:C3');
        $cmSheet->getStyle('B3:C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $cmSheet->setCellValue('A4', 'Aktual');
        $cmSheet->setCellValue('B4', 'Layak');
        $cmSheet->setCellValue('C4', 'Tidak Layak');

        // Matrix data (FP dan FN sudah ditukar)
        $layak = [];
        foreach ($confusionMatrixData as $row) {
            if ($row['class_name'] === 'Layak') {
                $layak = [
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

        $cmSheet->setCellValue('A5', 'Layak');
        $cmSheet->setCellValue('B5', $layak['tp'] ?? 0);
        $cmSheet->setCellValue('C5', $layak['fp'] ?? 0);

        $cmSheet->setCellValue('A6', 'Tidak Layak');
        $cmSheet->setCellValue('B6', $layak['fn'] ?? 0);
        $cmSheet->setCellValue('C6', $layak['tn'] ?? 0);

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
        $cmSheet->setCellValue('B10', $layak['tp'] ?? 0);
        $cmSheet->setCellValue('C10', $layak['fp'] ?? 0);
        $cmSheet->setCellValue('D10', $layak['tn'] ?? 0);
        $cmSheet->setCellValue('E10', $layak['fn'] ?? 0);
        $cmSheet->setCellValue('F10', number_format(($layak['precision'] ?? 0) * 100, 2) . '%');
        $cmSheet->setCellValue('G10', number_format(($layak['recall'] ?? 0) * 100, 2) . '%');
        $cmSheet->setCellValue('H10', number_format(($layak['f1_score'] ?? 0) * 100, 2) . '%');

        // Style metrik
        $cmSheet->getStyle('A9:H10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $cmSheet->getStyle('A9:H9')->getFont()->setBold(true);
        $cmSheet->getStyle('A9:H9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');

        // Akurasi
        $totalData = count($rawData);
        $totalBenar = ($layak['tp'] ?? 0) + ($layak['tn'] ?? 0);
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

        // Kembali ke sheet pertama
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
