<?php

namespace App\Filament\Resources\TrashedFolderResource\Pages;

use App\Filament\Resources\TrashedFolderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrashedFolders extends ListRecords
{
    protected static string $resource = TrashedFolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action needed in recycle bin
        ];
    }
}
