<x-filament-panels::page>
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center space-x-2">
            @if($this->currentFolderId)
                <x-filament::button wire:click="goUp" color="gray" icon="heroicon-o-arrow-left">
                    {{ __('Atrás') }}
                </x-filament::button>
                <h2 class="text-xl font-bold">{{ $this->currentFolder?->name }}</h2>
            @else
                <h2 class="text-xl font-bold">{{ __('Mis Carpetas') }}</h2>
            @endif
        </div>
        
        <div>
            <x-filament::button wire:click="toggleViewMode" color="secondary" icon="{{ $this->viewMode === 'cards' ? 'heroicon-o-list-bullet' : 'heroicon-o-squares-2x2' }}">
                {{ __('Vista') }}: {{ $this->viewMode === 'cards' ? __('Tarjetas') : __('Lista') }}
            </x-filament::button>
        </div>
    </div>

    @if($this->viewMode === 'cards')
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <!-- Carpetas -->
            @foreach($this->getFolders() as $folder)
                <div wire:click="openFolder({{ $folder->id }})" class="cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-xl shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition flex items-center space-x-4">
                    <x-heroicon-s-folder class="w-10 h-10 text-primary-500" />
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $folder->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Carpeta') }}</p>
                    </div>
                </div>
            @endforeach

            <!-- Archivos -->
            @foreach($this->getFiles() as $file)
                <div class="cursor-pointer bg-white dark:bg-gray-800 p-4 rounded-xl shadow border border-gray-200 dark:border-gray-700 hover:shadow-lg transition flex flex-col">
                    <div class="flex items-center space-x-4 mb-4">
                        @if($file->type === 'pdf')
                            <x-heroicon-s-document-text class="w-10 h-10 text-red-500" />
                        @elseif(in_array($file->type, ['word', 'excel']))
                            <x-heroicon-s-document class="w-10 h-10 text-blue-500" />
                        @elseif($file->type === 'image')
                            <x-heroicon-s-photo class="w-10 h-10 text-green-500" />
                        @else
                            <x-heroicon-s-document class="w-10 h-10 text-gray-500" />
                        @endif
                        <div class="flex-1 truncate">
                            <h3 class="font-semibold text-gray-900 dark:text-white truncate" title="{{ $file->name }}">{{ $file->name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ $file->type }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-auto pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                        <x-filament::button wire:click="mountAction('viewFile', { file: {{ $file->id }} })" size="sm" color="gray" icon="heroicon-o-eye">
                            {{ __('Ver') }}
                        </x-filament::button>
                        <x-filament::button wire:click="mountAction('viewNotes', { file: {{ $file->id }} })" size="sm" color="primary" icon="heroicon-o-chat-bubble-left">
                            {{ __('Notas') }}
                        </x-filament::button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Vista de Lista -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">{{ __('Nombre') }}</th>
                        <th scope="col" class="px-6 py-3">{{ __('Tipo') }}</th>
                        <th scope="col" class="px-6 py-3">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->getFolders() as $folder)
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer" wire:click="openFolder({{ $folder->id }})">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center space-x-2">
                                <x-heroicon-s-folder class="w-5 h-5 text-primary-500" />
                                <span>{{ $folder->name }}</span>
                            </td>
                            <td class="px-6 py-4">{{ __('Carpeta') }}</td>
                            <td class="px-6 py-4"></td>
                        </tr>
                    @endforeach
                    @foreach($this->getFiles() as $file)
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center space-x-2">
                                <x-heroicon-s-document class="w-5 h-5 text-gray-500" />
                                <span>{{ $file->name }}</span>
                            </td>
                            <td class="px-6 py-4 uppercase">{{ $file->type }}</td>
                            <td class="px-6 py-4 flex space-x-2">
                                <x-filament::button wire:click="mountAction('viewFile', { file: {{ $file->id }} })" size="xs" color="gray" icon="heroicon-o-eye">{{ __('Ver') }}</x-filament::button>
                                <x-filament::button wire:click="mountAction('viewNotes', { file: {{ $file->id }} })" size="xs" color="primary" icon="heroicon-o-chat-bubble-left">{{ __('Notas') }}</x-filament::button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    @if(count($this->getFolders()) === 0 && count($this->getFiles()) === 0)
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            <x-heroicon-o-folder-open class="w-16 h-16 mx-auto mb-4 text-gray-400" />
            <p class="text-lg">{{ __('Esta carpeta está vacía.') }}</p>
        </div>
    @endif
</x-filament-panels::page>
