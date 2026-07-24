<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileDocumentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_document_id',
        'version',
        'version_label',
        'file_path',
        'change_notes',
        'created_by',
    ];

    public function document()
    {
        return $this->belongsTo(FileDocument::class, 'file_document_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
