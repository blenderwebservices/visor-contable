<div class="flex flex-wrap gap-4 p-4">
    @foreach($records as $record)
        @php
            $groupNames = $record->folder && $record->folder->groups ? $record->folder->groups->pluck('name')->join(', ') : '';
        @endphp
        <div class="group relative bg-white dark:bg-gray-800 p-3 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all flex items-center w-full sm:w-[calc(50%-0.5rem)] md:w-[calc(33.333%-0.75rem)] lg:w-[calc(25%-0.75rem)] xl:w-[calc(20%-0.8rem)]">
            
            <!-- Icono -->
            <div class="w-10 h-10 flex-shrink-0 bg-gray-50 dark:bg-gray-900/50 rounded-lg flex items-center justify-center mr-3">
                @if($record->type === 'pdf')
                    <x-heroicon-s-document-text class="w-6 h-6 text-red-500" />
                @elseif(in_array($record->type, ['word', 'excel']))
                    <x-heroicon-s-document class="w-6 h-6 text-blue-600" />
                @elseif($record->type === 'image')
                    <x-heroicon-s-photo class="w-6 h-6 text-green-500" />
                @else
                    <x-heroicon-s-document class="w-6 h-6 text-gray-400" />
                @endif
            </div>
            
            <!-- Info -->
            <div class="flex-1 min-w-0">
                <h3 class="font-medium text-sm text-gray-900 dark:text-white truncate" title="{{ $record->name }}">
                    {{ $record->name }}
                </h3>
                @if($groupNames)
                    <p class="text-[10px] text-gray-500 truncate mt-0.5">({{ $groupNames }})</p>
                @endif
            </div>
            
            <!-- Acciones -->
            <div class="flex items-center space-x-1 ml-2">
                <button wire:click="mountAction('viewFile', { file: {{ $record->getKey() }} })" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition" title="Ver archivo">
                    <x-heroicon-o-eye class="w-4 h-4" />
                </button>
                <button wire:click="mountTableAction('edit', '{{ $record->getKey() }}')" class="p-1.5 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/30 rounded-md transition" title="Editar">
                    <x-heroicon-o-pencil class="w-4 h-4" />
                </button>
                <button wire:click="mountTableAction('delete', '{{ $record->getKey() }}')" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition" title="Eliminar">
                    <x-heroicon-o-trash class="w-4 h-4" />
                </button>
            </div>
            
        </div>
    @endforeach
    
    @if($records->isEmpty())
        <div class="w-full text-center py-10">
            <p class="text-gray-500 dark:text-gray-400">No hay archivos para mostrar.</p>
        </div>
    @endif
</div>
