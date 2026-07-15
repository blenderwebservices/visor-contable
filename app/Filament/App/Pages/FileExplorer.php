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
        $cacheKey = "user_{$user->id}_folders_parent_" . ($this->currentFolderId ?? 'root');
        
        $folderIds = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(15), function () use ($user) {
            $foldersQuery = Folder::where(function (Builder $query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })->orWhereHas('groups', function ($q) use ($user) {
                    $q->whereHas('users', function ($q2) use ($user) {
                        $q2->where('users.id', $user->id);
                    });
                });
            });

            if ($this->currentFolderId) {
                return $foldersQuery->where('parent_id', $this->currentFolderId)->pluck('id')->toArray();
            }

            return $foldersQuery->whereNull('parent_id')->pluck('id')->toArray();
        });

        return Folder::whereIn('id', $folderIds)->get();
    }

    public function getFiles()
    {
        if (!$this->currentFolderId) {
            return collect();
        }

        $cacheKey = "folder_{$this->currentFolderId}_files";
        
        $fileIds = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(15), function () {
            return FileDocument::where('folder_id', $this->currentFolderId)->pluck('id')->toArray();
        });

        return FileDocument::whereIn('id', $fileIds)->get();
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
            ->modalContent(function (array $arguments) {
                $file = FileDocument::find($arguments['file']);
                $url = '/storage/' . $file->file_path;
                $type = $file->type;
                
                if (in_array($file->type, ['word', 'excel'])) {
                    $pdfPath = DocumentConverterService::convertToPdf($file->file_path);
                    if ($pdfPath) {
                        $url = '/storage/' . $pdfPath;
                        $type = 'pdf'; // Render as PDF now
                    } else {
                        return view('filament.app.components.file-error');
                    }
                }
                
                return view('filament.app.components.file-viewer', ['url' => $url, 'type' => $type]);
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
