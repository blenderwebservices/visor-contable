<?php

namespace App\Observers;

use App\Models\Annotation;
use App\Models\Announcement;
use App\Models\FileDocument;
use App\Models\FileDocumentVersion;
use App\Models\Folder;
use App\Models\Group;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class ModelAuditObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        if (ActivityLogger::isLoggingDisabled()) {
            return;
        }

        $category = $this->resolveCategory($model);
        $subjectName = ActivityLogger::resolveSubjectName($model);
        $typeLabel = $this->resolveTypeLabel($model);

        $description = "{$typeLabel} '{$subjectName}' creado correctamente";
        $properties = [
            'attributes' => $model->getAttributes(),
        ];

        // Specific customizations for special models
        if ($model instanceof FileDocumentVersion) {
            $docName = $model->fileDocument?->name ?? 'documento';
            $description = "Nueva versión v{$model->version} subida para '{$docName}'";
        } elseif ($model instanceof Annotation) {
            $docName = $model->fileDocument?->name ?? 'documento';
            $description = "Nueva nota agregada a '{$docName}'";
        } elseif ($model instanceof User) {
            $description = "Usuario '{$model->name}' ({$model->email}) creado con rol '{$model->role}'";
        }

        ActivityLogger::log(
            action: 'created',
            category: $category,
            description: $description,
            subject: $model,
            properties: $properties
        );
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        if (ActivityLogger::isLoggingDisabled()) {
            return;
        }

        $changes = $model->getChanges();
        $ignoredFields = ['updated_at', 'remember_token', 'deleted_at'];

        foreach ($ignoredFields as $field) {
            unset($changes[$field]);
        }

        if (empty($changes)) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($changes as $attribute => $newValue) {
            $old[$attribute] = $model->getOriginal($attribute);
            $new[$attribute] = $newValue;
        }

        $category = $this->resolveCategory($model);
        $subjectName = ActivityLogger::resolveSubjectName($model);
        $typeLabel = $this->resolveTypeLabel($model);

        $description = "{$typeLabel} '{$subjectName}' modificado";

        ActivityLogger::log(
            action: 'updated',
            category: $category,
            description: $description,
            subject: $model,
            properties: [
                'old' => $old,
                'new' => $new,
                'changed_fields' => array_keys($changes),
            ]
        );
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        if (ActivityLogger::isLoggingDisabled()) {
            return;
        }

        $isForce = method_exists($model, 'isForceDeleting') ? $model->isForceDeleting() : false;
        $subjectName = ActivityLogger::resolveSubjectName($model);
        $typeLabel = $this->resolveTypeLabel($model);

        if ($isForce) {
            ActivityLogger::log(
                action: 'force_deleted',
                category: 'trash',
                description: "{$typeLabel} '{$subjectName}' eliminado permanentemente",
                subject: $model
            );
        } else {
            $category = method_exists($model, 'trashed') ? 'trash' : $this->resolveCategory($model);
            $action = method_exists($model, 'trashed') ? 'deleted' : 'deleted';
            $desc = method_exists($model, 'trashed')
                ? "{$typeLabel} '{$subjectName}' enviado a la papelera de reciclaje"
                : "{$typeLabel} '{$subjectName}' eliminado";

            ActivityLogger::log(
                action: $action,
                category: $category,
                description: $desc,
                subject: $model
            );
        }
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        if (ActivityLogger::isLoggingDisabled()) {
            return;
        }

        $subjectName = ActivityLogger::resolveSubjectName($model);
        $typeLabel = $this->resolveTypeLabel($model);

        ActivityLogger::log(
            action: 'restored',
            category: 'trash',
            description: "{$typeLabel} '{$subjectName}' restaurado de la papelera",
            subject: $model
        );
    }

    /**
     * Handle the Model "forceDeleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        if (ActivityLogger::isLoggingDisabled()) {
            return;
        }

        $subjectName = ActivityLogger::resolveSubjectName($model);
        $typeLabel = $this->resolveTypeLabel($model);

        // In case deleted() with isForceDeleting didn't already record it
        $recentLog = \App\Models\ActivityLog::where('action', 'force_deleted')
            ->where('subject_type', get_class($model))
            ->where('subject_id', $model->getKey())
            ->where('created_at', '>=', now()->subSeconds(2))
            ->first();

        if ($recentLog) {
            return;
        }

        ActivityLogger::log(
            action: 'force_deleted',
            category: 'trash',
            description: "{$typeLabel} '{$subjectName}' eliminado definitivamente de la papelera",
            subject: $model
        );
    }

    /**
     * Determine category based on model class.
     */
    protected function resolveCategory(Model $model): string
    {
        return match (get_class($model)) {
            FileDocument::class, FileDocumentVersion::class, Annotation::class => 'documents',
            Folder::class => 'folders',
            Group::class => 'companies',
            User::class => 'users',
            Announcement::class => 'system',
            default => 'system',
        };
    }

    /**
     * Determine Spanish human label based on model class.
     */
    protected function resolveTypeLabel(Model $model): string
    {
        return match (get_class($model)) {
            FileDocument::class => 'Documento',
            FileDocumentVersion::class => 'Versión de documento',
            Folder::class => 'Carpeta',
            Group::class => 'Empresa',
            User::class => 'Usuario',
            Announcement::class => 'Anuncio',
            Annotation::class => 'Nota',
            default => class_basename($model),
        };
    }
}
