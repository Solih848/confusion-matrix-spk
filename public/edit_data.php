<?php

/**
 * File untuk mengedit data OSPI dan menghitung ulang setelah edit
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

// Ambil data mentah
$rawData = $db->getRawData($datasetId);

// Proses form jika disubmit
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    try {
        // Hapus data confusion matrix lama
        $db->deleteConfusionMatrix($datasetId);

        // Update data mentah dan siapkan data untuk perhitungan confusion matrix
        $data = [];
        $classes = ['Layak', 'Tidak Layak']; // Kelas yang digunakan dalam SPK

        foreach ($_POST['data'] as $id => $row) {
            // Update data di database
            $db->updateRawData(
                $id,
                $row['nama_alternatif'],
                $row['nilai_vektor_v'],
                $row['kelayakan_sistem'],
                $row['kelayakan_aktual']
            );

            // Siapkan data untuk perhitungan confusion matrix
            $data[] = [
                'actual' => $row['kelayakan_aktual'],
                'predicted' => $row['kelayakan_sistem']
            ];
        }

        // Hitung ulang confusion matrix
        $cm = new ConfusionMatrix();
        $cm->calculateConfusionMatrix($data, $classes);

        // Simpan hasil confusion matrix baru ke database
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

        $successMessage = 'Data berhasil diperbarui dan confusion matrix telah dihitung ulang.';

        // Ambil data mentah yang sudah diupdate
        $rawData = $db->getRawData($datasetId);
    } catch (Exception $e) {
        $errorMessage = 'Error: ' . $e->getMessage();
    }
}

// Tampilkan halaman HTML
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data OSPI - Sistem Perhitungan Confusion Matrix SPK</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>Edit Data OSPI</h1>
            <p>Edit data dan hitung ulang confusion matrix</p>
        </header>

        <nav>
            <ul class="tabs">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="index.php?tab=history">Riwayat</a></li>
                <li><a href="index.php?tab=results&dataset=<?php echo $datasetId; ?>">Hasil</a></li>
                <li class="active"><a href="edit_data.php?dataset=<?php echo $datasetId; ?>">Edit Data</a></li>
            </ul>
        </nav>

        <main>
            <section class="edit-section">
                <h2>Edit Data Dataset: <?php echo htmlspecialchars($dataset['name']); ?></h2>

                <?php if ($successMessage): ?>
                    <div class="alert alert-success">
                        <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="alert alert-error">
                        <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post" id="form-edit">
                    <div class="bulk-actions">
                        <div class="bulk-select" style="display: flex; align-items: center; gap: 8px;">
                            <label style="margin-bottom:0; display:flex; align-items:center; gap:8px;">
                                <input type="checkbox" id="select-all-checkbox"> Pilih Semua Data
                                <span id="selected-count" style="font-size:13px; color:#007bff; font-weight:500; background:#eaf4ff; border-radius:12px; padding:2px 10px; display:inline-block; min-width:70px; text-align:center; margin-left:0;">(0 terpilih)</span>
                            </label>
                            <div class="bulk-help">
                                <small>Centang ini untuk memilih semua data di semua halaman</small>
                            </div>
                        </div>
                        <div class="bulk-apply">
                            <label for="bulk-kelayakan-sistem">Kelayakan Sistem:</label>
                            <select id="bulk-kelayakan-sistem">
                                <option value="">-- Pilih --</option>
                                <option value="Layak">Layak</option>
                                <option value="Tidak Layak">Tidak Layak</option>
                            </select>
                            <button type="button" id="apply-bulk-sistem" class="btn btn-small">Terapkan</button>

                            <label for="bulk-kelayakan-aktual">Kelayakan Aktual:</label>
                            <select id="bulk-kelayakan-aktual">
                                <option value="">-- Pilih --</option>
                                <option value="Layak">Layak</option>
                                <option value="Tidak Layak">Tidak Layak</option>
                            </select>
                            <button type="button" id="apply-bulk-aktual" class="btn btn-small">Terapkan</button>

                            <button type="button" id="show-all-data" class="btn btn-small btn-secondary">Tampilkan Semua Data</button>
                        </div>
                    </div>

                    <div class="data-warning">
                        <div class="alert alert-warning">
                            <strong>Perhatian!</strong> Untuk menghindari kesalahan, disarankan untuk <strong>menampilkan semua data terlebih dahulu</strong> dengan mengklik tombol "Tampilkan Semua Data" sebelum menyimpan perubahan dan menghitung ulang.
                        </div>
                    </div>

                    <table id="data-table" class="raw-data-table table-data">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="header-checkbox">
                                </th>
                                <th>No</th>
                                <th>Nama Alternatif</th>
                                <th>Nilai Vektor V</th>
                                <th>Kelayakan Sistem</th>
                                <th>Kelayakan Aktual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rawData as $row) : ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-checkbox" data-id="<?php echo $row['id']; ?>">
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['data_id']); ?>
                                    </td>
                                    <td>
                                        <input type="text" name="data[<?php echo $row['id']; ?>][nama_alternatif]" value="<?php echo htmlspecialchars($row['nama_alternatif']); ?>" class="form-control">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="data[<?php echo $row['id']; ?>][nilai_vektor_v]" value="<?php echo htmlspecialchars($row['nilai_vektor_v']); ?>" class="form-control">
                                    </td>
                                    <td>
                                        <select name="data[<?php echo $row['id']; ?>][kelayakan_sistem]" class="kelayakan-sistem-select">
                                            <option value="Layak" <?php echo ($row['kelayakan_sistem'] === 'Layak') ? 'selected' : ''; ?>>Layak</option>
                                            <option value="Tidak Layak" <?php echo ($row['kelayakan_sistem'] === 'Tidak Layak') ? 'selected' : ''; ?>>Tidak Layak</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="data[<?php echo $row['id']; ?>][kelayakan_aktual]" class="kelayakan-aktual-select">
                                            <option value="Layak" <?php echo ($row['kelayakan_aktual'] === 'Layak') ? 'selected' : ''; ?>>Layak</option>
                                            <option value="Tidak Layak" <?php echo ($row['kelayakan_aktual'] === 'Tidak Layak') ? 'selected' : ''; ?>>Tidak Layak</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="form-group form-edit">
                        <button type="submit" name="save_changes" id="save-changes-btn" class="btn"><i class="fas fa-calculator"></i> Simpan Perubahan & Hitung Ulang</button>
                        <a href="index.php?tab=results&dataset=<?php echo $datasetId; ?>" class="btn btn-secondary btn-batal"><i class="fas fa-times"></i> Batal</a>
                    </div>
                </form>
            </section>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> Sistem Perhitungan Confusion Matrix SPK. Versi 2.0</p>
        </footer>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Variabel untuk melacak status tampilan data
            var allDataShown = false;

            // Inisialisasi DataTable
            var table = $('#data-table').DataTable({
                "pageLength": 10,
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Semua"]
                ]
            });

            // Tampilkan semua data
            $('#show-all-data').click(function() {
                table.page.len(-1).draw();
                allDataShown = true;
                showAlert('Menampilkan semua data dalam satu halaman', 'info');
            });

            // Pilih semua checkbox
            $('#select-all-checkbox').change(function() {
                $('.row-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Header checkbox untuk halaman saat ini
            $('#header-checkbox').change(function() {
                var isChecked = $(this).prop('checked');
                table.page.info().start;
                table.page.info().end;

                table.rows({
                    page: 'current'
                }).nodes().each(function(node) {
                    $(node).find('.row-checkbox').prop('checked', isChecked);
                });
            });

            // Terapkan kelayakan sistem massal
            $('#apply-bulk-sistem').click(function() {
                var selectedValue = $('#bulk-kelayakan-sistem').val();
                if (!selectedValue) {
                    alert('Silakan pilih nilai kelayakan terlebih dahulu!');
                    return;
                }

                var checkedRows = $('.row-checkbox:checked');
                if (checkedRows.length === 0) {
                    alert('Silakan pilih minimal satu baris terlebih dahulu!');
                    return;
                }

                checkedRows.each(function() {
                    var rowId = $(this).data('id');
                    $('select[name="data[' + rowId + '][kelayakan_sistem]"]').val(selectedValue);
                });

                showAlert('Berhasil mengubah ' + checkedRows.length + ' data menjadi "' + selectedValue + '"', 'success');
            });

            // Terapkan kelayakan aktual massal
            $('#apply-bulk-aktual').click(function() {
                var selectedValue = $('#bulk-kelayakan-aktual').val();
                if (!selectedValue) {
                    alert('Silakan pilih nilai kelayakan terlebih dahulu!');
                    return;
                }

                var checkedRows = $('.row-checkbox:checked');
                if (checkedRows.length === 0) {
                    alert('Silakan pilih minimal satu baris terlebih dahulu!');
                    return;
                }

                checkedRows.each(function() {
                    var rowId = $(this).data('id');
                    $('select[name="data[' + rowId + '][kelayakan_aktual]"]').val(selectedValue);
                });

                showAlert('Berhasil mengubah ' + checkedRows.length + ' data menjadi "' + selectedValue + '"', 'success');
            });

            // Konfirmasi sebelum menyimpan perubahan
            $('#form-edit').on('submit', function(e) {
                if (!allDataShown) {
                    if (!confirm('Anda belum menampilkan semua data. Disarankan untuk menampilkan semua data terlebih dahulu dengan mengklik tombol "Tampilkan Semua Data" untuk menghindari kesalahan.\n\nLanjutkan menyimpan perubahan?')) {
                        e.preventDefault();
                        return false;
                    }
                }
                return true;
            });

            // Fungsi untuk menampilkan pesan alert
            function showAlert(message, type) {
                var alertDiv = $('<div class="alert alert-' + type + '">' + message + '</div>');
                $('.edit-section h2').after(alertDiv);

                setTimeout(function() {
                    alertDiv.fadeOut(500, function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            // Tampilkan pesan informasi saat halaman dimuat
            setTimeout(function() {
                showAlert('Untuk memudahkan pengisian, gunakan fitur "Pilih Semua Data" dan "Tampilkan Semua Data"', 'info');
            }, 1000);

            // Fungsi untuk update jumlah terpilih
            function updateSelectedCount() {
                var count = $('.row-checkbox:checked').length;
                $('#selected-count').text('(' + count + ' terpilih)');
            }

            // Update saat halaman dimuat
            updateSelectedCount();

            // Update saat checkbox baris diubah
            $(document).on('change', '.row-checkbox', function() {
                updateSelectedCount();
            });

            // Update saat header checkbox diubah
            $('#header-checkbox').change(function() {
                setTimeout(updateSelectedCount, 10);
            });

            // Update saat select-all-checkbox diubah
            $('#select-all-checkbox').change(function() {
                setTimeout(updateSelectedCount, 10);
            });

            // Update juga setelah DataTable draw (misal paginasi)
            var table = $('#data-table').DataTable();
            table.on('draw', function() {
                updateSelectedCount();
            });
        });
    </script>
</body>

</html>