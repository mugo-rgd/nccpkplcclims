<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'system_document_reviews',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId(
                    'system_document_id'
                )
                    ->constrained(
                        'system_documents'
                    )
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Version reviewed
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'document_version',
                    30
                );

                /*
                |--------------------------------------------------------------------------
                | Review type
                |--------------------------------------------------------------------------
                */

                $table->enum(
                    'review_type',
                    [
                        'scheduled',
                        'ad_hoc',
                        'post_incident',
                        'regulatory',
                    ]
                )->default('scheduled');

                /*
                |--------------------------------------------------------------------------
                | Review decision
                |--------------------------------------------------------------------------
                */

                $table->enum(
                    'outcome',
                    [
                        'no_change',
                        'revision_required',
                        'obsolete',
                    ]
                );

                $table->text(
                    'review_notes'
                );

                $table->text(
                    'recommended_changes'
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | Reviewer
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'reviewed_by'
                )
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->timestamp(
                    'reviewed_at'
                );

                /*
                |--------------------------------------------------------------------------
                | Next review
                |--------------------------------------------------------------------------
                */

                $table->date(
                    'next_review_date'
                )->nullable();

                $table->timestamps();

                // Fixed: Shortened index names
                $table->index([
                    'system_document_id',
                    'document_version'
                ], 'sdr_doc_id_version_idx'); // Custom short name

                $table->index([
                    'outcome',
                    'reviewed_at'
                ], 'sdr_outcome_reviewed_at_idx'); // Custom short name
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'system_document_reviews'
        );
    }
};