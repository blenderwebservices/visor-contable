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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('set null')->after('role');
        });

        Schema::table('folders', function (Blueprint $table) {
            if (Schema::hasColumn('folders', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('cascade')->after('id');
        });

        Schema::dropIfExists('supervisor_company_assignments');

        Schema::create('supervisor_group_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['supervisor_id', 'group_id']);
        });

        Schema::dropIfExists('companies');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::dropIfExists('supervisor_group_assignments');

        Schema::create('supervisor_company_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['supervisor_id', 'company_id']);
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
        });
    }
};
