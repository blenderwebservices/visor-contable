<?php

namespace App\Filament\App\Pages;

use App\Models\Group;
use App\Models\Folder;
use App\Models\FileDocument;
use App\Models\Annotation;
use App\Services\DocumentConverterService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;

class FileExplorer extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $title = 'Explorador de Archivos';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.app.pages.file-explorer';

    public ?int $currentGroupId = null;
    public ?int $currentFolderId = null;
    public string $viewMode = 'cards'; // cards or list

    public function mount()
    {
        $this->currentGroupId = request()->query('group');
        $this->currentFolderId = request()->query('folder');
    }

    public function openGroup($groupId)
    {
        $this->currentGroupId = $groupId;
        $this->currentFolderId = null;
    }

    public function openFolder($folderId)
    {
        $this->currentFolderId = $folderId;
    }

    public function goUp()
    {
        if ($this->currentFolderId) {
            $folder = Folder::find($this->currentFolderId);
            if ($folder && $folder->parent_id) {
                $this->currentFolderId = $folder->parent_id;
            } else {
                $this->currentFolderId = null;
            }
        } elseif ($this->currentGroupId) {
            $this->currentGroupId = null;
        }
    }
    
    public function goToPath($groupId = null, $folderId = null)
    {
        $this->currentGroupId = $groupId;
        $this->currentFolderId = $folderId;
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'cards' ? 'list' : 'cards';
    }

    public function getGroups()
    {
        if ($this->currentGroupId || $this->currentFolderId) {
            return collect();
        }

        $user = Auth::user();
        if ($user->role === 'admin' || $user->role === 'supervisor') {
            return Group::all();
        }

        return $user->groups;
    }

    public function getFolders()
    {
        $user = Auth::user();

        $query = Folder::query();
        
        if ($user->role === 'reader') {
            $query->where(function ($q) use ($user) {
                $q->whereHas('users', function ($q2) use ($user) {
                    $q2->where('users.id', $user->id);
                })->orWhereHas('groups', function ($q2) use ($user) {
                    $q2->whereHas('users', function ($q3) use ($user) {
                        $q3->where('users.id', $user->id);
                    });
                });
            });
        }

        if ($this->currentFolderId) {
            return (clone $query)->where('parent_id', $this->currentFolderId)->get();
        } elseif ($this->currentGroupId) {
            // Folders explicitly associated with the current group
            return (clone $query)
                ->whereHas('groups', function ($q) {
                    $q->where('groups.id', $this->currentGroupId);
                })
                ->get();
        }

        // Return all root-level folders accessible to the user
        return (clone $query)->whereNull('parent_id')->get();
    }

    public function getFiles()
    {
        if (!$this->currentFolderId) {
            return collect();
        }

        return FileDocument::where('folder_id', $this->currentFolderId)->get();
    }

    public function getBreadcrumbsArrayProperty()
    {
        $crumbs = [];
        
        if ($this->currentGroupId) {
            $group = Group::find($this->currentGroupId);
            if ($group) {
                $crumbs[] = ['label' => $group->name, 'groupId' => $group->id, 'folderId' => null];
            }
        }

        if ($this->currentFolderId) {
            $folder = Folder::find($this->currentFolderId);
            $folderPath = [];
            while ($folder) {
                array_unshift($folderPath, ['label' => $folder->name, 'groupId' => $this->currentGroupId, 'folderId' => $folder->id]);
                $folder = $folder->parent;
            }
            $crumbs = array_merge($crumbs, $folderPath);
        }
        
        return $crumbs;
    }

    public function getCurrentGroupProperty()
    {
        return Group::find($this->currentGroupId);
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
