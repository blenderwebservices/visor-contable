<?php

use Illuminate\Support\Facades\Route;
use App\Models\FileDocument;
use Illuminate\Support\Facades\Storage;
use App\Services\DocumentConverterService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/documents/view/{fileDocument}', function (FileDocument $fileDocument) {
    abort_unless(auth()->check() && auth()->user()->can('view', $fileDocument), 403, 'No autorizado para ver este documento.');

    $filePath = $fileDocument->file_path;
    
    if ($fileDocument->type === 'excel') {
        $htmlPath = DocumentConverterService::convertToHtml($filePath);
        if ($htmlPath) {
            $filePath = $htmlPath;
        } else {
            abort(404, 'No se pudo generar la vista previa HTML.');
        }
    } elseif ($fileDocument->type === 'word') {
        $pdfPath = DocumentConverterService::convertToPdf($filePath);
        if ($pdfPath) {
            $filePath = $pdfPath;
        } else {
            abort(404, 'No se pudo generar la vista previa PDF.');
        }
    }
    
    $absolutePath = Storage::disk('public')->path($filePath);
    
    if (!file_exists($absolutePath)) {
        abort(404);
    }
    
    $mime = mime_content_type($absolutePath);
    if ($fileDocument->type === 'pdf' || str_ends_with(strtolower($filePath), '.pdf')) {
        $mime = 'application/pdf';
    } elseif ($fileDocument->type === 'excel' || str_ends_with(strtolower($filePath), '.html')) {
        $mime = 'text/html; charset=utf-8';
    }
    
    return response()->file($absolutePath, [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"'
    ]);
})->name('documents.view')->middleware(['web', 'auth']);
