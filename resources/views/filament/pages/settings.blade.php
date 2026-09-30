<x-filament-panels::page>
    <style>
        /* Responsive header wrapping */
        .fi-page:has(.settings-grid) .fi-header,
        .fi-page:has(.logs-container) .fi-header,
        .fi-header {
            flex-wrap: wrap !important;
            row-gap: 1rem !important;
            column-gap: 1.5rem !important;
        }

        .fi-page:has(.settings-grid) .fi-header > div:last-child,
        .fi-page:has(.logs-container) .fi-header > div:last-child,
        .fi-header > div:last-child {
            flex-shrink: 1 !important;
            flex-grow: 1 !important;
            flex-wrap: wrap !important;
            max-width: 100% !important;
        }

        .fi-page:has(.settings-grid) .fi-header .fi-ac,
        .fi-page:has(.logs-container) .fi-header .fi-ac,
        .fi-header .fi-ac {
            flex-wrap: wrap !important;
            gap: 0.5rem !important;
            row-gap: 0.5rem !important;
            justify-content: flex-start !important;
        }

        @media (min-width: 1280px) {
            .fi-page:has(.settings-grid) .fi-header .fi-ac,
            .fi-page:has(.logs-container) .fi-header .fi-ac,
            .fi-header .fi-ac {
                justify-content: flex-end !important;
            }
        }

        @media (max-width: 1279px) {
            .fi-page:has(.settings-grid) .fi-header,
            .fi-page:has(.logs-container) .fi-header,
            .fi-header {
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            .fi-page:has(.settings-grid) .fi-header > div:first-child,
            .fi-page:has(.logs-container) .fi-header > div:first-child,
            .fi-header > div:first-child {
                width: 100% !important;
            }

            .fi-page:has(.settings-grid) .fi-header > div:last-child,
            .fi-page:has(.logs-container) .fi-header > div:last-child,
            .fi-header > div:last-child {
                width: 100% !important;
                justify-content: flex-start !important;
            }

            .fi-page:has(.settings-grid) .fi-header .fi-ac,
            .fi-page:has(.logs-container) .fi-header .fi-ac,
            .fi-header .fi-ac {
                width: 100% !important;
                justify-content: flex-start !important;
            }
        }

        .fi-page:has(.settings-grid) .fi-header .fi-btn,
        .fi-page:has(.logs-container) .fi-header .fi-btn,
        .fi-header .fi-btn {
            flex-shrink: 0 !important;
            white-space: nowrap !important;
        }

        /* Pill navigation styling */
        .category-pill {
            transition: all 0.2s ease-in-out;
        }
        .category-pill:hover {
            transform: translateY(-1px);
        }
    </style>

    <div class="space-y-6">

        <!-- ===================================================================
             1. Barra de Selección de Modo (Respaldos vs Logs de Auditoría)
             =================================================================== -->
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 pb-4">
            <nav class="flex space-x-2 sm:space-x-4" aria-label="Tabs">
                <button
                    type="button"
                    wire:click="setActiveTab('backups')"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $activeTab === 'backups' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}"
                >
                    <x-heroicon-o-server-stack class="w-5 h-5" />
                    <span>Respaldos y Mantenimiento</span>
                </button>

                <button
                    type="button"
                    wire:click="setActiveTab('logs')"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ $activeTab === 'logs' ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800' }}"
                >
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                    <span>Registros de Auditoría (Logs)</span>
                    @php $totalLogs = $this->logStats['total'] ?? 0; @endphp
                    @if($totalLogs > 0)
                        <span class="ml-1 px-2 py-0.5 text-xs font-semibold rounded-full {{ $activeTab === 'logs' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                            {{ number_format($totalLogs) }}
                        </span>
                    @endif
                </button>
            </nav>

            @if($activeTab === 'logs')
                <div class="hidden sm:flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="exportLogsCsv"
                        title="Exportar a CSV"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <x-heroicon-m-arrow-down-tray class="w-4 h-4 text-emerald-600" />
                        <span>Exportar CSV</span>
                    </button>
                </div>
            @endif
        </div>

        @if($activeTab === 'backups')
            <!-- ===================================================================
                 VISTA: RESPALDOS Y MANTENIMIENTO (Original + Acceso Rápido a Logs)
                 =================================================================== -->
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

                <!-- 5. Tarjeta informativa de Auditoría Activa -->
                <div class="col-span-1 md:col-span-2 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-800 dark:to-gray-800/60 border border-amber-200 dark:border-amber-900/40 rounded-xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-xl">
                            <x-heroicon-o-shield-check class="w-8 h-8" />
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Auditoría y Trazabilidad Activa</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                Todas las operaciones de respaldo, restauración, mantenimiento, autenticación y cambios CRUD se registran automáticamente en el sistema de auditoría.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="setActiveTab('logs')"
                        class="px-4 py-2 text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-100 hover:bg-amber-200 dark:bg-amber-900/50 dark:hover:bg-amber-900/80 rounded-lg transition whitespace-nowrap"
                    >
                        Ver Registros de Auditoría &rarr;
                    </button>
                </div>

            </div>

        @else
            <!-- ===================================================================
                 VISTA: REGISTROS DE AUDITORÍA (LOGS)
                 =================================================================== -->
            <div class="logs-container space-y-6">

                <!-- 1. Menú de Categorías (Submenu de acceso a diferentes tipos de Logs) -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-3 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 px-2 mb-2">
                        Categorías de Registro
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $categories = [
                                'all' => ['label' => 'Todos los Registros', 'icon' => 'heroicon-m-queue-list'],
                                'auth' => ['label' => 'Sesiones (Login / Logout)', 'icon' => 'heroicon-m-lock-closed'],
                                'documents' => ['label' => 'Documentos y Versiones', 'icon' => 'heroicon-m-document-text'],
                                'companies' => ['label' => 'Empresas', 'icon' => 'heroicon-m-building-office-2'],
                                'users' => ['label' => 'Usuarios', 'icon' => 'heroicon-m-users'],
                                'folders' => ['label' => 'Carpetas', 'icon' => 'heroicon-m-folder'],
                                'trash' => ['label' => 'Papelera de Reciclaje', 'icon' => 'heroicon-m-trash'],
                                'backup' => ['label' => 'Respaldos y Sistema', 'icon' => 'heroicon-m-archive-box'],
                            ];
                            $counts = $this->logCategoryCounts;
                        @endphp

                        @foreach($categories as $key => $cat)
                            @php
                                $isSelected = $logCategory === $key;
                                $count = $counts[$key] ?? 0;
                            @endphp
                            <button
                                type="button"
                                wire:click="setLogCategory('{{ $key }}')"
                                class="category-pill inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 {{ $isSelected ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/30' : 'bg-gray-50 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700/60' }}"
                            >
                                @svg($cat['icon'], 'w-4 h-4 ' . ($isSelected ? 'text-white' : 'text-amber-500 dark:text-amber-400'))
                                <span>{{ $cat['label'] }}</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $isSelected ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                    {{ $count }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Tarjetas Resumen de Métricas (KPIs) -->
                @php $stats = $this->logStats; @endphp
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Eventos</span>
                            <span class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg">
                                <x-heroicon-m-clipboard-document-list class="w-4 h-4" />
                            </span>
                        </div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['total']) }}
                        </div>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">Histórico acumulado</span>
                    </div>

                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Logins Hoy</span>
                            <span class="p-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-lg">
                                <x-heroicon-m-lock-closed class="w-4 h-4" />
                            </span>
                        </div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['today_logins']) }}
                        </div>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">Accesos del día</span>
                    </div>

                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Operaciones Docs</span>
                            <span class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-lg">
                                <x-heroicon-m-document-duplicate class="w-4 h-4" />
                            </span>
                        </div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['documents_ops']) }}
                        </div>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">Creación / edición / versiones</span>
                    </div>

                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Respaldos y Sistema</span>
                            <span class="p-2 bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 rounded-lg">
                                <x-heroicon-m-archive-box class="w-4 h-4" />
                            </span>
                        </div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['backups_ops']) }}
                        </div>
                        <span class="text-[11px] text-purple-600 dark:text-purple-400 font-medium">Export / restore / diag</span>
                    </div>
                </div>

                <!-- 3. Barra de Búsqueda y Filtros Dinámicos -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

                        <!-- Buscador -->
                        <div class="lg:col-span-2 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <x-heroicon-m-magnifying-glass class="w-4 h-4" />
                            </div>
                            <input
                                type="text"
                                wire:model.live.debounce.350ms="logSearch"
                                placeholder="Buscar por usuario, IP, descripción o elemento..."
                                class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            />
                        </div>

                        <!-- Filtro por Acción -->
                        <div>
                            <select
                                wire:model.live="logAction"
                                class="w-full py-2 px-3 text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            >
                                <option value="all">Todas las Acciones</option>
                                <option value="login">Inicios de Sesión</option>
                                <option value="logout">Cierres de Sesión</option>
                                <option value="failed_login">Accesos Fallidos</option>
                                <option value="created">Creación</option>
                                <option value="updated">Modificación</option>
                                <option value="deleted">Papelera / Eliminado</option>
                                <option value="restored">Restaurado de Papelera</option>
                                <option value="force_deleted">Eliminación Definitiva</option>
                                <option value="backup_export">Exportación de Respaldo</option>
                                <option value="backup_restore">Restauración de Respaldo</option>
                                <option value="system_optimize">Optimización de BD</option>
                                <option value="audit">Auditoría / Diagnóstico</option>
                                <option value="clean_orphans">Limpieza de Huérfanos</option>
                            </select>
                        </div>

                        <!-- Filtro por Usuario -->
                        <div>
                            <select
                                wire:model.live="logUserId"
                                class="w-full py-2 px-3 text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            >
                                <option value="">Todos los Usuarios</option>
                                @foreach($this->usersList as $userItem)
                                    <option value="{{ $userItem->id }}">{{ $userItem->name }} ({{ $userItem->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por Rango de Fecha -->
                        <div>
                            <select
                                wire:model.live="logDateRange"
                                class="w-full py-2 px-3 text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            >
                                <option value="all">Todo el Historial</option>
                                <option value="today">Solo Hoy</option>
                                <option value="yesterday">Ayer</option>
                                <option value="7days">Últimos 7 días</option>
                                <option value="30days">Últimos 30 días</option>
                            </select>
                        </div>
                    </div>

                    <!-- Botón Restablecer si hay filtros aplicados -->
                    @if($logSearch || $logAction !== 'all' || $logCategory !== 'all' || $logUserId || $logDateRange !== 'all')
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-xs">
                            <span class="text-gray-500 dark:text-gray-400">Filtros activos aplicados a la consulta</span>
                            <button
                                type="button"
                                wire:click="resetLogFilters"
                                class="text-amber-600 dark:text-amber-400 hover:underline font-medium inline-flex items-center gap-1"
                            >
                                <x-heroicon-m-x-circle class="w-3.5 h-3.5" />
                                Restablecer todos los filtros
                            </button>
                        </div>
                    @endif
                </div>

                <!-- 4. Tabla de Registros de Auditoría -->
                @php $logs = $this->logs; @endphp
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300 divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50/80 dark:bg-gray-800/60 uppercase font-semibold text-gray-500 dark:text-gray-400 tracking-wider text-[11px]">
                                <tr>
                                    <th scope="col" class="py-3 px-4">Fecha y Hora</th>
                                    <th scope="col" class="py-3 px-4">Usuario</th>
                                    <th scope="col" class="py-3 px-4">Módulo</th>
                                    <th scope="col" class="py-3 px-4">Acción</th>
                                    <th scope="col" class="py-3 px-4">Descripción y Elemento</th>
                                    <th scope="col" class="py-3 px-4">IP / Dispositivo</th>
                                    <th scope="col" class="py-3 px-4 text-right">Detalles</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                                @forelse($logs as $log)
                                    <tr class="hover:bg-amber-50/30 dark:hover:bg-gray-800/40 transition">

                                        <!-- Fecha / Hora -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                                            </div>
                                            <div class="text-[11px] text-gray-400 dark:text-gray-500">
                                                {{ $log->created_at->diffForHumans() }}
                                            </div>
                                        </td>

                                        <!-- Usuario -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-amber-500 to-orange-400 text-white flex items-center justify-center font-bold text-xs uppercase shadow-xs">
                                                    {{ substr($log->user_name ?? 'S', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">
                                                        {{ $log->user_name ?? 'Sistema' }}
                                                    </div>
                                                    @if($log->user_email)
                                                        <div class="text-[10px] text-gray-400">
                                                            {{ $log->user_email }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Módulo / Categoría -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                @svg($log->category_icon, 'w-3 h-3 text-gray-500')
                                                <span>{{ $log->category_label }}</span>
                                            </span>
                                        </td>

                                        <!-- Acción -->
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            @php
                                                $actionBadgeClasses = match($log->action_color) {
                                                    'success', 'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                                                    'warning', 'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
                                                    'danger', 'rose' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
                                                    'sky', 'info' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800',
                                                    'violet' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800',
                                                    'orange' => 'bg-orange-50 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300 border border-orange-200 dark:border-orange-800',
                                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $actionBadgeClasses }}">
                                                @svg($log->action_icon, 'w-3 h-3')
                                                <span>{{ $log->action_label }}</span>
                                            </span>
                                        </td>

                                        <!-- Descripción y Elemento -->
                                        <td class="py-3 px-4 max-w-md">
                                            <div class="text-gray-900 dark:text-gray-100 font-medium">
                                                {{ $log->description }}
                                            </div>
                                            @if($log->subject_name)
                                                <div class="mt-0.5 inline-flex items-center gap-1 text-[11px] text-amber-700 dark:text-amber-300 font-mono bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.2 rounded">
                                                    <span>{{ $log->subject_name }}</span>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- IP / Dispositivo -->
                                        <td class="py-3 px-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                            <div class="font-mono text-[11px]">
                                                {{ $log->ip_address ?? '127.0.0.1' }}
                                            </div>
                                            <div class="text-[10px] text-gray-400">
                                                {{ $log->browser_info }}
                                            </div>
                                        </td>

                                        <!-- Detalles -->
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <button
                                                type="button"
                                                wire:click="viewLogDetails({{ $log->id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-md text-amber-700 dark:text-amber-300 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 transition"
                                                title="Inspeccionar detalles y cambios"
                                            >
                                                <x-heroicon-m-eye class="w-3.5 h-3.5" />
                                                <span>Detalles</span>
                                            </button>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-12 px-4 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-3">
                                                <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-full text-gray-400">
                                                    <x-heroicon-o-document-magnifying-glass class="w-8 h-8" />
                                                </div>
                                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                                    No se encontraron registros de auditoría
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm">
                                                    No hay operaciones registradas que coincidan con la categoría seleccionada o los filtros aplicados.
                                                </p>
                                                @if($logSearch || $logAction !== 'all' || $logCategory !== 'all' || $logUserId || $logDateRange !== 'all')
                                                    <button
                                                        type="button"
                                                        wire:click="resetLogFilters"
                                                        class="mt-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-500 text-white hover:bg-amber-600 transition"
                                                    >
                                                        Limpiar filtros de búsqueda
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($logs->hasPages())
                        <div class="py-3 px-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>

            </div>

            <!-- ===================================================================
                 5. Modal / Slide-Over de Inspección de Registro
                 =================================================================== -->
            @php $selectedLog = $this->selectedLog; @endphp
            @if($viewingLogId && $selectedLog)
                <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <!-- Overlay de fondo -->
                    <div
                        class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                        wire:click="closeLogDetails"
                    ></div>

                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-200 dark:border-gray-800">

                            <!-- Cabecera del Modal -->
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/40">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-amber-500/10 text-amber-600 rounded-lg">
                                        <x-heroicon-o-shield-check class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white" id="modal-title">
                                            Detalle de Registro #{{ $selectedLog->id }}
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $selectedLog->created_at->format('d/m/Y H:i:s') }} ({{ $selectedLog->created_at->diffForHumans() }})
                                        </p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    wire:click="closeLogDetails"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg p-1 transition"
                                >
                                    <x-heroicon-m-x-mark class="w-5 h-5" />
                                </button>
                            </div>

                            <!-- Contenido del Modal -->
                            <div class="p-6 space-y-5 text-xs text-gray-700 dark:text-gray-300 max-h-[75vh] overflow-y-auto">

                                <!-- Grid de Metadatos Clave -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-gray-50 dark:bg-gray-800/60 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700/60">
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-gray-400">Usuario</span>
                                        <div class="font-medium text-gray-900 dark:text-white mt-0.5">
                                            {{ $selectedLog->user_name ?? 'Sistema' }}
                                        </div>
                                        <div class="text-[10px] text-gray-500">{{ $selectedLog->user_email ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-gray-400">Categoría</span>
                                        <div class="font-medium text-gray-900 dark:text-white mt-0.5">
                                            {{ $selectedLog->category_label }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-gray-400">Operación</span>
                                        <div class="font-medium text-gray-900 dark:text-white mt-0.5">
                                            {{ $selectedLog->action_label }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-semibold uppercase text-gray-400">Dirección IP</span>
                                        <div class="font-mono text-gray-900 dark:text-white mt-0.5">
                                            {{ $selectedLog->ip_address ?? '127.0.0.1' }}
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <span class="text-[10px] font-semibold uppercase text-gray-400">Navegador / Plataforma</span>
                                        <div class="font-medium text-gray-900 dark:text-white mt-0.5 truncate" title="{{ $selectedLog->user_agent }}">
                                            {{ $selectedLog->browser_info }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Elemento Afectado -->
                                <div>
                                    <span class="text-[11px] font-semibold uppercase text-gray-400 block mb-1">Descripción del Evento</span>
                                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 font-medium text-gray-900 dark:text-white text-sm">
                                        {{ $selectedLog->description }}
                                    </div>
                                </div>

                                @if($selectedLog->subject_name || $selectedLog->subject_type)
                                    <div class="p-3 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 rounded-lg">
                                        <span class="text-[10px] font-semibold uppercase text-amber-800 dark:text-amber-400 block mb-0.5">Elemento Afectado</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $selectedLog->subject_name }}</span>
                                            @if($selectedLog->subject_id)
                                                <span class="text-xs text-gray-500 font-mono">(ID: {{ $selectedLog->subject_id }})</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Historial de Cambios (Antes vs Después) -->
                                @php
                                    $props = $selectedLog->properties ?? [];
                                    $hasDiff = isset($props['old']) && isset($props['new']) && is_array($props['old']) && is_array($props['new']);
                                @endphp

                                @if($hasDiff)
                                    <div>
                                        <span class="text-[11px] font-semibold uppercase text-gray-400 block mb-2">Comparativa de Modificaciones (Antes / Después)</span>
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                            <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-700">
                                                <thead class="bg-gray-50 dark:bg-gray-800 font-semibold text-gray-500 text-[10px] uppercase">
                                                    <tr>
                                                        <th class="py-2 px-3">Atributo</th>
                                                        <th class="py-2 px-3 text-rose-600 dark:text-rose-400">Valor Anterior</th>
                                                        <th class="py-2 px-3 text-emerald-600 dark:text-emerald-400">Valor Nuevo</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                                    @foreach($props['new'] as $field => $newVal)
                                                        @php
                                                            $oldVal = $props['old'][$field] ?? '-';
                                                            $oldValStr = is_array($oldVal) ? json_encode($oldVal, JSON_UNESCAPED_UNICODE) : (string) $oldVal;
                                                            $newValStr = is_array($newVal) ? json_encode($newVal, JSON_UNESCAPED_UNICODE) : (string) $newVal;
                                                        @endphp
                                                        <tr>
                                                            <td class="py-2 px-3 font-mono text-[11px] text-gray-900 dark:text-white font-medium">
                                                                {{ $field }}
                                                            </td>
                                                            <td class="py-2 px-3 bg-rose-50/40 dark:bg-rose-950/20 text-rose-700 dark:text-rose-300 font-mono text-[11px] break-all">
                                                                {{ $oldValStr === '' ? '(vacío)' : $oldValStr }}
                                                            </td>
                                                            <td class="py-2 px-3 bg-emerald-50/40 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-mono text-[11px] break-all">
                                                                {{ $newValStr === '' ? '(vacío)' : $newValStr }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <!-- Información Adicional / JSON Payload -->
                                @if(!empty($props))
                                    <div>
                                        <details class="group bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700/60 p-3">
                                            <summary class="font-semibold text-xs text-gray-700 dark:text-gray-300 cursor-pointer flex items-center justify-between select-none">
                                                <span>Ver Datos Técnicos (JSON)</span>
                                                <span class="text-gray-400 group-open:rotate-180 transition-transform">&darr;</span>
                                            </summary>
                                            <pre class="mt-2.5 p-3 rounded-lg bg-gray-900 text-gray-100 font-mono text-[11px] overflow-x-auto max-h-56 leading-relaxed">{{ json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </details>
                                    </div>
                                @endif

                            </div>

                            <!-- Pie del Modal -->
                            <div class="px-6 py-3.5 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-200 dark:border-gray-800 flex justify-end">
                                <button
                                    type="button"
                                    wire:click="closeLogDetails"
                                    class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-700 rounded-lg transition"
                                >
                                    Cerrar
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            @endif

        @endif

    </div>
</x-filament-panels::page>
