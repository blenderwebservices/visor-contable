@php
    $groupedByFolder = $records->groupBy(function ($record) {
        return $record->folder ? $record->folder->id : 'unassigned';
    });

    // Fetch all folders to find empty ones
    $allFolders = \App\Models\Folder::with('groups')->get();
    
    // Create an array of folder data merging populated and empty folders
    $displayFolders = [];

    // First add "Sin Carpeta" if it exists
    if ($groupedByFolder->has('unassigned')) {
        $displayFolders['unassigned'] = [
            'folder' => null,
            'name' => 'Sin Carpeta',
            'groupNames' => '',
            'files' => $groupedByFolder['unassigned']
        ];
    }

    // Add all existing folders (populated or empty)
    foreach ($allFolders as $folder) {
        $files = $groupedByFolder->has($folder->id) ? $groupedByFolder[$folder->id] : collect();
        $displayFolders[$folder->id] = [
            'folder' => $folder,
            'name' => $folder->name,
            'groupNames' => $folder->groups ? $folder->groups->pluck('name')->join(', ') : '',
            'files' => $files
        ];
    }
@endphp

<div class="p-4 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
    <div class="space-y-6">
        @foreach($displayFolders as $id => $data)
            <div class="relative bg-gray-50 dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                    <div class="flex items-center space-x-3 flex-1">
                        <x-heroicon-s-folder class="w-6 h-6 text-blue-500" />
                        <h3 class="font-bold text-sm text-gray-900 dark:text-white flex-1">
                            {{ $data['name'] }}
                            @if($data['groupNames'])
                                <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-1">({{ $data['groupNames'] }})</span>
                            @endif
                        </h3>
                    </div>
                    
                    @if($data['folder'])
                        <div class="flex items-center space-x-2">
                            <button wire:click="mountAction('addFolderItem', { folder_id: {{ $data['folder']->id }} })" class="text-sm flex items-center gap-1 text-gray-600 hover:text-green-600 dark:text-gray-400 dark:hover:text-green-400 transition-colors bg-white dark:bg-gray-700 px-2 py-1 rounded shadow-sm border border-gray-200 dark:border-gray-600">
                                <x-heroicon-o-plus class="w-4 h-4" /> <span class="hidden sm:inline">Adicionar</span>
                            </button>
                            <button wire:click="mountAction('deleteFolderHierarchy', { folder_id: {{ $data['folder']->id }} })" class="text-sm flex items-center gap-1 text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors bg-white dark:bg-gray-700 px-2 py-1 rounded shadow-sm border border-gray-200 dark:border-gray-600">
                                <x-heroicon-o-trash class="w-4 h-4" /> <span class="hidden sm:inline">Eliminar</span>
                            </button>
                        </div>
                    @endif
                </div>
                
                @if($data['files']->isEmpty() && (!$data['folder'] || $data['folder']->children()->count() === 0))
                    <div class="text-center py-6 bg-white dark:bg-gray-900 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                        <p class="text-sm text-gray-400 dark:text-gray-500 flex flex-col items-center gap-2">
                            <x-heroicon-o-folder-open class="w-8 h-8 text-gray-300 dark:text-gray-600" />
                            Carpeta vacía
                        </p>
                    </div>
                @elseif($data['files']->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach($data['files'] as $file)
                            <div class="group flex items-center justify-between bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-700 shadow-sm hover:border-primary-300 transition-colors">
                                <div class="flex items-center space-x-2 min-w-0">
                                    @if($file->type === 'pdf')
                                        <x-heroicon-s-document-text class="w-5 h-5 text-red-500 flex-shrink-0" />
                                    @elseif(in_array($file->type, ['word', 'excel']))
                                        <x-heroicon-s-document class="w-5 h-5 text-blue-600 flex-shrink-0" />
                                    @elseif($file->type === 'image')
                                        <x-heroicon-s-photo class="w-5 h-5 text-green-500 flex-shrink-0" />
                                    @else
                                        <x-heroicon-s-document class="w-5 h-5 text-gray-400 flex-shrink-0" />
                                    @endif
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 block truncate" title="{{ $file->name }}">{{ $file->name }}</span>
                                </div>
                                
                                <div class="flex space-x-1 flex-shrink-0 bg-white dark:bg-gray-900 pl-2">
                                    <button wire:click="mountAction('viewFile', { file: {{ $file->id }} })" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-300" title="Ver archivo">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </button>
                                    <button wire:click="mountTableAction('edit', '{{ $file->id }}')" class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400" title="Editar">
                                        <x-heroicon-o-pencil class="w-4 h-4" />
                                    </button>
                                    <button wire:click="mountTableAction('delete', '{{ $file->id }}')" class="text-gray-400 hover:text-red-600 dark:hover:text-red-400" title="Eliminar">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
        
        @if(count($displayFolders) === 0)
            <div class="text-center py-10">
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay carpetas ni archivos disponibles.</p>
            </div>
        @endif
    </div>
</div>
