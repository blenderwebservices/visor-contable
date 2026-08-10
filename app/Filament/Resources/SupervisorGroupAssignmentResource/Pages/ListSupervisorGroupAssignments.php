<?php

namespace App\Filament\Resources\SupervisorGroupAssignmentResource\Pages;

use App\Filament\Resources\SupervisorGroupAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupervisorGroupAssignments extends ListRecords
{
    protected static string $resource = SupervisorGroupAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
