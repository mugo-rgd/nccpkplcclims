<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            // Add all columns without using 'after' (which requires the column to exist)

            if (!Schema::hasColumn('certificates', 'reference_number')) {
                $table->string('reference_number')->nullable();
            }

            if (!Schema::hasColumn('certificates', 'service_name')) {
                $table->string('service_name')->nullable();
            }

            if (!Schema::hasColumn('certificates', 'service_method')) {
                $table->string('service_method')->nullable();
            }

            if (!Schema::hasColumn('certificates', 'service_notes')) {
                $table->text('service_notes')->nullable();
            }

            if (!Schema::hasColumn('certificates', 'voltage')) {
                $table->decimal('voltage', 15, 2)->nullable();
            }

            if (!Schema::hasColumn('certificates', 'current')) {
                $table->decimal('current', 15, 2)->nullable();
            }

            if (!Schema::hasColumn('certificates', 'temperature')) {
                $table->decimal('temperature', 15, 2)->nullable();
            }

            if (!Schema::hasColumn('certificates', 'humidity')) {
                $table->decimal('humidity', 15, 2)->nullable();
            }

            if (!Schema::hasColumn('certificates', 'equipment_constant')) {
                $table->decimal('equipment_constant', 15, 2)->nullable();
            }

            if (!Schema::hasColumn('certificates', 'equipment_class')) {
                $table->string('equipment_class')->nullable();
            }

            if (!Schema::hasColumn('certificates', 'wiring')) {
                $table->string('wiring')->nullable();
            }

            if (!Schema::hasColumn('certificates', 'traceability_information')) {
                $table->text('traceability_information')->nullable();
            }

            if (!Schema::hasColumn('certificates', 'statement_conformity')) {
                $table->text('statement_conformity')->nullable();
            }

            if (!Schema::hasColumn('certificates', 'comments')) {
                $table->text('comments')->nullable();
            }

            if (!Schema::hasColumn('certificates', 'sent_by')) {
                $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('certificates', 'sent_at')) {
                $table->timestamp('sent_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $columns = [
                'reference_number',
                'service_name',
                'service_method',
                'service_notes',
                'voltage',
                'current',
                'temperature',
                'humidity',
                'equipment_constant',
                'equipment_class',
                'wiring',
                'traceability_information',
                'statement_conformity',
                'comments',
                'sent_by',
                'sent_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('certificates', $column)) {
                    if ($column === 'sent_by') {
                        try {
                            $table->dropForeign(['sent_by']);
                        } catch (\Exception $e) {
                            // Foreign key might not exist
                        }
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
