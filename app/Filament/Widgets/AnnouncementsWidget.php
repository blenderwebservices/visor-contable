<?php

namespace App\Filament\Widgets;

use App\Models\Announcement;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementsWidget extends Widget
{
    protected static string $view = 'filament.widgets.announcements-widget';

    protected static ?int $sort = -1;

    public int|string|array $columnSpan = 'full';

    public function getAnnouncementsProperty()
    {
        $user = auth()->user();
        if (!$user) {
            return collect();
        }

        $now = now();

        return Announcement::query()
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function (Builder $query) use ($now) {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            })
            ->whereDoesntHave('hiddenByUsers', function (Builder $query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->where(function (Builder $query) use ($user) {
                // Target: all users
                $query->where('target_type', 'all_users')
                      // Target: all groups (if user belongs to any group)
                      ->orWhere(function (Builder $q) use ($user) {
                          $q->where('target_type', 'all_groups')
                            ->whereExists(function ($sub) use ($user) {
                                // Checking if user has at least one group
                                if ($user->groups()->count() > 0) {
                                    $sub->selectRaw('1');
                                } else {
                                    $sub->selectRaw('0')->whereRaw('1 = 0');
                                }
                            });
                      })
                      // Target: specific users
                      ->orWhere(function (Builder $q) use ($user) {
                          $q->where('target_type', 'specific_users')
                            ->whereHas('users', function (Builder $q2) use ($user) {
                                $q2->where('users.id', $user->id);
                            });
                      })
                      // Target: specific groups
                      ->orWhere(function (Builder $q) use ($user) {
                          $q->where('target_type', 'specific_groups')
                            ->whereHas('groups', function (Builder $q2) use ($user) {
                                $q2->whereIn('groups.id', $user->groups()->pluck('groups.id'));
                            });
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function hideAnnouncement($announcementId)
    {
        $user = auth()->user();
        if ($user) {
            $user->hiddenAnnouncements()->syncWithoutDetaching([$announcementId]);
        }
    }

    public function restoreHiddenAnnouncements()
    {
        $user = auth()->user();
        if ($user) {
            $user->hiddenAnnouncements()->detach();
        }
    }
    
    public function getHiddenCountProperty()
    {
        $user = auth()->user();
        if (!$user) {
            return 0;
        }
        return $user->hiddenAnnouncements()->count();
    }
}
