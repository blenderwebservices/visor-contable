<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'parent_id', 'group_id'];

    protected static function booted()
    {
        static::deleting(function (Folder $folder) {
            if ($folder->isForceDeleting()) {
                $folder->children()->withTrashed()->get()->each->forceDelete();
                $folder->fileDocuments()->withTrashed()->get()->each->forceDelete();
            } else {
                $folder->children()->get()->each->delete();
                $folder->fileDocuments()->get()->each->delete();
            }
        });

        static::restoring(function (Folder $folder) {
            $folder->children()->onlyTrashed()->get()->each->restore();
            $folder->fileDocuments()->onlyTrashed()->get()->each->restore();
        });
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function sharedWithUsers()
    {
        return $this->belongsToMany(User::class, 'users_folders_shared', 'folder_id', 'user_id')
                    ->withPivot('can_upload', 'can_download');
    }

    public function scopeForCurrentUser($query)
    {
        $user = auth()->user();
        if (!$user) {
            return $query;
        }

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isSupervisor()) {
            $groupIds = $user->supervisedGroups()->pluck('groups.id');
            return $query->whereIn('group_id', $groupIds);
        }

        // Reader
        $query->where('group_id', $user->group_id);
        
        if ($user->has_restricted_folders) {
            $query->whereIn('id', $user->allowedFolders()->pluck('folders.id'));
        }
        
        return $query;
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function fileDocuments()
    {
        return $this->hasMany(FileDocument::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
