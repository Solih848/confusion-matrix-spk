<?php

/**
 * Konfigurasi Database dan Kelas Database
 * untuk Sistem Penghitungan Confusion Matrix SPK
 */

// Konfigurasi Database MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'confusion_matrix');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Kelas Database untuk mengelola koneksi dan operasi MySQL
 */
class Database
{
    private $db;

    /**
     * Konstruktor - Inisialisasi koneksi database
     */
    public function __construct()
    {
        try {
            // Menggunakan PDO dengan MySQL
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->db = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->createTables();
        } catch (PDOException $e) {
            die('Error connecting to database: ' . $e->getMessage());
        }
    }

    /**
     * Membuat tabel yang diperlukan jika belum ada
     */
    private function createTables()
    {
        try {
            // Cek apakah tabel datasets (lama) masih ada
            $stmt = $this->db->query("SHOW TABLES LIKE 'datasets'");
            $oldTableExists = $stmt->rowCount() > 0;

            if ($oldTableExists) {
                // Jika tabel lama ada, pindahkan datanya ke tabel baru
                // Buat tabel dataset baru jika belum ada
                $this->db->exec('
                    CREATE TABLE IF NOT EXISTS dataset (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        accuracy FLOAT DEFAULT 0,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ');

                // Pindahkan data dari datasets ke dataset
                $this->db->exec('
                    INSERT IGNORE INTO dataset (id, name, accuracy, created_at)
                    SELECT id, name, accuracy, created_at FROM datasets
                ');

                // Hapus tabel lama setelah selesai migrasi
                // Nonaktifkan foreign key checks untuk memungkinkan penghapusan
                $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
                $this->db->exec('DROP TABLE IF EXISTS datasets');
                $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
            } else {
                // Buat tabel dataset baru jika belum ada
        $this->db->exec('
                    CREATE TABLE IF NOT EXISTS dataset (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                        accuracy FLOAT DEFAULT 0,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ');
            }

            // Cek apakah tabel raw_data perlu di-alter
            $stmt = $this->db->query("DESCRIBE raw_data");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $needsUpdate = !in_array('nama_alternatif', $columns);

            if ($needsUpdate) {
                // Buat tabel raw_data_new dengan struktur baru
                $this->db->exec('
                    CREATE TABLE IF NOT EXISTS raw_data_new (
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

                // Pindahkan data dari tabel lama ke baru
                try {
                    $this->db->exec('
                        INSERT INTO raw_data_new (dataset_id, data_id, nama_alternatif, nilai_vektor_v, kelayakan_sistem, kelayakan_aktual)
                        SELECT dataset_id, data_id, CONCAT("Alternatif ", data_id), 0, predicted_class, actual_class
                        FROM raw_data
                    ');

                    // Hapus tabel lama dan rename tabel baru
                    $this->db->exec('DROP TABLE raw_data');
                    $this->db->exec('RENAME TABLE raw_data_new TO raw_data');
                } catch (PDOException $e) {
                    // Jika tabel lama tidak ada kolom yang diharapkan, buat tabel baru saja
                    $this->db->exec('DROP TABLE IF EXISTS raw_data');
                    $this->db->exec('
                        CREATE TABLE raw_data (
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
                }
            } else {
                // Pastikan tabel raw_data ada dengan struktur yang benar
                $this->db->exec('
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
            }

        // Tabel untuk menyimpan hasil confusion matrix
        $this->db->exec('
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

            // Tambahkan indeks untuk mempercepat query
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_raw_data_dataset_id ON raw_data (dataset_id)');
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_confusion_matrix_dataset_id ON confusion_matrix (dataset_id)');
            $this->db->exec('CREATE INDEX IF NOT EXISTS idx_datasets_created_at ON dataset (created_at)');
        } catch (PDOException $e) {
            // Gagal melakukan migrasi, kembalikan ke pembuatan tabel standar
            $this->db->exec('
                CREATE TABLE IF NOT EXISTS dataset (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    accuracy FLOAT DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ');

            $this->db->exec('
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

        $this->db->exec('
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
        }
    }

    /**
     * Menyimpan dataset baru
     * 
     * @param string $name Nama dataset
     * @return int ID dataset yang baru dibuat
     */
    public function saveDataset($name)
    {
        $stmt = $this->db->prepare('INSERT INTO dataset (name) VALUES (:name)');
        $stmt->bindValue(':name', $name);
        $stmt->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Menyimpan data mentah SPK
     * 
     * @param int $datasetId ID dataset
     * @param array $data Data dalam format [id, nama_alternatif, nilai_vektor_v, kelayakan_sistem, kelayakan_aktual]
     */
    public function saveRawDataSpk($datasetId, $data)
    {
        $stmt = $this->db->prepare('
            INSERT INTO raw_data (dataset_id, data_id, nama_alternatif, nilai_vektor_v, kelayakan_sistem, kelayakan_aktual) 
            VALUES (:dataset_id, :data_id, :nama_alternatif, :nilai_vektor_v, :kelayakan_sistem, :kelayakan_aktual)
        ');

        $stmt->bindValue(':dataset_id', $datasetId);
        $stmt->bindValue(':data_id', $data[0]);
        $stmt->bindValue(':nama_alternatif', $data[1]);
        $stmt->bindValue(':nilai_vektor_v', $data[2]);
        $stmt->bindValue(':kelayakan_sistem', $data[3]);
        $stmt->bindValue(':kelayakan_aktual', $data[4]);

        $stmt->execute();
    }

    /**
     * Menyimpan data mentah (kompatibilitas mundur)
     * 
     * @param int $datasetId ID dataset
     * @param array $data Data dalam format [data_id, actual_class, predicted_class]
     */
    public function saveRawData($datasetId, $data)
    {
        $stmt = $this->db->prepare('
            INSERT INTO raw_data (dataset_id, data_id, nama_alternatif, nilai_vektor_v, kelayakan_aktual, kelayakan_sistem) 
            VALUES (:dataset_id, :data_id, :nama_alternatif, 0, :kelayakan_aktual, :kelayakan_sistem)
        ');

        $stmt->bindValue(':dataset_id', $datasetId);
        $stmt->bindValue(':data_id', $data[0]);
        $stmt->bindValue(':nama_alternatif', 'Alternatif ' . $data[0]);
        $stmt->bindValue(':kelayakan_aktual', $data[1]);
        $stmt->bindValue(':kelayakan_sistem', $data[2]);

        $stmt->execute();
    }

    /**
     * Menyimpan hasil confusion matrix
     * 
     * @param int $datasetId ID dataset
     * @param string $className Nama kelas
     * @param array $metrics Metrik confusion matrix
     */
    public function saveConfusionMatrix($datasetId, $className, $metrics)
    {
        $stmt = $this->db->prepare('
            INSERT INTO confusion_matrix (
                dataset_id, class_name, true_positive, false_positive, 
                true_negative, false_negative, precision_val, recall_val, f1_score
            ) VALUES (
                :dataset_id, :class_name, :true_positive, :false_positive, 
                :true_negative, :false_negative, :precision_val, :recall_val, :f1_score
            )
        ');

        $stmt->bindValue(':dataset_id', $datasetId);
        $stmt->bindValue(':class_name', $className);
        $stmt->bindValue(':true_positive', $metrics['tp']);
        $stmt->bindValue(':false_positive', $metrics['fp']);
        $stmt->bindValue(':true_negative', $metrics['tn']);
        $stmt->bindValue(':false_negative', $metrics['fn']);
        $stmt->bindValue(':precision_val', $metrics['precision']);
        $stmt->bindValue(':recall_val', $metrics['recall']);
        $stmt->bindValue(':f1_score', $metrics['f1_score']);

        $stmt->execute();
    }

    /**
     * Memperbarui akurasi dataset
     * 
     * @param int $datasetId ID dataset
     * @param float $accuracy Nilai akurasi
     */
    public function updateAccuracy($datasetId, $accuracy)
    {
        $stmt = $this->db->prepare('UPDATE dataset SET accuracy = :accuracy WHERE id = :id');
        $stmt->bindValue(':accuracy', $accuracy);
        $stmt->bindValue(':id', $datasetId);
        $stmt->execute();
    }

    /**
     * Mendapatkan semua dataset
     * 
     * @return array Daftar dataset
     */
    public function getDatasets()
    {
        $stmt = $this->db->query('SELECT * FROM dataset ORDER BY created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mendapatkan dataset berdasarkan ID
     * 
     * @param int $id ID dataset
     * @return array|false Data dataset atau false jika tidak ditemukan
     */
    public function getDataset($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM dataset WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mendapatkan hasil confusion matrix berdasarkan dataset ID
     * 
     * @param int $datasetId ID dataset
     * @return array Hasil confusion matrix
     */
    public function getConfusionMatrix($datasetId)
    {
        $stmt = $this->db->prepare('SELECT * FROM confusion_matrix WHERE dataset_id = :dataset_id');
        $stmt->bindValue(':dataset_id', $datasetId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mendapatkan data mentah berdasarkan dataset ID
     * 
     * @param int $datasetId ID dataset
     * @return array Data mentah
     */
    public function getRawData($datasetId)
    {
        $stmt = $this->db->prepare('SELECT * FROM raw_data WHERE dataset_id = :dataset_id');
        $stmt->bindValue(':dataset_id', $datasetId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menghapus dataset dan semua data terkait
     * 
     * @param int $id ID dataset
     * @return bool Status keberhasilan
     */
    public function deleteDataset($id)
    {
        $stmt = $this->db->prepare('DELETE FROM dataset WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Menghapus data confusion matrix berdasarkan dataset ID
     * 
     * @param int $datasetId ID dataset
     * @return bool Status keberhasilan
     */
    public function deleteConfusionMatrix($datasetId)
    {
        $stmt = $this->db->prepare('DELETE FROM confusion_matrix WHERE dataset_id = :dataset_id');
        $stmt->bindValue(':dataset_id', $datasetId);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Memperbarui data mentah
     * 
     * @param int $id ID data mentah
     * @param string $namaAlternatif Nama alternatif
     * @param float $nilaiVektorV Nilai vektor V
     * @param string $kelayakanSistem Kelayakan sistem
     * @param string $kelayakanAktual Kelayakan aktual
     * @return bool Status keberhasilan
     */
    public function updateRawData($id, $namaAlternatif, $nilaiVektorV, $kelayakanSistem, $kelayakanAktual)
    {
        $stmt = $this->db->prepare('
            UPDATE raw_data 
            SET nama_alternatif = :nama_alternatif,
                nilai_vektor_v = :nilai_vektor_v,
                kelayakan_sistem = :kelayakan_sistem,
                kelayakan_aktual = :kelayakan_aktual
            WHERE id = :id
        ');

        $stmt->bindValue(':nama_alternatif', $namaAlternatif);
        $stmt->bindValue(':nilai_vektor_v', $nilaiVektorV);
        $stmt->bindValue(':kelayakan_sistem', $kelayakanSistem);
        $stmt->bindValue(':kelayakan_aktual', $kelayakanAktual);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
