<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Data header dan contoh
$header = ['id', 'nama_alternatif', 'nilai_vektor_v', 'kelayakan_sistem'];
$data = [
    [1, 'Alternatif A', 0.85, 'Layak'],
    [2, 'Alternatif B', 0.75, 'Layak'],
    [3, 'Alternatif C', 0.45, 'Tidak Layak'],
    [4, 'Alternatif D', 0.92, 'Layak'],
    [5, 'Alternatif E', 0.38, 'Tidak Layak'],
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray($header, NULL, 'A1');
$sheet->fromArray($data, NULL, 'A2');

// Styling header
$headerStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'F2F2F2']
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'AAAAAA']
        ]
    ]
];
$sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

// Styling data
$dataStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'AAAAAA']
        ]
    ]
];
$sheet->getStyle('A2:D6')->applyFromArray($dataStyle);

// Set lebar kolom otomatis
foreach (range('A', 'D') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="sample_data.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
