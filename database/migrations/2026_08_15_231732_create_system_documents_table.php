<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_documents', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Document identification
            |--------------------------------------------------------------------------
            */

            $table->string('document_code', 100)->unique();

            $table->string('title');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Classification
            |--------------------------------------------------------------------------
            */

            $table->string('category', 100)->index();

            $table->enum('classification', [
                'general',
                'internal',
                'confidential',
                'restricted',
            ])->default('internal')->index();

            /*
            |--------------------------------------------------------------------------
            | Version
            |--------------------------------------------------------------------------
            */

            $table->string('current_version', 30)->default('1.0');

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
            | Document lifecycle
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'archived',
                'obsolete',
            ])->default('draft')->index();

            $table->date('effective_date')->nullable();

            $table->date('review_date')->nullable();

            $table->date('expiry_date')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Document owner
            |--------------------------------------------------------------------------
            */

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Uploaded / created by
            |--------------------------------------------------------------------------
            */

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->text('approval_notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Document control
            |--------------------------------------------------------------------------
            */

            $table->boolean('requires_acknowledgement')
                ->default(false)
                ->index();

            $table->boolean('is_downloadable')
                ->default(true);

            $table->boolean('is_active')
                ->default(true)
                ->index();

            $table->unsignedInteger('download_count')
                ->default(0);

            $table->unsignedInteger('view_count')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Optional display order
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('sort_order')
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Audit timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Useful indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'category',
                'status',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_documents');
    }
};
