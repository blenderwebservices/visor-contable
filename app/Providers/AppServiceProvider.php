<?php

namespace App\Providers;

use App\Listeners\AuthEventListener;
use App\Models\Annotation;
use App\Models\Announcement;
use App\Models\FileDocument;
use App\Models\FileDocumentVersion;
use App\Models\Folder;
use App\Models\Group;
use App\Models\User;
use App\Observers\ModelAuditObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar suscriptor de eventos de autenticación
        Event::subscribe(AuthEventListener::class);

        // Registrar observador de auditoría para modelos del sistema
        FileDocument::observe(ModelAuditObserver::class);
        FileDocumentVersion::observe(ModelAuditObserver::class);
        Folder::observe(ModelAuditObserver::class);
        Group::observe(ModelAuditObserver::class);
        User::observe(ModelAuditObserver::class);
        Announcement::observe(ModelAuditObserver::class);
        Annotation::observe(ModelAuditObserver::class);
    }
}
