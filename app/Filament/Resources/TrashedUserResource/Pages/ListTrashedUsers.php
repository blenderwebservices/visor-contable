<?php

namespace App\Filament\Resources\TrashedUserResource\Pages;

use App\Filament\Resources\TrashedUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrashedUsers extends ListRecords
{
    protected static string $resource = TrashedUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action needed in recycle bin
        ];
    }
}
