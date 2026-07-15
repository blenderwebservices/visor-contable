<div class="space-y-4 max-h-[400px] overflow-y-auto mb-4 p-2">
    @forelse($notes as $note)
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex justify-between items-center mb-2">
                <span class="font-bold text-sm text-gray-900 dark:text-white">{{ $note->user->name }}</span>
                <span class="text-xs text-gray-500">{{ $note->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-gray-700 dark:text-gray-300 text-sm whitespace-pre-wrap">{{ $note->content }}</p>
        </div>
    @empty
        <div class="text-center py-8 text-gray-500">
            No hay notas para este archivo. ¡Sé el primero en añadir una!
        </div>
    @endforelse
</div>
