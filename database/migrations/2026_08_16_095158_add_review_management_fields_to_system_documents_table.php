<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'system_documents',
            function (Blueprint $table) {

                $table->enum(
                    'review_state',
                    [
                        'current',
                        'under_review',
                        'revision_required',
                    ]
                )
                    ->default('current')
                    ->after('review_date')
                    ->index();

                $table->timestamp(
                    'last_reviewed_at'
                )
                    ->nullable()
                    ->after('review_state');

                $table->foreignId(
                    'last_reviewed_by'
                )
                    ->nullable()
                    ->after('last_reviewed_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'system_documents',
            function (Blueprint $table) {

                $table->dropForeign([
                    'last_reviewed_by'
                ]);

                $table->dropColumn([
                    'review_state',
                    'last_reviewed_at',
                    'last_reviewed_by',
                ]);
            }
        );
    }
};