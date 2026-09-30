<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'category',
        'subject_type',
        'subject_id',
        'subject_name',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for category filtering.
     */
    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (empty($category) || $category === 'all') {
            return $query;
        }

        return $query->where('category', $category);
    }

    /**
     * Scope for action filtering.
     */
    public function scopeAction(Builder $query, ?string $action): Builder
    {
        if (empty($action) || $action === 'all') {
            return $query;
        }

        return $query->where('action', $action);
    }

    /**
     * Scope for user filtering.
     */
    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        if (empty($userId)) {
            return $query;
        }

        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range filtering.
     */
    public function scopeDateRange(Builder $query, ?string $range): Builder
    {
        return match ($range) {
            'today' => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', today()->subDay()),
            '7days' => $query->where('created_at', '>=', now()->subDays(7)),
            '30days' => $query->where('created_at', '>=', now()->subDays(30)),
            default => $query,
        };
    }

    /**
     * Scope for full-text search across relevant fields.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $term = '%' . trim($term) . '%';

        return $query->where(function ($q) use ($term) {
            $q->where('description', 'like', $term)
                ->orWhere('user_name', 'like', $term)
                ->orWhere('user_email', 'like', $term)
                ->orWhere('subject_name', 'like', $term)
                ->orWhere('ip_address', 'like', $term);
        });
    }

    /**
     * Get human-friendly action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'login' => 'Inicio de Sesión',
            'logout' => 'Cierre de Sesión',
            'failed_login' => 'Acceso Fallido',
            'created' => 'Creación',
            'updated' => 'Modificación',
            'deleted' => 'Movido a Papelera',
            'restored' => 'Restaurado',
            'force_deleted' => 'Eliminación Definitiva',
            'backup_export' => 'Exportación de Respaldo',
            'backup_restore' => 'Restauración de Respaldo',
            'system_optimize' => 'Optimización del Sistema',
            'audit' => 'Auditoría de Integridad',
            'clean_orphans' => 'Limpieza de Huérfanos',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get action color for badge rendering.
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'login', 'restored' => 'success',
            'logout' => 'gray',
            'failed_login', 'force_deleted' => 'danger',
            'created' => 'emerald',
            'updated' => 'amber',
            'deleted' => 'rose',
            'backup_export' => 'sky',
            'backup_restore' => 'violet',
            'system_optimize', 'clean_orphans' => 'orange',
            'audit' => 'indigo',
            default => 'primary',
        };
    }

    /**
     * Get action heroicon.
     */
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'login' => 'heroicon-m-arrow-right-end-on-rectangle',
            'logout' => 'heroicon-m-arrow-left-start-on-rectangle',
            'failed_login' => 'heroicon-m-exclamation-triangle',
            'created' => 'heroicon-m-plus-circle',
            'updated' => 'heroicon-m-pencil-square',
            'deleted' => 'heroicon-m-trash',
            'restored' => 'heroicon-m-arrow-uturn-left',
            'force_deleted' => 'heroicon-m-x-circle',
            'backup_export' => 'heroicon-m-arrow-down-tray',
            'backup_restore' => 'heroicon-m-arrow-up-tray',
            'system_optimize' => 'heroicon-m-arrow-path',
            'audit' => 'heroicon-m-shield-check',
            'clean_orphans' => 'heroicon-m-sparkles',
            default => 'heroicon-m-document-text',
        };
    }

    /**
     * Get human-friendly category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'auth' => 'Sesiones y Accesos',
            'documents' => 'Documentos',
            'companies' => 'Empresas',
            'users' => 'Usuarios',
            'folders' => 'Carpetas',
            'trash' => 'Papelera',
            'backup' => 'Respaldos y Sistema',
            'system' => 'Sistema / Avisos',
            default => ucfirst($this->category),
        };
    }

    /**
     * Get category heroicon.
     */
    public function getCategoryIconAttribute(): string
    {
        return match ($this->category) {
            'auth' => 'heroicon-m-lock-closed',
            'documents' => 'heroicon-m-document-duplicate',
            'companies' => 'heroicon-m-building-office-2',
            'users' => 'heroicon-m-users',
            'folders' => 'heroicon-m-folder',
            'trash' => 'heroicon-m-trash',
            'backup' => 'heroicon-m-archive-box',
            'system' => 'heroicon-m-cog-6-tooth',
            default => 'heroicon-m-tag',
        };
    }

    /**
     * Get human-friendly browser and OS representation from user agent.
     */
    public function getBrowserInfoAttribute(): string
    {
        if (empty($this->user_agent)) {
            return 'Desconocido';
        }

        $ua = $this->user_agent;
        $os = 'SO Desconocido';
        if (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS X')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'Linux')) {
            $os = 'Linux';
        } elseif (str_contains($ua, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $os = 'iOS';
        }

        $browser = 'Navegador';
        if (str_contains($ua, 'Edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'Chrome/') && !str_contains($ua, 'Edg/')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome/')) {
            $browser = 'Safari';
        }

        return "{$browser} ({$os})";
    }
}
