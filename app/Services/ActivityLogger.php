<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Annotation;
use App\Models\Announcement;
use App\Models\FileDocument;
use App\Models\FileDocumentVersion;
use App\Models\Folder;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    protected static bool $loggingDisabled = false;

    /**
     * Disable activity logging globally for the current lifecycle.
     */
    public static function disableLogging(): void
    {
        static::$loggingDisabled = true;
    }

    /**
     * Enable activity logging.
     */
    public static function enableLogging(): void
    {
        static::$loggingDisabled = false;
    }

    /**
     * Check if activity logging is currently disabled.
     */
    public static function isLoggingDisabled(): bool
    {
        return static::$loggingDisabled;
    }

    /**
     * Execute a callback without generating activity logs.
     */
    public static function withoutLogging(callable $callback)
    {
        $previousState = static::$loggingDisabled;
        static::$loggingDisabled = true;

        try {
            return $callback();
        } finally {
            static::$loggingDisabled = $previousState;
        }
    }

    /**
     * Record an activity log entry.
     */
    public static function log(
        string $action,
        string $category,
        string $description,
        $subject = null,
        array $properties = [],
        ?User $user = null
    ): ?ActivityLog {
        if (static::isLoggingDisabled()) {
            return null;
        }

        $currentUser = $user ?? Auth::user();
        $ip = request()?->ip();
        $userAgent = request()?->userAgent();

        $subjectType = null;
        $subjectId = null;
        $subjectName = null;

        if ($subject instanceof Model) {
            $subjectType = get_class($subject);
            $subjectId = $subject->getKey();
            $subjectName = static::resolveSubjectName($subject);
        } elseif (is_string($subject)) {
            $subjectName = $subject;
        }

        $sanitizedProperties = static::sanitizeProperties($properties);

        return ActivityLog::create([
            'user_id' => $currentUser?->id,
            'user_name' => $currentUser?->name ?? 'Sistema',
            'user_email' => $currentUser?->email,
            'action' => $action,
            'category' => $category,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_name' => $subjectName,
            'description' => $description,
            'properties' => !empty($sanitizedProperties) ? $sanitizedProperties : null,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Record authentication activity (login, logout, failed login).
     */
    public static function logAuth(string $action, ?User $user, string $description, array $properties = []): ?ActivityLog
    {
        return static::log(
            action: $action,
            category: 'auth',
            description: $description,
            subject: $user,
            properties: $properties,
            user: $user
        );
    }

    /**
     * Record backup / restore and maintenance operations.
     */
    public static function logBackup(string $action, string $description, array $properties = []): ?ActivityLog
    {
        return static::log(
            action: $action,
            category: 'backup',
            description: $description,
            subject: 'Sistema de Respaldos',
            properties: $properties
        );
    }

    /**
     * Resolve a human-friendly name for any audited model.
     */
    public static function resolveSubjectName(Model $model): string
    {
        if ($model instanceof FileDocument) {
            return $model->name ?? 'Documento #' . $model->id;
        }

        if ($model instanceof Folder) {
            return $model->name ?? 'Carpeta #' . $model->id;
        }

        if ($model instanceof Group) {
            return $model->name ?? 'Empresa #' . $model->id;
        }

        if ($model instanceof User) {
            return ($model->name ?? 'Usuario') . ($model->email ? " ({$model->email})" : '');
        }

        if ($model instanceof Announcement) {
            return $model->title ?? 'Anuncio #' . $model->id;
        }

        if ($model instanceof Annotation) {
            $docName = $model->fileDocument?->name ?? 'documento';
            return "Nota en {$docName}";
        }

        if ($model instanceof FileDocumentVersion) {
            $docName = $model->fileDocument?->name ?? 'documento';
            return "Versión v{$model->version} de {$docName}";
        }

        if (isset($model->name)) {
            return (string) $model->name;
        }

        if (isset($model->title)) {
            return (string) $model->title;
        }

        return class_basename($model) . ' #' . $model->getKey();
    }

    /**
     * Sanitize properties array, masking passwords and sensitive information.
     */
    public static function sanitizeProperties(array $properties): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        foreach ($properties as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $properties[$key] = '********';
            } elseif (is_array($value)) {
                $properties[$key] = static::sanitizeProperties($value);
            }
        }

        return $properties;
    }
}
