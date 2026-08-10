<?php

namespace App\Filament\Resources\SupervisorGroupAssignmentResource\Pages;

use App\Filament\Resources\SupervisorGroupAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupervisorGroupAssignment extends EditRecord
{
    protected static string $resource = SupervisorGroupAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
