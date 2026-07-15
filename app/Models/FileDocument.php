<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'type',
        'is_downloadable',
        'folder_id',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'is_downloadable' => 'boolean',
        ];
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function annotations()
    {
        return $this->hasMany(Annotation::class);
    }
}
