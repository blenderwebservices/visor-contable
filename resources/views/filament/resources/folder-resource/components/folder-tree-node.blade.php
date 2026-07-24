@props(['folder'])

<div class="ml-4 mt-2 border-l-2 border-gray-100 dark:border-gray-700 pl-4">
    <!-- Subcarpetas -->
    @if(isset($folder->assigned_children) && $folder->assigned_children->count() > 0)
        <div class="space-y-2 mt-2">
            @foreach($folder->assigned_children as $childFolder)
                <div class="bg-gray-50/50 dark:bg-gray-800/50 p-2 rounded-lg border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-s-folder class="w-5 h-5 text-blue-400" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $childFolder->name }}
                                @if($childFolder->groups && $childFolder->groups->count() > 0)
                                    <span class="text-xs font-normal text-gray-400">({{ $childFolder->groups->pluck('name')->join(', ') }})</span>
                                @endif
                            </span>
                        </div>
                        
                        <div class="flex space-x-1">
                            <x-filament::button wire:click="mountTableAction('view', '{{ $childFolder->id }}')" size="xs" color="gray" icon="heroicon-o-eye" class="px-1 py-0" title="Visualizar"></x-filament::button>
                            <x-filament::button wire:click="mountTableAction('edit', '{{ $childFolder->id }}')" size="xs" color="primary" icon="heroicon-o-pencil" class="px-1 py-0" title="Editar"></x-filament::button>
                            <x-filament::button wire:click="mountTableAction('delete', '{{ $childFolder->id }}')" size="xs" color="danger" icon="heroicon-o-trash" class="px-1 py-0" title="Eliminar"></x-filament::button>
                        </div>
                    </div>
                    @include('filament.resources.folder-resource.components.folder-tree-node', ['folder' => $childFolder])
                </div>
            @endforeach
        </div>
    @endif

    <!-- Archivos -->
    @if(isset($folder->fileDocuments) && $folder->fileDocuments->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 mt-2">
            @foreach($folder->fileDocuments as $file)
                <div class="flex items-center space-x-2 bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-700 shadow-sm">
                    @if($file->type === 'pdf')
                        <x-heroicon-s-document-text class="w-5 h-5 text-red-500 flex-shrink-0" />
                    @elseif(in_array($file->type, ['word', 'excel']))
                        <x-heroicon-s-document class="w-5 h-5 text-blue-600 flex-shrink-0" />
                    @elseif($file->type === 'image')
                        <x-heroicon-s-photo class="w-5 h-5 text-green-500 flex-shrink-0" />
                    @else
                        <x-heroicon-s-document class="w-5 h-5 text-gray-400 flex-shrink-0" />
                    @endif
                    <div class="flex-1 min-w-0">
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 block truncate" title="{{ $file->name }}">{{ $file->name }}</span>
                    </div>
                    <div class="flex-shrink-0">
                        <x-filament::button wire:click="mountAction('viewFile', { file: {{ $file->id }} })" size="xs" color="gray" icon="heroicon-o-eye" class="px-1 py-0" title="Ver archivo"></x-filament::button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
