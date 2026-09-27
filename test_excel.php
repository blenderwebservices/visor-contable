<?php
require 'vendor/autoload.php';
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Sheet 1');
$sheet1->setCellValue('A1', 'Hello');
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Sheet 2');
$sheet2->setCellValue('A1', 'World');
$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Html');
$writer->writeAllSheets();
$writer->save('test_excel.html');
