<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'parent_id'];

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

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
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
