<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Mantenimiento del Sistema -->
        <x-filament::section icon="heroicon-o-wrench-screwdriver" heading="Mantenimiento del Sistema" description="Herramientas para optimizar y asegurar el buen funcionamiento del portal.">
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Si notas que el sistema está lento, o si se han hecho cambios estructurales que no se reflejan (rutas, vistas o configuraciones), reindexar la base de datos limpiará la caché y optimizará los recursos.
                </p>
                <div>
                    {{ $this->getAction('reindex') }}
                </div>
            </div>
        </x-filament::section>

        <!-- Respaldo y Restauración -->
        <x-filament::section icon="heroicon-o-server-stack" heading="Respaldo y Restauración" description="Respalda la estructura completa de tu base de datos (Grupos, Carpetas, Usuarios y Registros de Archivos).">
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Utiliza esta herramienta para guardar un punto de control en formato JSON. Ten en cuenta que esto <strong>no respalda los archivos físicos</strong> (PDFs, Excel), únicamente la estructura de la base de datos y sus relaciones.
                </p>
                <div class="flex flex-col space-y-3 sm:flex-row sm:space-y-0 sm:space-x-3">
                    {{ $this->getAction('export_backup') }}
                    {{ $this->getAction('import_backup') }}
                </div>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
