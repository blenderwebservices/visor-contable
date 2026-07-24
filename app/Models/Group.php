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

    public function folders()
    {
        return $this->belongsToMany(Folder::class);
    }

    public function announcements()
    {
        return $this->belongsToMany(Announcement::class);
    }
}
