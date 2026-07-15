<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;

class DocumentConverterService
{
    public static function convertToPdf($filePath)
    {
        // $filePath is the relative path in the 'public' disk or 'local' disk
        // We'll assume the files are in storage/app/public/documents
        $absolutePath = Storage::disk('public')->path($filePath);
        $directory = dirname($absolutePath);
        
        $fileName = pathinfo($absolutePath, PATHINFO_FILENAME);
        $pdfFileName = $fileName . '.pdf';
        $pdfAbsolutePath = $directory . '/' . $pdfFileName;
        
        // If it already exists, return the path
        if (file_exists($pdfAbsolutePath)) {
            return str_replace(Storage::disk('public')->path(''), '', $pdfAbsolutePath);
        }

        // We assume libreoffice is installed and accessible via 'soffice' or 'libreoffice'
        $process = new Process([
            'libreoffice',
            '--headless',
            '--convert-to',
            'pdf',
            '--outdir',
            $directory,
            $absolutePath
        ]);

        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            // Log error or handle gracefully if LibreOffice is missing
            \Log::error('LibreOffice conversion failed: ' . $process->getErrorOutput());
            return null; // Conversion failed
        }

        return str_replace(Storage::disk('public')->path(''), '', $pdfAbsolutePath);
    }
}
