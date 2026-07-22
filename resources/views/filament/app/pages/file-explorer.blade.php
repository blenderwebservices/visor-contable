<x-filament-panels::page>
    <!-- Top Bar with Breadcrumbs and Views -->
    <div class="flex flex-col sm:flex-row justify-between items-center bg-white dark:bg-gray-900 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 mb-6 space-y-4 sm:space-y-0">
        <!-- Breadcrumbs -->
        <nav class="flex text-gray-500 dark:text-gray-400 text-sm font-medium" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="#" wire:click.prevent="goToPath(null, null)" class="inline-flex items-center hover:text-primary-600 dark:hover:text-primary-500 transition">
                        <x-heroicon-s-home class="w-4 h-4 mr-2" />
                        {{ __('Inicio') }}
                    </a>
                </li>
                
                @foreach($this->breadcrumbsArray as $crumb)
                <li>
                    <div class="flex items-center">
                        <x-heroicon-s-chevron-right class="w-4 h-4 text-gray-400 mx-1" />
                        <a href="#" wire:click.prevent="goToPath({{ $crumb['groupId'] ?? 'null' }}, {{ $crumb['folderId'] ?? 'null' }})" class="hover:text-primary-600 dark:hover:text-primary-500 transition truncate max-w-[150px] sm:max-w-[200px]" title="{{ $crumb['label'] }}">
                            {{ $crumb['label'] }}
                        </a>
                    </div>
                </li>
                @endforeach
            </ol>
        </nav>
        
        <!-- View Toggle -->
        <div>
            <div class="inline-flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                <button wire:click="$set('viewMode', 'cards')" class="{{ $this->viewMode === 'cards' ? 'bg-white dark:bg-gray-700 shadow-sm text-primary-600 dark:text-primary-500' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }} px-3 py-1.5 rounded-md text-sm font-medium transition flex items-center">
                    <x-heroicon-s-squares-2x2 class="w-4 h-4 mr-1.5" />
                    {{ __('Grid') }}
                </button>
                <button wire:click="$set('viewMode', 'list')" class="{{ $this->viewMode === 'list' ? 'bg-white dark:bg-gray-700 shadow-sm text-primary-600 dark:text-primary-500' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }} px-3 py-1.5 rounded-md text-sm font-medium transition flex items-center">
                    <x-heroicon-s-list-bullet class="w-4 h-4 mr-1.5" />
                    {{ __('Lista') }}
                </button>
            </div>
        </div>
    </div>

    @php
        $groups = $this->getGroups();
        $folders = $this->getFolders();
        $files = $this->getFiles();
        $isEmpty = count($groups) === 0 && count($folders) === 0 && count($files) === 0;
    @endphp

    @if($this->viewMode === 'cards')
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            
            <!-- Grupos -->
            @foreach($groups as $group)
                <div wire:click="openGroup({{ $group->id }})" class="cursor-pointer group relative bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition flex flex-col items-center text-center h-36 justify-center">
                    <div class="bg-primary-50 dark:bg-primary-900/30 p-3 rounded-full mb-3 group-hover:scale-110 transition-transform">
                        <x-heroicon-s-building-office-2 class="w-10 h-10 text-primary-600 dark:text-primary-400" />
                    </div>
                    <h3 class="font-medium text-sm text-gray-900 dark:text-white line-clamp-2 w-full px-2" title="{{ $group->name }}">{{ $group->name }}</h3>
                </div>
            @endforeach

            <!-- Carpetas -->
            @foreach($folders as $folder)
                <div wire:click="openFolder({{ $folder->id }})" class="cursor-pointer group relative bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition flex flex-col items-center text-center h-36 justify-center">
                    <div class="mb-3 group-hover:scale-110 transition-transform">
                        <x-heroicon-s-folder class="w-14 h-14 text-blue-500" />
                    </div>
                    <h3 class="font-medium text-sm text-gray-900 dark:text-white line-clamp-2 w-full px-2" title="{{ $folder->name }}">{{ $folder->name }}</h3>
                </div>
            @endforeach

            <!-- Archivos -->
            @foreach($files as $file)
                <div class="relative bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition flex flex-col h-44 group">
                    <div class="flex-1 flex items-center justify-center mb-2">
                        @if($file->type === 'pdf')
                            <x-heroicon-s-document-text class="w-14 h-14 text-red-500" />
                        @elseif(in_array($file->type, ['word', 'excel']))
                            <x-heroicon-s-document class="w-14 h-14 text-blue-600" />
                        @elseif($file->type === 'image')
                            <x-heroicon-s-photo class="w-14 h-14 text-green-500" />
                        @else
                            <x-heroicon-s-document class="w-14 h-14 text-gray-400" />
                        @endif
                    </div>
                    
                    <div class="text-center mt-auto">
                        <h3 class="font-medium text-xs text-gray-900 dark:text-white truncate w-full" title="{{ $file->name }}">{{ $file->name }}</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase mt-0.5">{{ $file->type }}</p>
                    </div>

                    <!-- Overlay Acciones -->
                    <div class="absolute inset-0 bg-white/90 dark:bg-gray-800/90 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center space-y-2 backdrop-blur-sm">
                        <x-filament::button wire:click="mountAction('viewFile', { file: {{ $file->id }} })" size="sm" color="gray" icon="heroicon-o-eye" class="w-24 justify-center">
                            {{ __('Ver') }}
                        </x-filament::button>
                        <x-filament::button wire:click="mountAction('viewNotes', { file: {{ $file->id }} })" size="sm" color="primary" icon="heroicon-o-chat-bubble-left" class="w-24 justify-center">
                            {{ __('Notas') }}
                        </x-filament::button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Vista de Lista -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">{{ __('Nombre') }}</th>
                        <th scope="col" class="px-6 py-3 font-semibold w-32">{{ __('Tipo') }}</th>
                        <th scope="col" class="px-6 py-3 font-semibold w-48 text-right">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($groups as $group)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition" wire:click="openGroup({{ $group->id }})">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center space-x-3">
                                <div class="bg-primary-50 dark:bg-primary-900/30 p-2 rounded-lg">
                                    <x-heroicon-s-building-office-2 class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                </div>
                                <span>{{ $group->name }}</span>
                            </td>
                            <td class="px-6 py-4">{{ __('Grupo') }}</td>
                            <td class="px-6 py-4 text-right"></td>
                        </tr>
                    @endforeach
                    @foreach($folders as $folder)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition" wire:click="openFolder({{ $folder->id }})">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center space-x-3">
                                <x-heroicon-s-folder class="w-8 h-8 text-blue-500" />
                                <span>{{ $folder->name }}</span>
                            </td>
                            <td class="px-6 py-4">{{ __('Carpeta') }}</td>
                            <td class="px-6 py-4 text-right"></td>
                        </tr>
                    @endforeach
                    @foreach($files as $file)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center space-x-3">
                                @if($file->type === 'pdf')
                                    <x-heroicon-s-document-text class="w-8 h-8 text-red-500" />
                                @elseif(in_array($file->type, ['word', 'excel']))
                                    <x-heroicon-s-document class="w-8 h-8 text-blue-600" />
                                @elseif($file->type === 'image')
                                    <x-heroicon-s-photo class="w-8 h-8 text-green-500" />
                                @else
                                    <x-heroicon-s-document class="w-8 h-8 text-gray-400" />
                                @endif
                                <span>{{ $file->name }}</span>
                            </td>
                            <td class="px-6 py-4 uppercase">{{ $file->type }}</td>
                            <td class="px-6 py-4 text-right space-x-1">
                                <x-filament::button wire:click="mountAction('viewFile', { file: {{ $file->id }} })" size="xs" color="gray" icon="heroicon-o-eye">{{ __('Ver') }}</x-filament::button>
                                <x-filament::button wire:click="mountAction('viewNotes', { file: {{ $file->id }} })" size="xs" color="primary" icon="heroicon-o-chat-bubble-left">{{ __('Notas') }}</x-filament::button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    @if($isEmpty)
        <div class="text-center py-20 bg-white dark:bg-gray-900 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                <x-heroicon-o-folder-open class="w-10 h-10 text-gray-400 dark:text-gray-500" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">{{ __('Esta ubicación está vacía') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No hay elementos para mostrar en este directorio.') }}</p>
        </div>
    @endif
</x-filament-panels::page>
