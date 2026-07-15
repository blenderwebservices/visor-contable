<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Folder;
use App\Models\FileDocument;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Usuarios Registrados', User::count())
                ->description('Total de usuarios en el sistema')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Carpetas', Folder::count())
                ->description('Estructura de directorios')
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),
            Stat::make('Documentos', FileDocument::count())
                ->description('Total de archivos subidos')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
        ];
    }
}
