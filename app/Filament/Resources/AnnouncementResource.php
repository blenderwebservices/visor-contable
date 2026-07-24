<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?string $navigationLabel = 'Avisos y Notificaciones';
    protected static ?string $pluralModelLabel = 'Avisos';
    protected static ?string $modelLabel = 'Aviso';

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles del Aviso')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenido')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Vigencia')
                    ->schema([
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->label('Válido Desde')
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label('Válido Hasta')
                            ->nullable(),
                    ])->columns(2),
                Forms\Components\Section::make('Visibilidad')
                    ->schema([
                        Forms\Components\Select::make('target_type')
                            ->label('Público Objetivo')
                            ->options([
                                'all_users' => 'Todos los usuarios',
                                'all_groups' => 'Todos los grupos',
                                'specific_users' => 'Usuarios específicos',
                                'specific_groups' => 'Grupos específicos',
                            ])
                            ->required()
                            ->live()
                            ->default('all_users'),
                        Forms\Components\Select::make('users')
                            ->label('Seleccionar Usuarios')
                            ->relationship('users', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => $get('target_type') === 'specific_users'),
                        Forms\Components\Select::make('groups')
                            ->label('Seleccionar Grupos')
                            ->relationship('groups', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => $get('target_type') === 'specific_groups'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                Tables\Columns\TextColumn::make('target_type')
                    ->label('Público')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'all_users' => 'Todos los usuarios',
                        'all_groups' => 'Todos los grupos',
                        'specific_users' => 'Usuarios específicos',
                        'specific_groups' => 'Grupos específicos',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('Desde')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Hasta')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
