@props(['folder'])

<div class="ml-4 mt-2 border-l-2 border-gray-100 dark:border-gray-700 pl-4">
    <!-- Subcarpetas -->
    @if(isset($folder->assigned_children) && $folder->assigned_children->count() > 0)
        <div class="space-y-2 mt-2">
            @foreach($folder->assigned_children as $childFolder)
                <div class="bg-gray-50/50 dark:bg-gray-800/50 p-2 rounded-lg border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-s-folder class="w-5 h-5 text-blue-400" />
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $childFolder->name }}</span>
                    </div>
                    @include('filament.resources.user-resource.components.folder-tree', ['folder' => $childFolder])
                </div>
            @endforeach
        </div>
    @endif

    <!-- Archivos -->
    @if($folder->fileDocuments->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 mt-2">
            @foreach($folder->fileDocuments as $file)
                <button type="button" wire:click="mountAction('viewFile', { file: {{ $file->id }} })" class="flex items-center space-x-2 bg-white dark:bg-gray-900 p-2 rounded border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition cursor-pointer text-left w-full">
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
                </button>
            @endforeach
        </div>
    @endif
</div>
