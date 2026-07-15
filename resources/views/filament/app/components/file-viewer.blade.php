<div class="h-[600px] w-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden">
    @if($type === 'pdf')
        <iframe src="{{ $url }}" class="w-full h-full" frameborder="0"></iframe>
    @elseif($type === 'image')
        <img src="{{ $url }}" alt="Imagen" class="max-w-full max-h-full object-contain">
    @elseif($type === 'txt')
        <iframe src="{{ $url }}" class="w-full h-full bg-white dark:bg-gray-900" frameborder="0"></iframe>
    @else
        <div class="text-center">
            <x-heroicon-o-document class="w-16 h-16 mx-auto mb-4 text-gray-400" />
            <p class="text-gray-500 dark:text-gray-400">Vista previa no disponible para este tipo de archivo.</p>
            <a href="{{ $url }}" target="_blank" download class="mt-4 inline-block text-primary-600 hover:underline">Descargar Archivo</a>
        </div>
    @endif
</div>
