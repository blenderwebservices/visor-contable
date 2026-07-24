<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrashedFileDocumentResource\Pages;
use App\Models\FileDocument;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrashedFileDocumentResource extends Resource
{
    protected static ?string $model = FileDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?string $navigationGroup = 'Papelera de reciclaje';
    protected static ?string $navigationLabel = 'Documentos Eliminados';
    protected static ?string $pluralModelLabel = 'Documentos Eliminados';
    protected static ?string $slug = 'trashed-documents';

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
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('folder.name')
                    ->label('Carpeta Original')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrashedFileDocuments::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->onlyTrashed();
        
        if (auth()->check() && auth()->user()->role === 'reader') {
            $query->whereHas('folder', function ($q) {
                $q->where(function ($q2) {
                    $q2->whereHas('users', function ($q3) {
                        $q3->where('users.id', auth()->id());
                    })->orWhereHas('groups', function ($q3) {
                        $q3->whereHas('users', function ($q4) {
                            $q4->where('users.id', auth()->id());
                        });
                    });
                });
            });
        }
        
        return $query;
    }
}
