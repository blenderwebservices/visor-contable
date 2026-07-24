<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrashedFolderResource\Pages;
use App\Models\Folder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrashedFolderResource extends Resource
{
    protected static ?string $model = Folder::class;

    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?string $navigationGroup = 'Papelera de reciclaje';
    protected static ?string $navigationLabel = 'Carpetas Eliminadas';
    protected static ?string $pluralModelLabel = 'Carpetas Eliminadas';
    protected static ?string $slug = 'trashed-folders';

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Carpeta Padre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // No need for TrashedFilter, the eloquent query already handles it
            ])
            ->actions([
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relations are usually not editable in trashed state
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrashedFolders::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->onlyTrashed();
        
        if (auth()->check() && auth()->user()->role === 'reader') {
            $query->where(function ($q) {
                $q->whereHas('users', function ($q2) {
                    $q2->where('users.id', auth()->id());
                })->orWhereHas('groups', function ($q2) {
                    $q2->whereHas('users', function ($q3) {
                        $q3->where('users.id', auth()->id());
                    });
                });
            });
        }
        
        return $query;
    }
}
