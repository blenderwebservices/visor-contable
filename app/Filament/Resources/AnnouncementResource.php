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
    public static function getNavigationGroup(): ?string
    {
        return __('Admin Panel');
    }

    public static function getNavigationLabel(): string
    {
        return __('Announcements');
    }

    public static function getModelLabel(): string
    {
        return __('Announcement');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Announcements');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Announcement Details'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('content')
                            ->label(__('Content'))
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make(__('Validity'))
                    ->schema([
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->label(__('Valid From'))
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label(__('Valid Until'))
                            ->nullable(),
                    ])->columns(2),
                Forms\Components\Section::make(__('Visibility'))
                    ->schema([
                        Forms\Components\Select::make('target_type')
                            ->label(__('Target Audience'))
                            ->options([
                                'all_users' => __('All Users'),
                                'all_groups' => __('All Companies'),
                                'specific_users' => __('Specific Users'),
                                'specific_groups' => __('Specific Companies'),
                            ])
                            ->required()
                            ->live()
                            ->default('all_users'),
                        Forms\Components\Select::make('users')
                            ->label(__('Select Users'))
                            ->relationship('users', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => $get('target_type') === 'specific_users'),
                        Forms\Components\Select::make('groups')
                            ->label(__('Select Companies'))
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
                    ->label(__('Title'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('target_type')
                    ->label(__('Audience'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'all_users' => __('All Users'),
                        'all_groups' => __('All Companies'),
                        'specific_users' => __('Specific Users'),
                        'specific_groups' => __('Specific Companies'),
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('valid_from')
                    ->label(__('From'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label(__('Until'))
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
