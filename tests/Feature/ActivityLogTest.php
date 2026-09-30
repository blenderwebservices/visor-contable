<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings;
use App\Models\ActivityLog;
use App\Models\Annotation;
use App\Models\FileDocument;
use App\Models\FileDocumentVersion;
use App\Models\Folder;
use App\Models\Group;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_auth_login_and_logout_are_logged(): void
    {
        // Fire Login event
        event(new Login('web', $this->admin, false));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'login',
            'category' => 'auth',
            'user_id' => $this->admin->id,
        ]);

        // Fire Logout event
        event(new Logout('web', $this->admin));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'logout',
            'category' => 'auth',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_document_crud_and_trash_are_logged(): void
    {
        $this->actingAs($this->admin);

        $folder = Folder::create(['name' => 'Contabilidad 2026']);

        // 1. Create document
        $doc = FileDocument::create([
            'name' => 'Balance General 2026',
            'file_path' => 'documents/balance.pdf',
            'type' => 'pdf',
            'folder_id' => $folder->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'created',
            'category' => 'documents',
            'subject_id' => $doc->id,
            'subject_name' => 'Balance General 2026',
        ]);

        // 2. Update document
        $doc->update(['name' => 'Balance General 2026 v2']);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'updated',
            'category' => 'documents',
            'subject_id' => $doc->id,
        ]);

        // 3. Move to trash (Soft Delete)
        $doc->delete();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'deleted',
            'category' => 'trash',
            'subject_id' => $doc->id,
        ]);

        // 4. Restore from trash
        $doc->restore();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'restored',
            'category' => 'trash',
            'subject_id' => $doc->id,
        ]);

        // 5. Force delete (Permanent delete)
        $docId = $doc->id;
        $doc->forceDelete();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'force_deleted',
            'category' => 'trash',
            'subject_id' => $docId,
        ]);
    }

    public function test_company_creation_and_modification_are_logged(): void
    {
        $this->actingAs($this->admin);

        // Create company (Group)
        $group = Group::create([
            'name' => 'Empresa Fiscal SAS',
            'description' => 'Servicios contables',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'created',
            'category' => 'companies',
            'subject_id' => $group->id,
            'subject_name' => 'Empresa Fiscal SAS',
        ]);

        // Update company
        $group->update(['name' => 'Empresa Fiscal Global SAS']);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'updated',
            'category' => 'companies',
            'subject_id' => $group->id,
        ]);

        // Delete company
        $groupId = $group->id;
        $group->delete();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'deleted',
            'category' => 'companies',
            'subject_id' => $groupId,
        ]);
    }

    public function test_user_creation_and_trash_are_logged(): void
    {
        $this->actingAs($this->admin);

        $user = User::create([
            'name' => 'Carlos Contador',
            'username' => 'carloscontador',
            'email' => 'carlos@contador.com',
            'password' => 'secret123',
            'role' => 'reader',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'created',
            'category' => 'users',
            'subject_id' => $user->id,
        ]);

        // Move user to trash
        $user->delete();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'deleted',
            'category' => 'trash',
            'subject_id' => $user->id,
        ]);

        // Restore user
        $user->restore();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'restored',
            'category' => 'trash',
            'subject_id' => $user->id,
        ]);
    }

    public function test_folder_crud_is_logged(): void
    {
        $this->actingAs($this->admin);

        $folder = Folder::create(['name' => 'Carpeta Finanzas']);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'created',
            'category' => 'folders',
            'subject_id' => $folder->id,
            'subject_name' => 'Carpeta Finanzas',
        ]);

        $folder->update(['name' => 'Carpeta Finanzas 2026']);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'updated',
            'category' => 'folders',
            'subject_id' => $folder->id,
        ]);

        $folder->delete();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'deleted',
            'category' => 'trash',
            'subject_id' => $folder->id,
        ]);
    }

    public function test_backup_and_maintenance_logging(): void
    {
        $this->actingAs($this->admin);

        ActivityLogger::logBackup(
            action: 'backup_export',
            description: 'Exportación de Respaldo Completo ZIP',
            properties: ['filename' => 'backup_2026.zip']
        );

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'backup_export',
            'category' => 'backup',
            'description' => 'Exportación de Respaldo Completo ZIP',
        ]);

        ActivityLogger::logBackup(
            action: 'backup_restore',
            description: 'Restauración Completa del sistema ejecutada',
            properties: ['extracted_files' => 15]
        );

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'backup_restore',
            'category' => 'backup',
        ]);
    }

    public function test_settings_page_renders_logs_and_filters(): void
    {
        $this->actingAs($this->admin);

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'user_name' => $this->admin->name,
            'user_email' => $this->admin->email,
            'action' => 'login',
            'category' => 'auth',
            'description' => 'Inicio de sesión de prueba',
            'ip_address' => '127.0.0.1',
        ]);

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'user_name' => $this->admin->name,
            'user_email' => $this->admin->email,
            'action' => 'created',
            'category' => 'documents',
            'description' => 'Creación de documento prueba.pdf',
            'subject_name' => 'prueba.pdf',
            'ip_address' => '127.0.0.1',
        ]);

        Livewire::test(Settings::class)
            ->set('activeTab', 'logs')
            ->assertSee('Registros de Auditoría (Logs)')
            ->assertSee('Inicio de sesión de prueba')
            ->assertSee('Creación de documento prueba.pdf')
            ->set('logCategory', 'auth')
            ->assertSee('Inicio de sesión de prueba')
            ->assertDontSee('Creación de documento prueba.pdf')
            ->call('exportLogsCsv')
            ->assertFileDownloaded();
    }
}
