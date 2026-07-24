@php
    $query = \App\Models\Folder::query();
    
    if (auth()->check() && auth()->user()->role === 'reader') {
        $query->where(function ($q) {
            $q->whereHas('users', function ($q2) {
                $q2->where('users.id', auth()->id());
            })->orWhereHas('groups', function ($q2) {
                $q2->whereHas('users', function ($q3) {
                    $q3->where('users.id', auth()->id());
                });
            });
        });
    }
    
    $allAccessibleFolders = $query->with(['fileDocuments', 'groups'])->get()->keyBy('id');
    
    $tree = [];
    foreach ($allAccessibleFolders as $folder) {
        if (!$folder->parent_id || !isset($allAccessibleFolders[$folder->parent_id])) {
            $folder->assigned_children = collect();
            $tree[$folder->id] = $folder;
        }
    }
    
    foreach ($allAccessibleFolders as $folder) {
        if ($folder->parent_id && isset($allAccessibleFolders[$folder->parent_id])) {
            if (!isset($allAccessibleFolders[$folder->parent_id]->assigned_children)) {
                $allAccessibleFolders[$folder->parent_id]->assigned_children = collect();
            }
            $allAccessibleFolders[$folder->parent_id]->assigned_children->push($folder);
        }
    }
@endphp

<div class="p-4 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
    <div class="space-y-4">
        @foreach($tree as $folder)
            <div class="relative bg-gray-50 dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 flex flex-col justify-center">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-3">
                        <x-heroicon-s-folder class="w-8 h-8 text-blue-500" />
                        <h3 class="font-bold text-md text-gray-900 dark:text-white" title="{{ $folder->name }}">
                            {{ $folder->name }}
                            @if($folder->groups && $folder->groups->count() > 0)
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $folder->groups->pluck('name')->join(', ') }})</span>
                            @endif
                        </h3>
                    </div>
                    
                    <div class="flex space-x-1">
                        <x-filament::button wire:click="mountTableAction('view', '{{ $folder->id }}')" size="xs" color="gray" icon="heroicon-o-eye" class="px-1" title="Visualizar"></x-filament::button>
                        <x-filament::button wire:click="mountTableAction('edit', '{{ $folder->id }}')" size="xs" color="primary" icon="heroicon-o-pencil" class="px-1" title="Editar"></x-filament::button>
                        <x-filament::button wire:click="mountTableAction('delete', '{{ $folder->id }}')" size="xs" color="danger" icon="heroicon-o-trash" class="px-1" title="Eliminar"></x-filament::button>
                    </div>
                </div>
                
                <!-- Contenido recursivo -->
                @include('filament.resources.folder-resource.components.folder-tree-node', ['folder' => $folder])
            </div>
        @endforeach
        
        @if(empty($tree))
            <div class="text-center py-10">
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay carpetas disponibles en esta vista.</p>
            </div>
        @endif
    </div>
</div>
