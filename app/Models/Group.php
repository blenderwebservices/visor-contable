<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function directUsers()
    {
        return $this->hasMany(User::class);
    }

    public function folders()
    {
        return $this->belongsToMany(Folder::class);
    }

    public function directFolders()
    {
        return $this->hasMany(Folder::class);
    }

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class);
    }

    public function supervisors()
    {
        return $this->belongsToMany(User::class, 'supervisor_group_assignments', 'group_id', 'supervisor_id');
    }
}
