<?php

namespace App\Filament\Resources\FolderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FileDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'fileDocuments';

    public function form(Form $form): Form
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
                Forms\Components\Toggle::make('is_downloadable')
                    ->label('Permitir descarga')
                    ->default(false),
                Forms\Components\KeyValue::make('attributes')
                    ->keyLabel('Attribute Name')
                    ->valueLabel('Attribute Value')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('type')->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DissociateAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
