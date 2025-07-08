<?php

/**
 * File untuk menghapus dataset dari Sistem Perhitungan Confusion Matrix SPK
 */

// Muat konfigurasi database
require_once __DIR__ . '/../src/config.php';

// Periksa apakah ID dataset diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect dengan pesan error
    header('Location: index.php?tab=history&status=error&message=ID dataset tidak valid');
    exit;
}

$datasetId = intval($_GET['id']);

// Inisialisasi database
$db = new Database();

// Hapus dataset
$success = $db->deleteDataset($datasetId);

// Redirect dengan pesan sesuai hasil
if ($success) {
    header('Location: index.php?tab=history&status=success&message=Dataset berhasil dihapus');
} else {
    header('Location: index.php?tab=history&status=error&message=Gagal menghapus dataset');
}
exit;
