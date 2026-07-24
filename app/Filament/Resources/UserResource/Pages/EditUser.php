<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function viewFileAction(): Actions\Action
    {
        return Actions\Action::make('viewFile')
            ->modalHeading(fn (array $arguments) => \App\Models\FileDocument::find($arguments['file'])->name ?? 'Visor')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->closeModalByClickingAway(false)
            ->modalContent(function (array $arguments) {
                $file = \App\Models\FileDocument::find($arguments['file']);
                $url = route('documents.view', ['fileDocument' => $file->id]);
                $type = $file->type;
                
                $originalUrl = '/storage/' . $file->file_path;
                
                $extension = pathinfo($file->file_path, PATHINFO_EXTENSION);
                $downloadFileName = $file->name;
                if ($extension && !str_ends_with(strtolower($downloadFileName), '.' . strtolower($extension))) {
                    $downloadFileName .= '.' . $extension;
                }
                
                if (in_array($file->type, ['word', 'excel'])) {
                    $pdfPath = \App\Services\DocumentConverterService::convertToPdf($file->file_path);
                    if ($pdfPath) {
                        $type = 'pdf';
                    } else {
                        return view('filament.app.components.file-error');
                    }
                }
                
                return view('filament.app.components.file-viewer', [
                    'url' => $url, 
                    'type' => $type,
                    'isDownloadable' => $file->is_downloadable,
                    'isPrintable' => $file->is_printable,
                    'downloadUrl' => $originalUrl,
                    'fileName' => $downloadFileName,
                ]);
            });
    }
}
