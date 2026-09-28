<x-filament-panels::page>
    <style>
        /* ================================================================
           Barra de botones principal responsiva para Ajustes del Sistema
           ================================================================ */

        /* Permite que la cabecera envuelva elementos en lugar de desbordar la pantalla */
        .fi-page:has(.settings-grid) .fi-header,
        .fi-header {
            flex-wrap: wrap !important;
            row-gap: 1rem !important;
            column-gap: 1.5rem !important;
        }

        /* Evita que el contenedor de acciones se congele sin encoger (anula shrink-0) */
        .fi-page:has(.settings-grid) .fi-header > div:last-child,
        .fi-header > div:last-child {
            flex-shrink: 1 !important;
            flex-grow: 1 !important;
            flex-wrap: wrap !important;
            max-width: 100% !important;
        }

        /* Permite que el grupo de botones envuelva en múltiples líneas si no caben */
        .fi-page:has(.settings-grid) .fi-header .fi-ac,
        .fi-header .fi-ac {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
            row-gap: 0.5rem !important;
            justify-content: flex-start !important;
        }

        /* En pantallas grandes (>=1280px), alinear a la derecha pero permitiendo salto de línea */
        @media (min-width: 1280px) {
            .fi-page:has(.settings-grid) .fi-header .fi-ac,
            .fi-header .fi-ac {
                justify-content: flex-end !important;
            }
        }

        /* En pantallas medianas o menores, organizar en bloque con ancho completo */
        @media (max-width: 1279px) {
            .fi-page:has(.settings-grid) .fi-header,
            .fi-header {
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            .fi-page:has(.settings-grid) .fi-header > div:first-child,
            .fi-header > div:first-child {
                width: 100% !important;
            }

            .fi-page:has(.settings-grid) .fi-header > div:last-child,
            .fi-header > div:last-child {
                width: 100% !important;
                justify-content: flex-start !important;
            }

            .fi-page:has(.settings-grid) .fi-header .fi-ac,
            .fi-header .fi-ac {
                width: 100% !important;
                justify-content: flex-start !important;
            }
        }

        /* Los botones no deben encoger su texto individual */
        .fi-page:has(.settings-grid) .fi-header .fi-btn,
        .fi-header .fi-btn {
            flex-shrink: 0 !important;
            white-space: nowrap !important;
        }
    </style>

    <div class="settings-grid grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- 1. Respaldo Completo (Todo en Uno) -->
        <x-filament::section icon="heroicon-o-archive-box-arrow-down" heading="Respaldo Completo (Todo en Uno)" description="Empaqueta tanto la estructura de la base de datos como todos los documentos físicos en un único archivo ZIP.">
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Es la opción recomendada para recuperación ante desastres o migración de servidor. Incluye la estructura relacional completa (<code class="font-mono text-xs">structure.json</code>), todos los archivos físicos y un manifiesto de verificación.
                </p>
                <div class="flex flex-wrap gap-3">
                    {{ $this->getAction('export_full_backup') }}
                    {{ $this->getAction('import_full_backup') }}
                </div>
            </div>
        </x-filament::section>

        <!-- 2. Mantenimiento y Auditoría de Sincronización -->
        <x-filament::section icon="heroicon-o-wrench-screwdriver" heading="Mantenimiento y Diagnóstico" description="Optimiza el sistema y audita la coherencia entre la base de datos y los archivos en disco.">
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Revisa en tiempo real si todos los documentos registrados en la BD tienen su archivo físico correspondiente en disco, detecta enlaces rotos o archivos huérfanos y limpia la caché.
                </p>
                <div class="flex flex-wrap gap-3">
                    {{ $this->getAction('verify_integrity') }}
                    {{ $this->getAction('clean_orphans') }}
                    {{ $this->getAction('reindex') }}
                </div>
            </div>
        </x-filament::section>

        <!-- 3. Estructura de la Base de Datos (JSON) -->
        <x-filament::section icon="heroicon-o-server-stack" heading="Estructura de Base de Datos (JSON)" description="Respalda únicamente las tablas, carpetas, usuarios, grupos y permisos.">
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Genera un archivo JSON ligero para guardar puntos de control de la organización del sistema. <strong>No incluye los archivos físicos</strong> (PDFs, Excels), solo sus metadatos y relaciones.
                </p>
                <div class="flex flex-wrap gap-3">
                    {{ $this->getAction('export_backup') }}
                    {{ $this->getAction('import_backup') }}
                </div>
            </div>
        </x-filament::section>

        <!-- 4. Documentos Físicos (ZIP) -->
        <x-filament::section icon="heroicon-o-document-duplicate" heading="Documentos Físicos (ZIP)" description="Respalda los archivos binarios reales (PDFs, Excels, imágenes) almacenados en disco.">
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Empaqueta todos los archivos de almacenamiento físico con un <strong>manifiesto de integridad</strong> (<code class="font-mono text-xs">manifest.json</code>) que permite validarlos y sincronizarlos con la estructura activa.
                </p>
                <div class="flex flex-wrap gap-3">
                    {{ $this->getAction('export_documents') }}
                    {{ $this->getAction('import_documents') }}
                </div>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
