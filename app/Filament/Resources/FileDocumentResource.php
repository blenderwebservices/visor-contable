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
        return auth()->user()?->role === 'admin';
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
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListFileDocuments::route('/'),
            'create' => Pages\CreateFileDocument::route('/create'),
            'edit' => Pages\EditFileDocument::route('/{record}/edit'),
        ];
    }
}
