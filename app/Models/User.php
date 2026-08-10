<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'group_id',
        'has_restricted_folders',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function supervisedGroups()
    {
        return $this->belongsToMany(Group::class, 'supervisor_group_assignments', 'supervisor_id', 'group_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isReader(): bool
    {
        return $this->role === 'reader';
    }

    public function allowedFolders()
    {
        return $this->belongsToMany(Folder::class, 'users_folders_shared', 'user_id', 'folder_id')
                    ->withPivot('can_upload', 'can_download');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function folders()
    {
        return $this->belongsToMany(Folder::class);
    }

    public function annotations()
    {
        return $this->hasMany(Annotation::class);
    }

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class);
    }

    public function hiddenAnnouncements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_user_hidden');
    }
}
