<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'valid_from',
        'valid_until',
        'target_type',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function hiddenByUsers()
    {
        return $this->belongsToMany(User::class, 'announcement_user_hidden');
    }

    /**
     * Sanitiza el contenido HTML de avisos contra XSS (Pilar 1).
     */
    public function getSanitizedContentAttribute(): string
    {
        $content = $this->content ?? '';
        // Eliminar tags ejecutables
        $content = preg_replace('/<\s*(script|iframe|object|embed|applet|form)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $content);
        $content = preg_replace('/<\s*(script|iframe|object|embed|applet|form)[^>]*\/?>/is', '', $content);
        // Eliminar eventos inline tipo on* (onclick, onerror, onload, etc.)
        $content = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/is', '', $content);
        // Eliminar pseudoprotocolos javascript: en enlaces o atributos
        $content = preg_replace('/(href|src)\s*=\s*["\']\s*javascript:[^"\']*["\']/is', '$1="#"', $content);

        return $content;
    }
}
