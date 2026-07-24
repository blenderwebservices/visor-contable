@php
    $user = $getRecord();
    $groups = [];
    $unassignedFolders = [];
    
    if ($user) {
        $userGroups = $user->groups()->with(['folders.fileDocuments'])->get();
        
        foreach ($userGroups as $group) {
            // Get all assigned folders for this group
            $assignedFolders = $group->folders->keyBy('id');
            
            // Build tree
            $tree = [];
            foreach ($assignedFolders as $folder) {
                // If it has no parent, or its parent is not assigned to this group, treat it as a root for this group
                if (!$folder->parent_id || !isset($assignedFolders[$folder->parent_id])) {
                    $folder->assigned_children = collect();
                    $tree[$folder->id] = $folder;
                }
            }
            
            // Second pass: attach children to parents
            foreach ($assignedFolders as $folder) {
                if ($folder->parent_id && isset($assignedFolders[$folder->parent_id])) {
                    if (!isset($assignedFolders[$folder->parent_id]->assigned_children)) {
                        $assignedFolders[$folder->parent_id]->assigned_children = collect();
                    }
                    $assignedFolders[$folder->parent_id]->assigned_children->push($folder);
                }
            }
            
            $groups[$group->id] = [
                'group' => $group,
                'folders' => collect($tree)->values()
            ];
        }
        
        $groupFolderIds = collect($groups)->pluck('folders')->flatten()->pluck('id')->unique()->toArray();
        $directFolders = $user->folders()->with('fileDocuments')->whereNotIn('folders.id', $groupFolderIds)->get()->keyBy('id');
        
        $unassignedTree = [];
        foreach ($directFolders as $folder) {
            if (!$folder->parent_id || !isset($directFolders[$folder->parent_id])) {
                $folder->assigned_children = collect();
                $unassignedTree[$folder->id] = $folder;
            }
        }
        
        foreach ($directFolders as $folder) {
            if ($folder->parent_id && isset($directFolders[$folder->parent_id])) {
                if (!isset($directFolders[$folder->parent_id]->assigned_children)) {
                    $directFolders[$folder->parent_id]->assigned_children = collect();
                }
                $directFolders[$folder->parent_id]->assigned_children->push($folder);
            }
        }
        
        $unassignedFolders = collect($unassignedTree)->values();
    }
@endphp

@if($user)
<div class="mt-8 space-y-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Carpetas Asignadas') }}</h3>
    
    @foreach($groups as $groupId => $data)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4">
            <div class="flex items-center mb-4 space-x-3">
                <div class="bg-primary-50 dark:bg-primary-900/30 p-2 rounded-lg">
                    <x-heroicon-s-building-office-2 class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200">{{ $data['group']->name }}</h4>
            </div>
            
            <div class="space-y-4">
                @foreach($data['folders'] as $folder)
                    <div class="relative bg-gray-50 dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 flex flex-col justify-center">
                        <div class="flex items-center space-x-3 mb-2">
                            <x-heroicon-s-folder class="w-8 h-8 text-blue-500" />
                            <h3 class="font-bold text-md text-gray-900 dark:text-white" title="{{ $folder->name }}">{{ $folder->name }}</h3>
                        </div>
                        
                        <!-- Contenido recursivo -->
                        @include('filament.resources.user-resource.components.folder-tree', ['folder' => $folder])
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if(count($unassignedFolders) > 0)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4">
            <div class="flex items-center mb-4 space-x-3">
                <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded-lg">
                    <x-heroicon-s-folder-open class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                </div>
                <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200">{{ __('Sin Grupo') }}</h4>
            </div>
            
            <div class="space-y-4">
                @foreach($unassignedFolders as $folder)
                    <div class="relative bg-gray-50 dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 flex flex-col justify-center">
                        <div class="flex items-center space-x-3 mb-2">
                            <x-heroicon-s-folder class="w-8 h-8 text-blue-500" />
                            <h3 class="font-bold text-md text-gray-900 dark:text-white" title="{{ $folder->name }}">{{ $folder->name }}</h3>
                        </div>
                        
                        <!-- Contenido recursivo -->
                        @include('filament.resources.user-resource.components.folder-tree', ['folder' => $folder])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    @if(count($groups) === 0 && count($unassignedFolders) === 0)
        <div class="text-center py-10 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Este usuario no tiene carpetas asignadas.') }}</p>
        </div>
    @endif
</div>
@endif
