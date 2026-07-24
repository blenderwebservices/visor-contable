@php
    $groupedByFolder = $records->groupBy(function ($record) {
        return $record->folder ? $record->folder->id : 'unassigned';
    });
@endphp

<div class="p-4 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
    <div class="space-y-6">
        @foreach($groupedByFolder as $folderId => $files)
            @php
                $firstFile = $files->first();
                $folder = $firstFile->folder;
                $folderName = $folder ? $folder->name : 'Sin Carpeta';
                $groupNames = $folder && $folder->groups ? $folder->groups->pluck('name')->join(', ') : '';
            @endphp
            
            <div class="relative bg-gray-50 dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center space-x-3 mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                    <x-heroicon-s-folder class="w-6 h-6 text-blue-500" />
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white flex-1">
                        {{ $folderName }}
                        @if($groupNames)
                            <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-1">({{ $groupNames }})</span>
                        @endif
                    </h3>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach($files as $file)
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
            </div>
        @endforeach
        
        @if($records->isEmpty())
            <div class="text-center py-10">
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay archivos disponibles en esta vista.</p>
            </div>
        @endif
    </div>
</div>
