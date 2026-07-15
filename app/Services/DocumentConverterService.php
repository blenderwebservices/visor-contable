<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;

use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as PhpSpreadsheetIOFactory;

class DocumentConverterService
{
    public static function convertToPdf($filePath)
    {
        // $filePath is the relative path in the 'public' disk or 'local' disk
        $absolutePath = Storage::disk('public')->path($filePath);
        $directory = dirname($absolutePath);
        
        $fileName = pathinfo($absolutePath, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $pdfFileName = $fileName . '.pdf';
        $pdfAbsolutePath = $directory . '/' . $pdfFileName;
        
        // If it already exists, return the path
        if (file_exists($pdfAbsolutePath)) {
            return str_replace(Storage::disk('public')->path(''), '', $pdfAbsolutePath);
        }

        try {
            if (in_array($extension, ['doc', 'docx', 'rtf', 'odt'])) {
                // Setup DomPDF for PhpWord
                \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF);
                \PhpOffice\PhpWord\Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

                $phpWord = PhpWordIOFactory::load($absolutePath);
                $pdfWriter = PhpWordIOFactory::createWriter($phpWord, 'PDF');
                $pdfWriter->save($pdfAbsolutePath);

            } elseif (in_array($extension, ['xls', 'xlsx', 'csv', 'ods'])) {
                $spreadsheet = PhpSpreadsheetIOFactory::load($absolutePath);
                
                // PhpSpreadsheet supports Dompdf out of the box with the 'Dompdf' writer
                $writer = PhpSpreadsheetIOFactory::createWriter($spreadsheet, 'Dompdf');
                $writer->save($pdfAbsolutePath);
            } else {
                return null;
            }
        } catch (\Exception $e) {
            \Log::error('PHP Conversion failed: ' . $e->getMessage());
            return null; // Conversion failed
        }

        if (file_exists($pdfAbsolutePath)) {
            chmod($pdfAbsolutePath, 0644);
            return str_replace(Storage::disk('public')->path(''), '', $pdfAbsolutePath);
        }

        return null;
    }
}
