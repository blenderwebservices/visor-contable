<?php

namespace App\Filament\App\Pages;

use App\Models\Folder;
use App\Models\FileDocument;
use App\Models\Annotation;
use App\Services\DocumentConverterService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;

class FileExplorer extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $title = 'Explorador de Archivos';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.app.pages.file-explorer';

    public ?int $currentFolderId = null;
    public string $viewMode = 'cards'; // cards or list

    public function mount()
    {
        $this->currentFolderId = request()->query('folder');
    }

    public function openFolder($folderId)
    {
        $this->currentFolderId = $folderId;
    }

    public function goUp()
    {
        if ($this->currentFolderId) {
            $folder = Folder::find($this->currentFolderId);
            $this->currentFolderId = $folder?->parent_id;
        }
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'cards' ? 'list' : 'cards';
    }

    public function getFolders()
    {
        $user = Auth::user();
        
        $accessibleFolderIds = Folder::where(function (Builder $query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->orWhereHas('groups', function ($q) use ($user) {
                $q->whereHas('users', function ($q2) use ($user) {
                    $q2->where('users.id', $user->id);
                });
            });
        })->pluck('id')->toArray();

        if ($this->currentFolderId) {
            if (!in_array($this->currentFolderId, $accessibleFolderIds)) {
                return collect();
            }
            return Folder::whereIn('id', $accessibleFolderIds)
                         ->where('parent_id', $this->currentFolderId)
                         ->get();
        }

        return Folder::whereIn('id', $accessibleFolderIds)
            ->where(function($q) use ($accessibleFolderIds) {
                $q->whereNull('parent_id')
                  ->orWhereNotIn('parent_id', $accessibleFolderIds);
            })
            ->get();
    }

    public function getFiles()
    {
        if (!$this->currentFolderId) {
            return collect();
        }

        return FileDocument::where('folder_id', $this->currentFolderId)->get();
    }
    
    public function getCurrentFolderProperty()
    {
        return Folder::find($this->currentFolderId);
    }

    public function viewFileAction(): Action
    {
        return Action::make('viewFile')
            ->modalHeading(fn (array $arguments) => FileDocument::find($arguments['file'])->name ?? 'Visor')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->closeModalByClickingAway(false)
            ->modalContent(function (array $arguments) {
                $file = FileDocument::find($arguments['file']);
                $url = route('documents.view', ['fileDocument' => $file->id]);
                $type = $file->type;
                
                $originalUrl = '/storage/' . $file->file_path;
                
                $extension = pathinfo($file->file_path, PATHINFO_EXTENSION);
                $downloadFileName = $file->name;
                if ($extension && !str_ends_with(strtolower($downloadFileName), '.' . strtolower($extension))) {
                    $downloadFileName .= '.' . $extension;
                }
                
                if (in_array($file->type, ['word', 'excel'])) {
                    $pdfPath = DocumentConverterService::convertToPdf($file->file_path);
                    if ($pdfPath) {
                        // The route will serve the PDF version
                        $type = 'pdf'; // Render as PDF now
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

    public function viewNotesAction(): Action
    {
        return Action::make('viewNotes')
            ->modalHeading('Notas del Archivo')
            ->form([
                Textarea::make('content')->label('Nueva Nota')->required(),
            ])
            ->action(function (array $data, array $arguments) {
                Annotation::create([
                    'file_document_id' => $arguments['file'],
                    'user_id' => Auth::id(),
                    'content' => $data['content'],
                ]);
            })
            ->modalContent(function (array $arguments) {
                $notes = Annotation::where('file_document_id', $arguments['file'])->with('user')->latest()->get();
                return view('filament.app.components.file-notes', ['notes' => $notes]);
            });
    }
}
