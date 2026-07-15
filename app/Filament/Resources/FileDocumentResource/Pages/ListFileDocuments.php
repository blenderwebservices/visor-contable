<?php

namespace App\Filament\Resources\FileDocumentResource\Pages;

use App\Filament\Resources\FileDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFileDocuments extends ListRecords
{
    protected static string $resource = FileDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
