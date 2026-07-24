<?php

namespace App\Filament\Resources\TrashedFileDocumentResource\Pages;

use App\Filament\Resources\TrashedFileDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrashedFileDocuments extends ListRecords
{
    protected static string $resource = TrashedFileDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action needed in recycle bin
        ];
    }
}
