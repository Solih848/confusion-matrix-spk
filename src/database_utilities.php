<?php

/**
 * File database_utilities.php untuk operasi database lanjutan
 * seperti export dan import data untuk Sistem Penghitungan Confusion Matrix
 */

// Muat konfigurasi database
require_once 'config.php';

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
}
