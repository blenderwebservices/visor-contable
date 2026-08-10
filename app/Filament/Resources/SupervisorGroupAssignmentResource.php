<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupervisorGroupAssignmentResource\Pages;
use App\Models\SupervisorGroupAssignment;
use App\Models\User;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupervisorGroupAssignmentResource extends Resource
{
    protected static ?string $model = SupervisorGroupAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Asignaciones de Supervisor';
    protected static ?string $modelLabel = 'Asignación de Supervisor';
    protected static ?string $pluralModelLabel = 'Asignaciones de Supervisor';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('supervisor_id')
                    ->label('Supervisor')
                    ->options(User::where('role', 'supervisor')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('group_id')
                    ->label('Empresa (Grupo)')
                    ->options(Group::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supervisor.name')
                    ->label('Supervisor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('Empresa (Grupo)')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Asignación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupervisorGroupAssignments::route('/'),
            'create' => Pages\CreateSupervisorGroupAssignment::route('/create'),
            'edit' => Pages\EditSupervisorGroupAssignment::route('/{record}/edit'),
        ];
    }
}
