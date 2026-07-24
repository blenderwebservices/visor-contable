<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('file_documents', function (Blueprint $table) {
            $table->integer('current_version')->default(1)->after('folder_id');
        });

        Schema::create('file_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_document_id')->constrained()->cascadeOnDelete();
            $table->integer('version');
            $table->string('version_label');
            $table->string('file_path');
            $table->text('change_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_document_versions');

        Schema::table('file_documents', function (Blueprint $table) {
            $table->dropColumn('current_version');
        });
    }
};
