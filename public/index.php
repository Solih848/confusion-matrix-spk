<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Inisialisasi database
$db = new Database();

// Ambil semua dataset
$datasets = $db->getDatasets();

// Tentukan tab aktif
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'upload';
$selectedDataset = isset($_GET['dataset']) ? intval($_GET['dataset']) : 0;

// Proses upload Excel (.xlsx)
$uploadedData = [];
if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
    $fileInfo = pathinfo($_FILES['excel_file']['name']);
    if (strtolower($fileInfo['extension']) === 'xlsx') {
        $excelFile = $_FILES['excel_file']['tmp_name'];
        try {
            $spreadsheet = IOFactory::load($excelFile);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            // Validasi header minimal 4 kolom
            if ($rows && count($rows[0]) >= 4) {
                foreach ($rows as $i => $row) {
                    if ($i === 0) continue; // skip header
                    if (count($row) >= 4) {
                        $uploadedData[] = [
                            'id' => $row[0],
                            'nama_alternatif' => $row[1],
                            'nilai_vektor_v' => $row[2],
                            'kelayakan_sistem' => $row[3],
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            // error reading excel
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Perhitungan Confusion Matrix SPK</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Sistem Perhitungan Confusion Matrix SPK</h1>
            <p>Aplikasi untuk menghitung dan menganalisis confusion matrix dari sistem pendukung keputusan</p>
        </header>

        <nav>
            <ul class="tabs">
                <li class="<?php echo $activeTab === 'upload' ? 'active' : ''; ?>">
                    <a href="?tab=upload">Upload Data</a>
                </li>
                <li class="<?php echo $activeTab === 'history' ? 'active' : ''; ?>">
                    <a href="?tab=history">Riwayat</a>
                </li>
                <?php if ($selectedDataset > 0) : ?>
                    <li class="<?php echo $activeTab === 'results' ? 'active' : ''; ?>">
                        <a href="?tab=results&dataset=<?php echo $selectedDataset; ?>">Hasil</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <main>
            <?php if ($activeTab === 'upload') : ?>
                <section class="upload-section">
                    <h2>Upload Data SPK</h2>
                    <p>Upload file Excel (.xlsx) dengan format: id, nama_alternatif, nilai_vektor_v, kelayakan_sistem <br><b>(tanpa kolom kelayakan_aktual)</b></p>

                    <?php if (empty($uploadedData)) : ?>
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="dataset_name">Nama Dataset:</label>
                                <input type="text" id="dataset_name" name="dataset_name" required>
                            </div>
                            <div class="form-group">
                                <label for="excel_file">Pilih File Excel (.xlsx):</label>
                                <input type="file" id="excel_file" name="excel_file" accept=".xlsx" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn">Upload & Input Kelayakan Aktual</button>
                            </div>
                        </form>
                        <div class="csv-format-info">
                            <h3>Format Excel</h3>
                            <p>File Excel harus memiliki format sebagai berikut:</p>
                            <table class="sample-table table-data">
                                <thead>
                                    <tr>
                                        <th>id</th>
                                        <th>nama_alternatif</th>
                                        <th>nilai_vektor_v</th>
                                        <th>kelayakan_sistem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Alternatif A</td>
                                        <td>0.85</td>
                                        <td>Layak</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Alternatif B</td>
                                        <td>0.75</td>
                                        <td>Layak</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Alternatif C</td>
                                        <td>0.45</td>
                                        <td>Tidak Layak</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>Alternatif D</td>
                                        <td>0.92</td>
                                        <td>Layak</td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>Alternatif E</td>
                                        <td>0.38</td>
                                        <td>Tidak Layak</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p>
                                <a href="download_template_excel.php" class="btn btn-small">Download Template Excel</a>
                            </p>
                            <div class="excel-info">
                                <b>Tips:</b> Anda dapat mengisi data pada file Excel, lalu upload ke sistem.
                            </div>
                        </div>
                    <?php else : ?>
                        <form action="input_aktual.php" method="post" id="form-aktual">
                            <input type="hidden" name="dataset_name" value="<?php echo htmlspecialchars($_POST['dataset_name'] ?? ''); ?>">

                            <!-- Tambahkan kontrol massal untuk kelayakan aktual -->
                            <div class="bulk-actions">
                                <div class="bulk-select">
                                    <label>
                                        <input type="checkbox" id="select-all-checkbox"> Pilih Semua Data
                                    </label>
                                    <div class="bulk-help">
                                        <small>Centang ini untuk memilih semua data di semua halaman</small>
                                    </div>
                                </div>
                                <div class="bulk-apply">
                                    <label for="bulk-kelayakan">Set Kelayakan Terpilih:</label>
                                    <select id="bulk-kelayakan">
                                        <option value="">-- Pilih --</option>
                                        <option value="Layak">Layak</option>
                                        <option value="Tidak Layak">Tidak Layak</option>
                                    </select>
                                    <button type="button" id="apply-bulk" class="btn btn-small">Terapkan</button>
                                    <button type="button" id="show-all-data" class="btn btn-small btn-secondary">Tampilkan Semua Data</button>
                                </div>
                            </div>

                            <div class="data-warning">
                                <div class="alert alert-warning">
                                    <strong>Perhatian!</strong> Untuk menghindari kesalahan, disarankan untuk <strong>menampilkan semua data terlebih dahulu</strong> dengan mengklik tombol "Tampilkan Semua Data" sebelum melakukan pengisian kelayakan aktual secara massal dan menghitung confusion matrix.
                                </div>
                            </div>

                            <table id="data-table" class="raw-data-table table-data">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="header-checkbox"></th>
                                        <th>No</th>
                                        <th>Nama Alternatif</th>
                                        <th>Nilai Vektor V</th>
                                        <th>Kelayakan Sistem</th>
                                        <th>Kelayakan Aktual (Input User)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($uploadedData as $i => $row) : ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="row-checkbox" data-index="<?php echo $i; ?>">
                                            </td>
                                            <td>
                                                <input type="hidden" name="data[<?php echo $i; ?>][id]" value="<?php echo htmlspecialchars($row['id']); ?>">
                                                <?php echo htmlspecialchars($row['id']); ?>
                                            </td>
                                            <td>
                                                <input type="hidden" name="data[<?php echo $i; ?>][nama_alternatif]" value="<?php echo htmlspecialchars($row['nama_alternatif']); ?>">
                                                <?php echo htmlspecialchars($row['nama_alternatif']); ?>
                                            </td>
                                            <td>
                                                <input type="hidden" name="data[<?php echo $i; ?>][nilai_vektor_v]" value="<?php echo htmlspecialchars($row['nilai_vektor_v']); ?>">
                                                <?php echo htmlspecialchars($row['nilai_vektor_v']); ?>
                                            </td>
                                            <td>
                                                <input type="hidden" name="data[<?php echo $i; ?>][kelayakan_sistem]" value="<?php echo htmlspecialchars($row['kelayakan_sistem']); ?>">
                                                <?php echo htmlspecialchars($row['kelayakan_sistem']); ?>
                                            </td>
                                            <td>
                                                <select name="data[<?php echo $i; ?>][kelayakan_aktual]" class="kelayakan-select" required>
                                                    <option value="">Pilih...</option>
                                                    <option value="Layak">Layak</option>
                                                    <option value="Tidak Layak">Tidak Layak</option>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="form-group">
                                <button type="submit" class="btn">Proses Data & Hitung Confusion Matrix</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>
            <?php elseif ($activeTab === 'history') : ?>
                <section class="history-section">
                    <h2>Riwayat Perhitungan</h2>

                    <?php if (empty($datasets)) : ?>
                        <p>Belum ada dataset yang diproses.</p>
                    <?php else : ?>
                        <table class="datasets-table table-data">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Dataset</th>
                                    <th>Akurasi</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datasets as $dataset) : ?>
                                    <tr>
                                        <td><?php echo $dataset['id']; ?></td>
                                        <td><?php echo htmlspecialchars($dataset['name']); ?></td>
                                        <td><?php echo number_format($dataset['accuracy'], 2); ?>%</td>
                                        <td><?php echo $dataset['created_at']; ?></td>
                                        <td>
                                            <a href="?tab=results&dataset=<?php echo $dataset['id']; ?>" class="btn btn-small">Lihat</a>
                                            <a href="edit_data.php?dataset=<?php echo $dataset['id']; ?>" class="btn btn-small btn-primary">Edit</a>
                                            <a href="export_dataset.php?id=<?php echo $dataset['id']; ?>&format=json" class="btn btn-small">Export</a>
                                            <a href="delete_dataset.php?id=<?php echo $dataset['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus dataset ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </section>
            <?php elseif ($activeTab === 'results' && $selectedDataset > 0) : ?>
                <section class="results-section">
                    <?php include 'get_result.php'; ?>
                </section>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> Sistem Perhitungan Confusion Matrix SPK. Versi 2.0</p>
        </footer>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="../js/script.js"></script>
</body>

</html>