<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupervisorGroupAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'supervisor_id',
        'group_id',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
