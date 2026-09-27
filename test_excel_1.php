<?php
require 'vendor/autoload.php';
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setCellValue('A1', 'Hello');
$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Html');
$writer->writeAllSheets();
$writer->save('test_excel_1.html');
