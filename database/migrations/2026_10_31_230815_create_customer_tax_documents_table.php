
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'customer_tax_documents',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('customer_id')
                    ->constrained('customers')
                    ->cascadeOnDelete();

                $table->string('document_type', 50);

                $table->string('file_path');

                $table->string('original_filename');

                $table->string('mime_type', 100)
                    ->nullable();

                $table->unsignedBigInteger('file_size')
                    ->nullable();

                $table->string(
                    'verification_status',
                    20
                )->default('pending');

                $table->foreignId('uploaded_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('verified_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('verified_at')
                    ->nullable();

                $table->text('verification_notes')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'customer_id',
                    'document_type',
                ]);

                $table->index(
                    'verification_status'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'customer_tax_documents'
        );
    }
};
