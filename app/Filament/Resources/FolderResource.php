<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FolderResource\Pages;
use App\Filament\Resources\FolderResource\RelationManagers;
use App\Models\Folder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FolderResource extends Resource
{
    protected static ?string $model = Folder::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = 'Carpetas';
    protected static ?string $modelLabel = 'Carpeta';
    protected static ?string $pluralModelLabel = 'Carpetas';

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('group_id')
                    ->relationship('group', 'name')
                    ->label('Empresa')
                    ->searchable()
                    ->preload()
                    ->required(fn () => !auth()->user()?->isReader()),
                Forms\Components\Select::make('parent_id')
                    ->relationship('parent', 'name', modifyQueryUsing: function (Builder $query, ?Folder $record) {
                        if ($record) {
                            $query->where('id', '!=', $record->id);
                        }
                        return $query->forCurrentUser();
                    })
                    ->label('Carpeta Padre')
                    ->searchable()
                    ->preload()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Carpeta Padre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->content(function (\Filament\Resources\Pages\ListRecords $livewire) {
                if (property_exists($livewire, 'viewMode') && $livewire->viewMode === 'grid') {
                    return view('filament.resources.folder-resource.components.grid-view', ['records' => $livewire->getTableRecords()]);
                }
                if (property_exists($livewire, 'viewMode') && $livewire->viewMode === 'explorer') {
                    return view('filament.resources.folder-resource.components.explorer-view');
                }
                return null;
            })
            ->actions([
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
            RelationManagers\FileDocumentsRelationManager::class,
            RelationManagers\UsersRelationManager::class,
            RelationManagers\GroupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFolders::route('/'),
            'create' => Pages\CreateFolder::route('/create'),
            'edit' => Pages\EditFolder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->forCurrentUser();
    }
}
