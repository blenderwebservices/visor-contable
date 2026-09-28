<?php

namespace App\Filament\Pages;

use App\Models\Annotation;
use App\Models\FileDocument;
use App\Models\FileDocumentVersion;
use App\Models\Folder;
use App\Models\Group;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use ZipArchive;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationLabel = 'Ajustes';
    protected static ?string $title = 'Ajustes del Sistema';
    protected static ?string $slug = 'ajustes';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    protected function getHeaderActions(): array
    {
        return [
            // --- 1. Mantenimiento y Auditoría ---
            Action::make('reindex')
                ->label('Reindexar BD')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->size('sm')
                ->requiresConfirmation()
                ->modalHeading('¿Reindexar y optimizar la Base de Datos?')
                ->modalDescription('Esta acción limpiará la caché y optimizará las consultas y rutas del sistema.')
                ->action(function () {
                    Artisan::call('optimize:clear');
                    Notification::make()
                        ->title('Sistema optimizado y reindexado correctamente')
                        ->success()
                        ->send();
                }),

            Action::make('verify_integrity')
                ->label('Auditar Sincronización')
                ->icon('heroicon-o-shield-check')
                ->color('info')
                ->size('sm')
                ->modalHeading('Auditoría de Sincronización de Archivos')
                ->modalDescription('Diagnóstico en tiempo real entre la Base de Datos y el almacenamiento físico en disco.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar')
                ->modalContent(fn () => view('filament.pages.modals.integrity-audit', [
                    'audit' => $this->auditIntegrity()
                ])),

            Action::make('clean_orphans')
                ->label('Limpiar Huérfanos')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->size('sm')
                ->requiresConfirmation()
                ->modalHeading('¿Eliminar archivos huérfanos del disco?')
                ->modalDescription('Esta acción eliminará de forma irreversible los archivos físicos en disco que no pertenecen a ningún documento en la base de datos.')
                ->action(function () {
                    $audit = $this->auditIntegrity();
                    if ($audit['orphans_count'] === 0) {
                        Notification::make()
                            ->title('No hay archivos huérfanos para limpiar')
                            ->info()
                            ->send();
                        return;
                    }

                    $disk = Storage::disk('public');
                    $deleted = 0;
                    foreach ($audit['orphans_files'] as $orphan) {
                        if ($disk->delete($orphan)) {
                            $deleted++;
                        }
                    }

                    Notification::make()
                        ->title("Se eliminaron {$deleted} archivo(s) huérfano(s) exitosamente.")
                        ->success()
                        ->send();
                }),

            // --- 2. Respaldo Completo (Todo en Uno: Estructura + Documentos) ---
            Action::make('export_full_backup')
                ->label('Respaldar Todo (ZIP)')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('success')
                ->size('sm')
                ->action(function () {
                    @set_time_limit(300);
                    $structure = $this->getStructureData();
                    $tempZipPath = tempnam(sys_get_temp_dir(), 'full_backup_') . '.zip';

                    $this->createDocumentsZip($tempZipPath, $structure);

                    $filename = 'respaldo_completo_' . date('Y_m_d_His') . '.zip';

                    return response()->streamDownload(function () use ($tempZipPath) {
                        readfile($tempZipPath);
                        @unlink($tempZipPath);
                    }, $filename, ['Content-Type' => 'application/zip']);
                }),

            Action::make('import_full_backup')
                ->label('Restaurar Todo (ZIP)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('danger')
                ->size('sm')
                ->requiresConfirmation()
                ->modalHeading('Restauración Completa del Sistema (Todo en Uno)')
                ->modalDescription('ADVERTENCIA CRÍTICA: Esto sobrescribirá la estructura actual de la Base de Datos e integrará los archivos físicos desde el archivo ZIP. Esta acción no se puede deshacer. ¿Deseas continuar?')
                ->form([
                    FileUpload::make('full_backup_file')
                        ->label('Paquete Completo (.zip)')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/octet-stream'])
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    @set_time_limit(300);
                    /** @var TemporaryUploadedFile $file */
                    $file = $data['full_backup_file'];
                    $zipPath = $file->getRealPath();

                    try {
                        $result = $this->extractDocumentsZip($zipPath);

                        if (empty($result['structure_data'])) {
                            Notification::make()
                                ->title('El archivo ZIP no contiene la estructura del sistema (structure.json)')
                                ->body('Si se trata de un paquete solo de documentos, utiliza la opción "Restaurar Documentos (ZIP)".')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Restaurar estructura de la base de datos
                        $this->restoreStructureData($result['structure_data'], auth()->id());

                        // Auditar sincronización post-restauración
                        $audit = $this->auditIntegrity();

                        Notification::make()
                            ->title('Restauración Completa exitosa')
                            ->body("Estructura de base de datos restaurada. Archivos físicos extraídos: {$result['extracted_count']}. Documentos sincronizados: {$audit['synced_count']} / {$audit['total_db_records']}.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error en la restauración completa: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // --- 3. Estructura de Base de Datos (JSON) ---
            Action::make('export_backup')
                ->label('Respaldar Estructura (JSON)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->size('sm')
                ->action(function () {
                    $data = $this->getStructureData();
                    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    $filename = 'respaldo_estructura_' . date('Y_m_d_His') . '.json';

                    return response()->streamDownload(function () use ($json) {
                        echo $json;
                    }, $filename, ['Content-Type' => 'application/json']);
                }),

            Action::make('import_backup')
                ->label('Restaurar Estructura (JSON)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('danger')
                ->size('sm')
                ->requiresConfirmation()
                ->modalHeading('Restaurar Estructura desde JSON')
                ->modalDescription('ADVERTENCIA: Esto borrará la estructura actual (registros en la BD) y restaurará la del archivo. Los archivos físicos no serán eliminados. ¿Estás seguro de proceder?')
                ->form([
                    FileUpload::make('backup_file')
                        ->label('Archivo JSON')
                        ->acceptedFileTypes(['application/json'])
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    /** @var TemporaryUploadedFile $file */
                    $file = $data['backup_file'];
                    $jsonContent = file_get_contents($file->getRealPath());
                    $backupData = json_decode($jsonContent, true);

                    if (!$backupData || !isset($backupData['groups'], $backupData['users'], $backupData['folders'], $backupData['file_documents'])) {
                        Notification::make()
                            ->title('Archivo JSON inválido')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $this->restoreStructureData($backupData, auth()->id());

                        Notification::make()
                            ->title('Estructura restaurada exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al restaurar: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // --- 4. Documentos Físicos (Archivos ZIP) ---
            Action::make('export_documents')
                ->label('Respaldar Documentos (ZIP)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->size('sm')
                ->action(function () {
                    @set_time_limit(300);
                    $tempZipPath = tempnam(sys_get_temp_dir(), 'docs_backup_') . '.zip';

                    $info = $this->createDocumentsZip($tempZipPath);

                    $filename = 'respaldo_documentos_' . date('Y_m_d_His') . '.zip';

                    return response()->streamDownload(function () use ($tempZipPath) {
                        readfile($tempZipPath);
                        @unlink($tempZipPath);
                    }, $filename, ['Content-Type' => 'application/zip']);
                }),

            Action::make('import_documents')
                ->label('Restaurar Documentos (ZIP)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->size('sm')
                ->requiresConfirmation()
                ->modalHeading('Restaurar Archivos Físicos de Documentos')
                ->modalDescription('Se descomprimirán los archivos en el almacenamiento y se sincronizarán con los registros existentes en la base de datos.')
                ->form([
                    FileUpload::make('documents_file')
                        ->label('Archivo ZIP de Documentos')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/octet-stream'])
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    @set_time_limit(300);
                    /** @var TemporaryUploadedFile $file */
                    $file = $data['documents_file'];
                    $zipPath = $file->getRealPath();

                    try {
                        $result = $this->extractDocumentsZip($zipPath);
                        $audit = $result['sync'];

                        $message = "Se extrajeron {$result['extracted_count']} archivo(s) físico(s). Sincronizados con la BD: {$audit['synced_count']} de {$audit['total_db_records']}.";
                        if ($audit['missing_count'] > 0) {
                            $message .= " (Aviso: Aún faltan {$audit['missing_count']} archivo(s) por sincronizar).";
                        }

                        Notification::make()
                            ->title('Documentos físicos restaurados')
                            ->body($message)
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al restaurar documentos: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    /**
     * Obtiene la estructura completa de la Base de Datos para respaldo.
     */
    public function getStructureData(): array
    {
        return [
            'groups' => Group::all()->toArray(),
            'users' => User::all()->map(function($user) {
                $userData = $user->makeVisible(['password', 'remember_token'])->toArray();
                $userData['group_ids'] = $user->groups->pluck('id')->toArray();
                $userData['supervised_group_ids'] = $user->supervisedGroups->pluck('id')->toArray();
                unset($userData['groups'], $userData['supervised_groups']);
                return $userData;
            })->toArray(),
            'folders' => Folder::with('groups', 'users')->get()->map(function($folder) {
                $folderData = $folder->toArray();
                $folderData['group_ids'] = $folder->groups->pluck('id')->toArray();
                $folderData['user_ids'] = $folder->users->pluck('id')->toArray();
                unset($folderData['groups'], $folderData['users']);
                return $folderData;
            })->toArray(),
            'file_documents' => FileDocument::all()->toArray(),
            'file_document_versions' => FileDocumentVersion::all()->toArray(),
            'annotations' => Annotation::all()->toArray(),
            'users_folders_shared' => DB::table('users_folders_shared')->get()->map(fn($item) => (array) $item)->toArray(),
            'supervisor_group_assignments' => DB::table('supervisor_group_assignments')->get()->map(fn($item) => (array) $item)->toArray(),
        ];
    }

    /**
     * Restaura la estructura relacional en la Base de Datos con soporte SQLite / MySQL.
     */
    public function restoreStructureData(array $backupData, ?int $currentUserId): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            DB::beginTransaction();

            // Limpiar tablas relacionales con delete() (compatible con MySQL sin error 1701 y con SQLite)
            DB::table('folder_group')->delete();
            DB::table('folder_user')->delete();
            DB::table('group_user')->delete();
            DB::table('users_folders_shared')->delete();
            DB::table('supervisor_group_assignments')->delete();
            Annotation::query()->forceDelete();
            FileDocumentVersion::query()->delete();
            FileDocument::query()->forceDelete();
            Folder::query()->forceDelete();

            // Eliminar físicamente los demás usuarios para evitar conflictos de claves UNIQUE (email, username)
            if ($currentUserId) {
                User::where('id', '!=', $currentUserId)->forceDelete();
            } else {
                User::query()->forceDelete();
            }
            Group::query()->delete();

            // Insertar Grupos preservando IDs
            foreach ($backupData['groups'] as $groupData) {
                Group::forceCreate($groupData);
            }

            // Insertar Usuarios y relaciones
            foreach ($backupData['users'] as $userData) {
                $groupIds = $userData['group_ids'] ?? [];
                $supervisedGroupIds = $userData['supervised_group_ids'] ?? [];
                unset($userData['group_ids'], $userData['supervised_group_ids']);

                if (empty($userData['password'])) {
                    if ($currentUserId && $userData['id'] === $currentUserId) {
                        unset($userData['password']);
                    } else {
                        $userData['password'] = Hash::make('password');
                    }
                }

                if ($currentUserId && $userData['id'] === $currentUserId) {
                    $user = User::find($currentUserId);
                    if ($user) {
                        $user->forceFill($userData)->save();
                    }
                } else {
                    $user = User::withTrashed()->where('id', $userData['id'])->first();
                    if ($user) {
                        $user->restore();
                        $user->forceFill($userData)->save();
                    } else {
                        $user = User::forceCreate($userData);
                    }
                }

                if ($user) {
                    if (!empty($groupIds)) {
                        $user->groups()->sync($groupIds);
                    }
                    if (!empty($supervisedGroupIds) && method_exists($user, 'supervisedGroups')) {
                        $user->supervisedGroups()->sync($supervisedGroupIds);
                    }
                }
            }

            // Insertar Folders y relaciones preservando IDs
            foreach ($backupData['folders'] as $folderData) {
                $groupIds = $folderData['group_ids'] ?? [];
                $userIds = $folderData['user_ids'] ?? [];
                unset($folderData['group_ids'], $folderData['user_ids'], $folderData['shared_users']);

                $folder = Folder::forceCreate($folderData);
                if (!empty($groupIds)) {
                    $folder->groups()->sync($groupIds);
                }
                if (!empty($userIds)) {
                    $folder->users()->sync($userIds);
                }
            }

            // Insertar FileDocuments preservando IDs y atributos JSON
            foreach ($backupData['file_documents'] as $fileData) {
                if (isset($fileData['attributes']) && is_string($fileData['attributes'])) {
                    $decoded = json_decode($fileData['attributes'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $fileData['attributes'] = $decoded;
                    }
                }
                FileDocument::forceCreate($fileData);
            }

            // Insertar versiones si existen en el respaldo
            if (!empty($backupData['file_document_versions'])) {
                foreach ($backupData['file_document_versions'] as $versionData) {
                    FileDocumentVersion::forceCreate($versionData);
                }
            }

            // Insertar anotaciones si existen en el respaldo
            if (!empty($backupData['annotations'])) {
                foreach ($backupData['annotations'] as $annotationData) {
                    Annotation::forceCreate($annotationData);
                }
            }

            // Restaurar permisos compartidos si existen
            if (!empty($backupData['users_folders_shared'])) {
                foreach ($backupData['users_folders_shared'] as $shared) {
                    DB::table('users_folders_shared')->insertOrIgnore((array) $shared);
                }
            }

            // Restaurar supervisores si existen
            if (!empty($backupData['supervisor_group_assignments'])) {
                foreach ($backupData['supervisor_group_assignments'] as $assignment) {
                    DB::table('supervisor_group_assignments')->insertOrIgnore((array) $assignment);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Empaqueta los archivos físicos en un archivo ZIP con manifiesto de sincronía.
     */
    public function createDocumentsZip(string $zipPath, ?array $structureData = null): array
    {
        $disk = Storage::disk('public');
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('No se pudo inicializar el archivo ZIP.');
        }

        $docFiles = FileDocument::pluck('file_path')->filter()->unique();
        $versionFiles = FileDocumentVersion::pluck('file_path')->filter()->unique();
        $referencedFiles = $docFiles->merge($versionFiles)->unique();
        $diskFiles = collect($disk->allFiles('documents'));
        $allFiles = $referencedFiles->merge($diskFiles)->unique();

        $manifestFiles = [];
        $totalBytes = 0;
        $addedCount = 0;

        foreach ($allFiles as $relativePath) {
            if ($disk->exists($relativePath)) {
                $fullPath = $disk->path($relativePath);
                $size = filesize($fullPath);
                $totalBytes += $size;

                $zip->addFile($fullPath, $relativePath);
                $addedCount++;

                $manifestFiles[] = [
                    'file_path' => $relativePath,
                    'size' => $size,
                    'sha256' => hash_file('sha256', $fullPath),
                ];
            }
        }

        $manifest = [
            'type' => $structureData ? 'full_backup' : 'documents_backup',
            'created_at' => now()->toIso8601String(),
            'total_files' => $addedCount,
            'total_bytes' => $totalBytes,
            'has_structure' => $structureData !== null,
            'files' => $manifestFiles,
        ];

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($structureData !== null) {
            $zip->addFromString('structure.json', json_encode($structureData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $zip->close();

        return [
            'total_files' => $addedCount,
            'total_bytes' => $totalBytes,
        ];
    }

    /**
     * Extrae archivos de un ZIP a storage/app/public/ y devuelve datos de sincronía.
     */
    public function extractDocumentsZip(string $zipFilePath): array
    {
        $disk = Storage::disk('public');
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) !== true) {
            throw new \Exception('No se pudo abrir el archivo ZIP.');
        }

        $extractedCount = 0;
        $manifestData = null;
        $structureData = null;

        $manifestIndex = $zip->locateName('manifest.json');
        if ($manifestIndex !== false) {
            $manifestContent = $zip->getFromIndex($manifestIndex);
            $manifestData = json_decode($manifestContent, true);
        }

        $structureIndex = $zip->locateName('structure.json');
        if ($structureIndex !== false) {
            $structureContent = $zip->getFromIndex($structureIndex);
            $structureData = json_decode($structureContent, true);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            // Ignorar directorios, metadata de macOS y JSONs de control
            if (
                str_ends_with($filename, '/') ||
                str_starts_with($filename, '__MACOSX/') ||
                basename($filename) === '.DS_Store' ||
                $filename === 'manifest.json' ||
                $filename === 'structure.json' ||
                str_contains($filename, '..')
            ) {
                continue;
            }

            $stream = $zip->getStream($filename);
            if ($stream) {
                $disk->put($filename, $stream);
                fclose($stream);
                $extractedCount++;
            }
        }

        $zip->close();

        return [
            'extracted_count' => $extractedCount,
            'manifest' => $manifestData,
            'structure_data' => $structureData,
            'sync' => $this->auditIntegrity(),
        ];
    }

    /**
     * Realiza un diagnóstico de integridad entre los registros de la BD y los archivos en disco.
     */
    public function auditIntegrity(): array
    {
        $disk = Storage::disk('public');
        $dbDocs = FileDocument::all();
        $dbVersions = FileDocumentVersion::all();

        $referencedFiles = $dbDocs->pluck('file_path')
            ->merge($dbVersions->pluck('file_path'))
            ->filter()
            ->unique()
            ->values();

        $diskFiles = collect($disk->allFiles('documents'))->values();

        $synced = $referencedFiles->filter(fn($f) => $disk->exists($f))->values();
        $missing = $referencedFiles->reject(fn($f) => $disk->exists($f))->values();
        $orphans = $diskFiles->reject(fn($f) => $referencedFiles->contains($f))->values();

        return [
            'total_db_records' => $referencedFiles->count(),
            'total_disk_files' => $diskFiles->count(),
            'synced_count' => $synced->count(),
            'missing_count' => $missing->count(),
            'missing_files' => $missing->all(),
            'orphans_count' => $orphans->count(),
            'orphans_files' => $orphans->all(),
        ];
    }
}
