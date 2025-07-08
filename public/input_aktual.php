<?php
// Endpoint ini hanya meneruskan data POST ke process.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dataset_name']) && isset($_POST['data'])) {
    // Forward ke process.php
    require 'process.php';
    exit;
} else {
    header('Location: index.php?tab=upload&status=error&message=Data tidak lengkap');
    exit;
}
