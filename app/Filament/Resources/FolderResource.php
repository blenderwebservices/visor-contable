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

    public static function getModelLabel(): string
    {
        return __('Folder');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Folders');
    }

    public static function getNavigationLabel(): string
    {
        return __('Folders');
    }

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
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
                    ->label(__('Parent Folder'))
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
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('group.name')
                    ->label(__('Company'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label(__('Parent Folder'))
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
