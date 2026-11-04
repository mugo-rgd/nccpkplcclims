<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'system_document_acknowledgements',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('system_document_id')
                    ->constrained('system_documents')
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Exact version acknowledged
                |--------------------------------------------------------------------------
                */

                $table->string('document_version', 30);

                /*
                |--------------------------------------------------------------------------
                | Acknowledgement
                |--------------------------------------------------------------------------
                */

                $table->timestamp('acknowledged_at');

                $table->string('ip_address', 45)->nullable();

                $table->text('user_agent')->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | A user acknowledges one version only once
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'system_document_id',
                        'user_id',
                        'document_version'
                    ],
                    'document_user_version_ack_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'system_document_acknowledgements'
        );
    }
};
