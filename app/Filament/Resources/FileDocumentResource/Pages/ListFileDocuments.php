<?php

namespace App\Filament\Resources\FileDocumentResource\Pages;

use App\Filament\Resources\FileDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFileDocuments extends ListRecords
{
    protected static string $resource = FileDocumentResource::class;

    public string $viewMode = 'list';

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

    public function addFolderItemAction(): Actions\Action
    {
        return Actions\Action::make('addFolderItem')
            ->label('Adicionar a Carpeta')
            ->modalHeading('Adicionar Elemento')
            ->form([
                \Filament\Forms\Components\Select::make('type')
                    ->label('¿Qué deseas agregar?')
                    ->options([
                        'file' => 'Subir un nuevo Archivo',
                        'folder' => 'Crear una Subcarpeta',
                    ])
                    ->required()
                    ->live(),
                
                \Filament\Forms\Components\TextInput::make('folder_name')
                    ->label('Nombre de la Subcarpeta')
                    ->required()
                    ->visible(fn (\Filament\Forms\Get $get) => $get('type') === 'folder'),
                    
                \Filament\Forms\Components\FileUpload::make('new_file')
                    ->label('Archivo')
                    ->directory('documents')
                    ->required()
                    ->visible(fn (\Filament\Forms\Get $get) => $get('type') === 'file'),
                
                \Filament\Forms\Components\TextInput::make('file_name')
                    ->label('Nombre descriptivo (Opcional)')
                    ->visible(fn (\Filament\Forms\Get $get) => $get('type') === 'file'),
            ])
            ->action(function (array $data, array $arguments) {
                $folder = \App\Models\Folder::find($arguments['folder_id']);
                if (!$folder) return;

                if ($data['type'] === 'folder') {
                    $folder->children()->create([
                        'name' => $data['folder_name']
                    ]);
                    \Filament\Notifications\Notification::make()->title('Subcarpeta creada')->success()->send();
                } else {
                    $extension = strtolower(pathinfo($data['new_file'], PATHINFO_EXTENSION));
                    $fileType = match ($extension) {
                        'pdf' => 'pdf',
                        'doc', 'docx' => 'word',
                        'xls', 'xlsx' => 'excel',
                        'jpg', 'jpeg', 'png', 'gif' => 'image',
                        'txt' => 'txt',
                        default => 'other',
                    };
                    $folder->fileDocuments()->create([
                        'name' => $data['file_name'] ?: basename($data['new_file']),
                        'file_path' => $data['new_file'],
                        'type' => $fileType,
                        'created_by' => auth()->id(),
                    ]);
                    \Filament\Notifications\Notification::make()->title('Archivo subido')->success()->send();
                }
            });
    }

    public function deleteFolderHierarchyAction(): Actions\Action
    {
        return Actions\Action::make('deleteFolderHierarchy')
            ->label('Eliminar Carpeta')
            ->color('danger')
            ->modalHeading('Opciones de Eliminación')
            ->form([
                \Filament\Forms\Components\Radio::make('delete_mode')
                    ->label('¿Qué deseas eliminar?')
                    ->options([
                        'all' => 'Toda la subestructura (Carpetas y Archivos)',
                        'only_folders' => 'Solo la subestructura (Carpetas, respetando Archivos)',
                        'only_files' => 'Solo los archivos (Respetando subestructura)',
                    ])
                    ->descriptions([
                        'all' => 'Se eliminará en cascada esta carpeta, subcarpetas y todos sus archivos.',
                        'only_folders' => 'Se eliminarán las carpetas, pero los archivos se moverán a la carpeta padre.',
                        'only_files' => 'Se eliminarán los archivos, pero las carpetas quedarán vacías.',
                    ])
                    ->required()
                    ->default('all'),
            ])
            ->action(function (array $data, array $arguments) {
                $folder = \App\Models\Folder::find($arguments['folder_id']);
                if (!$folder) return;

                $mode = $data['delete_mode'];

                if ($mode === 'all') {
                    $folder->delete();
                    \Filament\Notifications\Notification::make()->title('Carpeta e hijos eliminados')->success()->send();
                } 
                elseif ($mode === 'only_files') {
                    $this->deleteFilesRecursively($folder);
                    \Filament\Notifications\Notification::make()->title('Archivos eliminados')->success()->send();
                }
                elseif ($mode === 'only_folders') {
                    $parentId = $folder->parent_id; 
                    $this->moveFilesAndDropFolders($folder, $parentId);
                    \Filament\Notifications\Notification::make()->title('Carpetas eliminadas, archivos respetados')->success()->send();
                }
            });
    }

    protected function deleteFilesRecursively($folder)
    {
        $folder->fileDocuments()->delete();
        foreach ($folder->children as $child) {
            $this->deleteFilesRecursively($child);
        }
    }

    protected function moveFilesAndDropFolders($folder, $targetFolderId)
    {
        // Mover archivos directos
        $folder->fileDocuments()->update(['folder_id' => $targetFolderId]);
        
        // Hacer lo mismo con los hijos recursivamente
        foreach ($folder->children as $child) {
            $this->moveFilesAndDropFolders($child, $targetFolderId);
        }

        // Borrar carpeta
        $folder->delete();
    }
}
