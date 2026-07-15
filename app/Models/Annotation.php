<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annotation extends Model
{
    use HasFactory;

    protected $fillable = ['file_document_id', 'user_id', 'content'];

    public function fileDocument()
    {
        return $this->belongsTo(FileDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
