<?php

namespace App\Filament\Resources\FolderResource\Pages;

use App\Filament\Resources\FolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFolders extends ListRecords
{
    protected static string $resource = FolderResource::class;

    public string $viewMode = 'explorer';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('view_list')
                    ->label('Vista Lista')
                    ->icon('heroicon-o-list-bullet')
                    ->action(fn () => $this->viewMode = 'list'),
                Actions\Action::make('view_grid')
                    ->label('Vista Objetos')
                    ->icon('heroicon-o-squares-2x2')
                    ->action(fn () => $this->viewMode = 'grid'),
                Actions\Action::make('view_explorer')
                    ->label('Vista Explorer')
                    ->icon('heroicon-o-folder-open')
                    ->action(fn () => $this->viewMode = 'explorer'),
            ])->label('Modo de Vista')->icon('heroicon-m-eye')->button(),
            Actions\CreateAction::make(),
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
