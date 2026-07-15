<?php

namespace App\Filament\App\Widgets;

use App\Models\Folder;
use App\Models\FileDocument;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class UserStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        
        $accessibleFolderIds = Folder::where(function (Builder $query) use ($user) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->orWhereHas('groups', function ($q) use ($user) {
                $q->whereHas('users', function ($q2) use ($user) {
                    $q2->where('users.id', $user->id);
                });
            });
        })->pluck('id')->toArray();

        $accessibleDocumentsCount = FileDocument::whereIn('folder_id', $accessibleFolderIds)->count();

        return [
            Stat::make('Mis Carpetas', count($accessibleFolderIds))
                ->description('Carpetas con acceso')
                ->descriptionIcon('heroicon-m-folder-open')
                ->color('primary'),
            Stat::make('Documentos Disponibles', $accessibleDocumentsCount)
                ->description('Archivos accesibles')
                ->descriptionIcon('heroicon-m-document')
                ->color('success'),
        ];
    }
}
