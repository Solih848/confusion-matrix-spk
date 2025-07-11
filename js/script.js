/**
 * Script untuk Sistem Perhitungan Confusion Matrix SPK
 */


document.addEventListener('DOMContentLoaded', function () {
    // Fungsi untuk memvalidasi file CSV sebelum diupload
    const fileInput = document.getElementById('csv_file');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const fileSize = this.files[0].size / 1024 / 1024; // dalam MB
            if (fileSize > 5) {
                alert('Ukuran file terlalu besar. Maksimal 5MB.');
                this.value = '';
            }

            const fileExt = this.value.split('.').pop().toLowerCase();
            if (fileExt !== 'csv') {
                alert('Hanya file CSV yang diperbolehkan.');
                this.value = '';
            }
        });
    }

    // Fungsi untuk halaman Matrix Gabungan
    const selectAllBtn = document.getElementById('select-all');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('input[name="datasets[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        });
    }

    const deselectAllBtn = document.getElementById('deselect-all');
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function () {
            const checkboxes = document.querySelectorAll('input[name="datasets[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    }

    // Menampilkan alert jika ada parameter status di URL
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('message');

    if (status && message) {
        if (status === 'success') {
            showAlert(message, 'success');
        } else if (status === 'error') {
            showAlert(message, 'error');
        }
    }

    // Variabel untuk menyimpan status checkbox global
    let allCheckboxesSelected = false;

    // Inisialisasi form aktual dan tampilkan peringatan
    const formAktual = document.getElementById('form-aktual');
    if (formAktual) {
        // Tampilkan peringatan tentang fitur baru setelah halaman dimuat
        setTimeout(() => {
            showAlert('Untuk memudahkan pengisian, gunakan fitur "Pilih Semua Data" dan "Tampilkan Semua Data"', 'info');
        }, 1000);
    }

    // Inisialisasi DataTables jika ada tabel data
    let dataTable = null;
    if (jQuery().DataTable) {
        // Periksa apakah tabel sudah diinisialisasi sebelumnya
        const dataTableElement = $('#data-table');
        if (dataTableElement.length) {
            if ($.fn.dataTable.isDataTable(dataTableElement)) {
                // Jika sudah diinisialisasi, hancurkan instance lama
                dataTableElement.DataTable().destroy();
            }

            // Inisialisasi DataTable baru dengan opsi yang dioptimalkan
            dataTable = dataTableElement.DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                // Konfigurasi untuk memastikan semua data dapat diakses
                stateSave: true,
                pagingType: "full_numbers",
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                drawCallback: function () {
                    // Mempertahankan status checkbox setelah paginasi
                    if (allCheckboxesSelected) {
                        $('.row-checkbox').prop('checked', true);
                        $('#header-checkbox').prop('checked', true);
                    }
                }
            });
        }

        // Inisialisasi tabel lain jika ada
        $('.table-data:not(#data-table)').each(function () {
            if ($.fn.dataTable.isDataTable(this)) {
                $(this).DataTable().destroy();
            }

            $(this).DataTable({
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                }
            });
        });
    }

    // Validasi form input kelayakan aktual
    if (formAktual) {
        formAktual.addEventListener('submit', function (e) {
            let valid = true;
            const selects = formAktual.querySelectorAll('select[name^="data"][name$="[kelayakan_aktual]"]');
            selects.forEach(function (select) {
                if (!select.value) {
                    valid = false;
                    select.style.border = '2px solid #e74c3c';
                } else {
                    select.style.border = '';
                }
            });
            if (!valid) {
                alert('Semua kolom Kelayakan Aktual harus diisi!');
                e.preventDefault();
            }
        });

        // Fungsi untuk menangani checkbox header (pilih semua yang terlihat)
        const headerCheckbox = document.getElementById('header-checkbox');
        if (headerCheckbox) {
            headerCheckbox.addEventListener('change', function () {
                // Pilih semua checkbox yang terlihat di halaman saat ini
                const visibleCheckboxes = dataTable ? dataTable.$('.row-checkbox').toArray() : document.querySelectorAll('.row-checkbox');
                visibleCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                
                // Jika header checkbox dicentang, periksa apakah semua baris dicentang
                updateSelectAllCheckbox();
            });
        }

        // Fungsi untuk menangani tombol "Tampilkan Semua Data"
        const showAllDataBtn = document.getElementById('show-all-data');
        if (showAllDataBtn && dataTable) {
            showAllDataBtn.addEventListener('click', function () {
                // Ubah jumlah baris yang ditampilkan menjadi -1 (semua)
                dataTable.page.len(-1).draw();
                showAlert('Menampilkan semua data dalam satu halaman', 'info');
            });
        }

        // Fungsi untuk menangani checkbox "Pilih Semua"
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                allCheckboxesSelected = this.checked;

                // Update header checkbox
                const headerCheckbox = document.getElementById('header-checkbox');
                if (headerCheckbox) {
                    headerCheckbox.checked = this.checked;
                }

                // Update semua checkbox yang terlihat
                if (dataTable) {
                    const visibleCheckboxes = dataTable.$('.row-checkbox').toArray();
                    visibleCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                } else {
                    // Fallback jika dataTable tidak tersedia
                    const allCheckboxes = document.querySelectorAll('.row-checkbox');
                    allCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                }

                // Tampilkan pesan informasi
                if (this.checked) {
                    showAlert('Semua data telah dipilih, termasuk yang tidak terlihat di halaman ini', 'info');
                }
            });
        }

        // Tambahkan event listener untuk setiap checkbox baris
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('row-checkbox')) {
                // Ketika checkbox baris diubah, periksa status "Pilih Semua"
                updateSelectAllCheckbox();
            }
        });

        // Fungsi untuk memeriksa dan memperbarui status checkbox "Pilih Semua"
        function updateSelectAllCheckbox() {
            const allRowCheckboxes = document.querySelectorAll('.row-checkbox');
            const checkedRowCheckboxes = document.querySelectorAll('.row-checkbox:checked');
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            
            // Jika ada checkbox baris yang tidak dicentang, batalkan "Pilih Semua"
            if (allRowCheckboxes.length > checkedRowCheckboxes.length) {
                allCheckboxesSelected = false;
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                }
            } else if (allRowCheckboxes.length === checkedRowCheckboxes.length && allRowCheckboxes.length > 0) {
                // Jika semua checkbox baris dicentang, centang "Pilih Semua"
                allCheckboxesSelected = true;
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = true;
                }
            }
        }

        // Fungsi untuk menangani tombol "Terapkan" untuk bulk action
        const applyBulkBtn = document.getElementById('apply-bulk');
        if (applyBulkBtn) {
            applyBulkBtn.addEventListener('click', function () {
                const bulkKelayakan = document.getElementById('bulk-kelayakan').value;
                if (!bulkKelayakan) {
                    alert('Silakan pilih nilai kelayakan terlebih dahulu!');
                    return;
                }

                // Periksa apakah pengguna sudah menampilkan semua data
                const currentPageLength = dataTable ? dataTable.page.len() : 10;
                const isAllDataShown = currentPageLength === -1;

                // Jika "Pilih Semua" dicentang tapi tidak semua data ditampilkan, tampilkan konfirmasi
                if (allCheckboxesSelected && !isAllDataShown) {
                    const confirmAction = confirm(
                        'Anda sedang mengubah SEMUA data, tetapi tidak semua data ditampilkan di layar saat ini.\n\n' +
                        'Disarankan untuk menampilkan semua data terlebih dahulu dengan mengklik tombol "Tampilkan Semua Data".\n\n' +
                        'Lanjutkan mengubah semua data?'
                    );

                    if (!confirmAction) {
                        return;
                    }
                }

                // Hitung jumlah data yang akan diubah
                let totalChanged = 0;

                if (allCheckboxesSelected) {
                    // Jika "Pilih Semua" dicentang, ubah semua data
                    const allSelects = document.querySelectorAll('select[name^="data"][name$="[kelayakan_aktual]"]');
                    allSelects.forEach(select => {
                        select.value = bulkKelayakan;
                        totalChanged++;
                    });
                } else {
                    // Jika tidak, ubah hanya yang dicentang
                    const checkedRows = document.querySelectorAll('.row-checkbox:checked');
                    if (checkedRows.length === 0) {
                        alert('Silakan pilih minimal satu baris terlebih dahulu!');
                        return;
                    }

                    checkedRows.forEach(checkbox => {
                        const index = checkbox.getAttribute('data-index');
                        const selectElement = document.querySelector(`select[name="data[${index}][kelayakan_aktual]"]`);
                        if (selectElement) {
                            selectElement.value = bulkKelayakan;
                            totalChanged++;
                        }
                    });
                }

                // Tampilkan pesan sukses
                showAlert(`Berhasil mengubah ${totalChanged} data menjadi "${bulkKelayakan}"`, 'success');
            });
        }
    }
});

/**
 * Menampilkan pesan alert
 * 
 * @param {string} message Pesan yang akan ditampilkan
 * @param {string} type Tipe alert (success/error)
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = message;

    // Tambahkan ke container
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);

    // Otomatis hilangkan setelah 5 detik
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => {
            alertDiv.remove();
        }, 500);
    }, 5000);
}

/**
 * Konfirmasi sebelum menghapus dataset
 * 
 * @param {Element} element Elemen link yang diklik
 * @returns {boolean} True jika user mengkonfirmasi, false jika tidak
 */
function confirmDelete(element) {
    return confirm('Apakah Anda yakin ingin menghapus dataset ini?');
}

// Fungsi untuk mengatur tab
function openTab(tabName) {
    // Sembunyikan semua konten tab
    const tabContents = document.getElementsByClassName('tab-content');
    for (let i = 0; i < tabContents.length; i++) {
        tabContents[i].classList.remove('active');
    }

    // Nonaktifkan semua tombol tab
    const tabButtons = document.getElementsByClassName('tab-btn');
    for (let i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove('active');
    }

    // Tampilkan tab yang dipilih
    document.getElementById(tabName).classList.add('active');

    // Aktifkan tombol tab yang dipilih
    const activeButtons = document.querySelectorAll(`.tab-btn[onclick="openTab('${tabName}')"]`);
    for (let i = 0; i < activeButtons.length; i++) {
        activeButtons[i].classList.add('active');
    }

    // Perbarui URL dengan parameter tab
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);

    // Jika tab results dan ada parameter dataset, muat hasil
    if (tabName === 'results' && url.searchParams.has('dataset')) {
        loadDatasetResult(url.searchParams.get('dataset'));
    }
}

// Fungsi untuk memuat daftar dataset
function loadDatasetList() {
    const tbody = document.querySelector('#dataset-table tbody');
    fetch('public/list_datasets.php')
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4">Belum ada dataset.</td></tr>';
            } else {
                data.forEach(dataset => {
                    const row = `<tr>
                        <td><a href="#" class="dataset-link" data-id="${dataset.id}">${dataset.name}</a></td>
                        <td>${dataset.created_at}</td>
                        <td>${parseFloat(dataset.accuracy).toFixed(2)}%</td>
                        <td class="action-buttons">
                            <a href="public/get_result.php?dataset=${dataset.id}" class="btn btn-sm">Lihat</a>
                            <a href="public/edit_data.php?dataset=${dataset.id}" class="btn btn-sm btn-primary">Edit</a>
                            <button onclick="deleteDataset(${dataset.id})" class="btn btn-sm btn-danger">Hapus</button>
                        </td>
                    </tr>`;
                    tbody.innerHTML += row;
                });
            }
        })
        .catch(error => {
            console.error('Error memuat daftar dataset:', error);
            tbody.innerHTML = '<tr><td colspan="4">Gagal memuat daftar dataset.</td></tr>';
        });
}

// Fungsi untuk memuat hasil dataset
function loadDatasetResult(datasetId) {
    const resultsContainer = document.getElementById('results-container');
    resultsContainer.innerHTML = '<p>Memuat hasil...</p>';

    // Gunakan path yang benar ke get_result.php
    fetch(`public/get_result.php?dataset=${datasetId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal memuat hasil. Status: ' + response.status);
            }
            return response.text();
        })
        .then(html => {
            resultsContainer.innerHTML = html;
            // Inisialisasi DataTables pada tabel hasil yang baru dimuat
            const newTable = $('#raw-data-table-dt');
            if (newTable.length && jQuery().DataTable) {
                if ($.fn.dataTable.isDataTable(newTable)) {
                    newTable.DataTable().destroy();
                }
                newTable.DataTable({
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                    },
                    stateSave: true,
                    pagingType: "full_numbers",
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultsContainer.innerHTML = `<div class="error-message"><h2>Error</h2><p>${error.message}</p></div>`;
        });
}

// Fungsi untuk menghapus dataset
function deleteDataset(datasetId) {
    if (confirm('Apakah Anda yakin ingin menghapus dataset ini?')) {
        // Hapus dataset dengan AJAX
        fetch(`delete_dataset.php?dataset_id=${datasetId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    // Reload halaman untuk memperbarui daftar
                    location.reload();
                } else {
                    alert(`Error: ${data.message}`);
                }
            })
            .catch(error => {
                alert(`Error: ${error.message}`);
            });
    }
}

// Event listener saat dokumen dimuat
document.addEventListener('DOMContentLoaded', function () {
    // Periksa parameter URL untuk tab aktif
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');

    if (tabParam) {
        openTab(tabParam);
    }

    // Periksa parameter dataset
    const datasetParam = urlParams.get('dataset');
    if (datasetParam) {
        loadDatasetResult(datasetParam);
    }
}); 