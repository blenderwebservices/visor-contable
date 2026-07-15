<?php

use Illuminate\Support\Facades\Route;
use App\Models\FileDocument;
use Illuminate\Support\Facades\Storage;
use App\Services\DocumentConverterService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/documents/view/{fileDocument}', function (FileDocument $fileDocument) {
    $filePath = $fileDocument->file_path;
    
    if (in_array($fileDocument->type, ['word', 'excel'])) {
        $pdfPath = DocumentConverterService::convertToPdf($filePath);
        if ($pdfPath) {
            $filePath = $pdfPath;
        } else {
            abort(404, 'No se pudo generar la vista previa.');
        }
    }
    
    $absolutePath = Storage::disk('public')->path($filePath);
    
    if (!file_exists($absolutePath)) {
        abort(404);
    }
    
    $mime = mime_content_type($absolutePath);
    if ($fileDocument->type === 'pdf' || str_ends_with(strtolower($filePath), '.pdf')) {
        $mime = 'application/pdf';
    }
    
    return response()->file($absolutePath, [
        'Content-Type' => $mime,
        'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"'
    ]);
})->name('documents.view')->middleware(['web']);
