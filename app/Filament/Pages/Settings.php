<?php

namespace App\Filament\Pages;

use App\Models\FileDocument;
use App\Models\Folder;
use App\Models\Group;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
            Action::make('reindex')
                ->label('Reindexar BD')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
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

            Action::make('export_backup')
                ->label('Respaldar Estructura (JSON)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $data = [
                        'groups' => Group::all()->toArray(),
                        'users' => User::with('groups')->get()->map(function($user) {
                            $userData = $user->toArray();
                            $userData['group_ids'] = $user->groups->pluck('id')->toArray();
                            unset($userData['groups']);
                            return $userData;
                        })->toArray(),
                        'folders' => Folder::with('groups', 'users')->get()->map(function($folder) {
                            $folderData = $folder->toArray();
                            $folderData['group_ids'] = $folder->groups->pluck('id')->toArray();
                            $folderData['user_ids'] = $folder->users->pluck('id')->toArray();
                            unset($folderData['groups']);
                            unset($folderData['users']);
                            return $folderData;
                        })->toArray(),
                        'file_documents' => FileDocument::all()->toArray(),
                    ];

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

                    DB::beginTransaction();
                    try {
                        // Limpiar tablas con cuidado (excepto archivos físicos)
                        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                        DB::table('folder_group')->truncate();
                        DB::table('folder_user')->truncate();
                        DB::table('group_user')->truncate();
                        FileDocument::truncate();
                        Folder::truncate();
                        // Mantener al admin actual, eliminar los demás
                        User::where('id', '!=', auth()->id())->delete();
                        Group::truncate();
                        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                        // Insertar Grupos
                        foreach ($backupData['groups'] as $groupData) {
                            Group::insert($groupData);
                        }

                        // Insertar Usuarios y relaciones
                        foreach ($backupData['users'] as $userData) {
                            $groupIds = $userData['group_ids'] ?? [];
                            unset($userData['group_ids']);
                            
                            if ($userData['id'] === auth()->id()) {
                                User::where('id', $userData['id'])->update($userData);
                                $user = User::find($userData['id']);
                            } else {
                                $user = User::create($userData);
                            }
                            $user->groups()->sync($groupIds);
                        }

                        // Insertar Folders y relaciones
                        foreach ($backupData['folders'] as $folderData) {
                            $groupIds = $folderData['group_ids'] ?? [];
                            $userIds = $folderData['user_ids'] ?? [];
                            unset($folderData['group_ids'], $folderData['user_ids']);
                            
                            $folder = Folder::create($folderData);
                            $folder->groups()->sync($groupIds);
                            $folder->users()->sync($userIds);
                        }

                        // Insertar FileDocuments
                        foreach ($backupData['file_documents'] as $fileData) {
                            FileDocument::insert($fileData);
                        }

                        DB::commit();

                        Notification::make()
                            ->title('Estructura restaurada exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Notification::make()
                            ->title('Error al restaurar: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
        ];
    }
}
