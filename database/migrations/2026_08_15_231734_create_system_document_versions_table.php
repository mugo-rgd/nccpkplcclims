<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_document_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('system_document_id')
                ->constrained('system_documents')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Version information
            |--------------------------------------------------------------------------
            */

            $table->string('version', 30);

            $table->text('change_summary')->nullable();

            $table->text('revision_notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | File information
            |--------------------------------------------------------------------------
            */

            $table->string('file_path');

            $table->string('original_filename');

            $table->string('stored_filename')->nullable();

            $table->string('mime_type', 150)->nullable();

            $table->unsignedBigInteger('file_size')->nullable();

            $table->string('file_extension', 20)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Version status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'archived',
                'obsolete',
            ])->default('draft')->index();

            /*
            |--------------------------------------------------------------------------
            | Uploaded by
            |--------------------------------------------------------------------------
            */

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Approved by
            |--------------------------------------------------------------------------
            */

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Effective dates
            |--------------------------------------------------------------------------
            */

            $table->date('effective_date')->nullable();

            $table->date('review_date')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Current version marker
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_current')
                ->default(false)
                ->index();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate versions for same document
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['system_document_id', 'version'],
                'system_document_version_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_document_versions');
    }
};
