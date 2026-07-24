<?php

namespace App\Filament\Resources\FileDocumentResource\RelationManagers;

use App\Models\FileDocumentVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';
    protected static ?string $title = 'Historial de Versiones';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version_label')
            ->columns([
                Tables\Columns\TextColumn::make('version_label')
                    ->label('Versión')
                    ->sortable(),
                Tables\Columns\TextColumn::make('change_notes')
                    ->label('Notas de cambio')
                    ->wrap(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Subido por'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                // No create action here, it's done from the main resource
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (FileDocumentVersion $record) => url('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('restore')
                    ->label('Restaurar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Restaurar versión')
                    ->modalDescription('Esto reemplazará el documento actual con esta versión anterior. Se creará una copia de respaldo de tu archivo actual automáticamente.')
                    ->action(function (FileDocumentVersion $record) {
                        $document = $record->document;
                        
                        // Create a backup of the current state
                        $currentVersionNum = $document->current_version;
                        $document->versions()->create([
                            'version' => $currentVersionNum,
                            'version_label' => 'v' . $currentVersionNum . ' - ' . now()->format('Y-m-d/His'),
                            'file_path' => $document->file_path,
                            'change_notes' => 'Backup automático antes de restaurar la versión ' . $record->version_label,
                            'created_by' => auth()->id(),
                        ]);
                        
                        // Apply the restore
                        $document->update([
                            'file_path' => $record->file_path,
                            'current_version' => $currentVersionNum + 1,
                        ]);
                        
                        Notification::make()
                            ->title('Versión restaurada con éxito')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }
}
