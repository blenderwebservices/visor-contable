<div class="space-y-6 text-sm">
    <!-- Resumen de Métricas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
            <div class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Registros en BD</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ $audit['total_db_records'] }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Documentos y versiones</div>
        </div>

        <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700">
            <div class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Archivos en Disco</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ $audit['total_disk_files'] }}
            </div>
            <div class="text-xs text-gray-400 mt-1">En storage/app/public</div>
        </div>

        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
            <div class="text-xs uppercase font-semibold text-emerald-600 dark:text-emerald-400">Sincronizados</div>
            <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-300 mt-1">
                {{ $audit['synced_count'] }}
            </div>
            <div class="text-xs text-emerald-600/80 dark:text-emerald-400/80 mt-1">Enlace íntegro (OK)</div>
        </div>

        <div class="p-4 rounded-xl {{ $audit['missing_count'] > 0 ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800' : 'bg-gray-50 dark:bg-gray-800/60 border-gray-200 dark:border-gray-700' }}">
            <div class="text-xs uppercase font-semibold {{ $audit['missing_count'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400' }}">
                Faltantes en Disco
            </div>
            <div class="text-2xl font-bold {{ $audit['missing_count'] > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-gray-700 dark:text-gray-300' }} mt-1">
                {{ $audit['missing_count'] }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Enlaces rotos (sin archivo)</div>
        </div>
    </div>

    <!-- Estado Global -->
    @if ($audit['missing_count'] === 0 && $audit['orphans_count'] === 0)
        <div class="flex items-start gap-3 p-4 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200">
            <x-filament::icon icon="heroicon-m-check-circle" class="w-6 h-6 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
            <div>
                <h4 class="font-semibold text-emerald-900 dark:text-emerald-100">¡Sincronización Perfecta!</h4>
                <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">
                    Todos los documentos registrados en la base de datos cuentan con su archivo físico en disco y no existen archivos huérfanos.
                </p>
            </div>
        </div>
    @else
        @if ($audit['missing_count'] > 0)
            <div class="p-4 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-100 space-y-2">
                <div class="flex items-center gap-2 font-semibold">
                    <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" />
                    <span>Archivos Faltantes en Disco ({{ $audit['missing_count'] }})</span>
                </div>
                <p class="text-xs text-amber-700 dark:text-amber-300">
                    Los siguientes registros en la base de datos no tienen su archivo físico correspondiente en <code class="font-mono text-amber-800 dark:text-amber-200">storage/app/public/</code>. Restaura el paquete de documentos ZIP para recuperarlos.
                </p>
                <div class="max-h-40 overflow-y-auto space-y-1 pr-2 mt-2">
                    @foreach ($audit['missing_files'] as $missingPath)
                        <div class="text-xs font-mono bg-white dark:bg-gray-900 p-1.5 rounded border border-amber-200 dark:border-amber-800 text-gray-800 dark:text-gray-200">
                            {{ $missingPath }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($audit['orphans_count'] > 0)
            <div class="p-4 rounded-lg bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-100 space-y-2">
                <div class="flex items-center gap-2 font-semibold">
                    <x-filament::icon icon="heroicon-m-information-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                    <span>Archivos Huérfanos en Disco ({{ $audit['orphans_count'] }})</span>
                </div>
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    Estos archivos existen físicamente en disco pero no están vinculados a ningún documento en la base de datos (pueden ser de documentos eliminados anteriormente).
                </p>
                <div class="max-h-40 overflow-y-auto space-y-1 pr-2 mt-2">
                    @foreach ($audit['orphans_files'] as $orphanPath)
                        <div class="text-xs font-mono bg-white dark:bg-gray-900 p-1.5 rounded border border-blue-200 dark:border-blue-800 text-gray-800 dark:text-gray-200">
                            {{ $orphanPath }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
