<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'system_document_audiences',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('system_document_id')
                    ->constrained('system_documents')
                    ->cascadeOnDelete();

                $table->foreignId('role_id')
                    ->nullable()
                    ->constrained('roles')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique(
                    [
                        'system_document_id',
                        'role_id'
                    ],
                    'doc_role_unique'  // Shorter name
                );

                $table->unique(
                    [
                        'system_document_id',
                        'user_id'
                    ],
                    'doc_user_unique'  // Shorter name
                );

                // Use a shorter custom index name
                $table->index(
                    ['system_document_id', 'role_id', 'user_id'],
                    'doc_audience_index'  // Shorter name
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'system_document_audiences'
        );
    }
};