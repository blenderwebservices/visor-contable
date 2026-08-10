<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileDocumentResource\Pages;
use App\Filament\Resources\FileDocumentResource\RelationManagers;
use App\Models\FileDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FileDocumentResource extends Resource
{
    protected static ?string $model = FileDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file_path')
                    ->required()
                    ->directory('documents')
                    ->maxSize(51200) // 50MB
                    ->columnSpanFull()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                            $filename = pathinfo($state->getClientOriginalName(), PATHINFO_FILENAME);
                            $set('name', \Illuminate\Support\Str::slug($filename, '-'));
                        }
                    }),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'pdf' => 'PDF',
                        'word' => 'Word',
                        'excel' => 'Excel',
                        'image' => 'Image',
                        'txt' => 'Text',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\Select::make('folder_id')
                    ->relationship('folder', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('parent_id')
                            ->relationship('parent', 'name') // The relationship on Folder model is 'parent'
                            ->label('Parent Folder')
                            ->searchable()
                            ->preload()
                            ->default(null),
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_downloadable')
                    ->label('Descargable')
                    ->default(false),
                Forms\Components\Toggle::make('is_printable')
                    ->label('Imprimible')
                    ->default(false),
                Forms\Components\KeyValue::make('attributes')
                    ->keyLabel('Attribute Name')
                    ->valueLabel('Attribute Value')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('folder.name')
                    ->label('Folder')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Tables\Grouping\Group::make('folder.name')
                    ->label('Folder'),
                Tables\Grouping\Group::make('folder_id')
                    ->label('Grupo (Empresa)')
                    ->getTitleFromRecordUsing(fn ($record) => $record->folder?->groups->pluck('name')->join(', ') ?: 'Sin Grupo'),
            ])
            ->content(function (\Filament\Resources\Pages\ListRecords $livewire) {
                if (property_exists($livewire, 'viewMode') && $livewire->viewMode === 'grid') {
                    return view('filament.resources.file-document-resource.components.grid-view', ['records' => $livewire->getTableRecords()]);
                }
                if (property_exists($livewire, 'viewMode') && $livewire->viewMode === 'explorer') {
                    return view('filament.resources.file-document-resource.components.explorer-view');
                }
                return null;
            })
            ->actions([
                Tables\Actions\Action::make('new_version')
                    ->label('Subir Nueva Versión')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\FileUpload::make('new_file')
                            ->label('Nuevo Archivo')
                            ->required()
                            ->directory('documents')
                            ->maxSize(51200), // 50MB
                        Forms\Components\Textarea::make('change_notes')
                            ->label('Notas de cambio (Opcional)')
                            ->placeholder('Describe qué cambió en esta versión...')
                            ->maxLength(65535),
                    ])
                    ->action(function ($record, array $data) {
                        $currentVersionNum = $record->current_version ?? 1;
                        
                        // Guarda el archivo actual en el historial
                        $record->versions()->create([
                            'version' => $currentVersionNum,
                            'version_label' => 'v' . $currentVersionNum . ' - ' . now()->format('Y-m-d/His'),
                            'file_path' => $record->file_path,
                            'change_notes' => $data['change_notes'] ?? 'Sin descripción de cambios.',
                            'created_by' => auth()->id(),
                        ]);

                        // Actualiza el registro con el nuevo archivo
                        $record->update([
                            'file_path' => $data['new_file'],
                            'current_version' => $currentVersionNum + 1,
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Nueva versión subida')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VersionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFileDocuments::route('/'),
            'create' => Pages\CreateFileDocument::route('/create'),
            'edit' => Pages\EditFileDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('folder', function ($q) {
            $q->forCurrentUser();
        });
    }
}
