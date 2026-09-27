<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filePath = 'documents/01J8R6H5A72P0QZ6V073E2QTVR.xlsx'; // Or we can create a dummy excel file in storage
// Let's create a dummy excel file first
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setCellValue('A1', 'Hello');
$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save(storage_path('app/public/test.xlsx'));

$res = \App\Services\DocumentConverterService::convertToHtml('test.xlsx');
echo "Result: " . $res . "\n";
