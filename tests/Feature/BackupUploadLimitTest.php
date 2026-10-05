<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Livewire;
use Tests\TestCase;

class BackupUploadLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_livewire_allows_large_file_uploads_up_to_500mb(): void
    {
        $rules = FileUploadConfiguration::rules();

        $this->assertContains('required', $rules);
        $this->assertContains('file', $rules);
        $this->assertContains('max:512000', $rules);
    }

    public function test_livewire_temporary_upload_endpoint_accepts_file_larger_than_two_megabytes(): void
    {
        \Illuminate\Support\Facades\Storage::fake('tmp-for-tests');
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // Generar un archivo falso de 8 MB (8 * 1024 KB = 8192 KB)
        $file = UploadedFile::fake()->create('respaldo_documentos_test.zip', 8192, 'application/zip');

        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'livewire.upload-file',
            now()->addMinutes(5)
        );

        $response = $this->post($signedUrl, [
            'files' => [$file],
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['paths']);
    }

    public function test_settings_page_can_import_documents_zip(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // Crear un ZIP válido en memoria con un archivo de prueba
        $tempZipPath = tempnam(sys_get_temp_dir(), 'test_docs_') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('documents/test.txt', 'Contenido de prueba');
        $zip->addFromString('manifest.json', json_encode([
            'type' => 'documents_backup',
            'total_files' => 1,
        ]));
        $zip->close();

        $uploadedFile = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::fake()->create('respaldo_documentos.zip', 100);
        // Copiar el contenido real del ZIP al archivo temporal
        file_put_contents($uploadedFile->getRealPath(), file_get_contents($tempZipPath));

        Livewire::test(\App\Filament\Pages\Settings::class)
            ->callAction('import_documents', [
                'documents_file' => $uploadedFile,
            ])
            ->assertHasNoActionErrors();

        \Illuminate\Support\Facades\Storage::disk('public')->assertExists('documents/test.txt');

        @unlink($tempZipPath);
    }
}
