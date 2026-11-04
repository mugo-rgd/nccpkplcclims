<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('equipment', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('equipment', 'name')) {
                $table->string('name')->nullable()->after('equipment_number');
            }

            if (!Schema::hasColumn('equipment', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (!Schema::hasColumn('equipment', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('description');
            }

            if (!Schema::hasColumn('equipment', 'type_id')) {
                $table->foreignId('type_id')->nullable()->after('category_id');
            }

            if (!Schema::hasColumn('equipment', 'manufacturer_id')) {
                $table->foreignId('manufacturer_id')->nullable()->after('type_id');
            }

            // manufacturer already exists as string, keep it

            if (!Schema::hasColumn('equipment', 'part_number')) {
                $table->string('part_number')->nullable()->after('model');
            }

            // type already exists as string, keep it

            if (!Schema::hasColumn('equipment', 'ratings')) {
                $table->text('ratings')->nullable()->after('accuracy_class');
            }

            if (!Schema::hasColumn('equipment', 'calibration_laboratory')) {
                $table->string('calibration_laboratory')->nullable()->after('ratings');
            }

            if (!Schema::hasColumn('equipment', 'calibration_interval_days')) {
                $table->integer('calibration_interval_days')->default(365)->after('next_calibration_date');
            }

            if (!Schema::hasColumn('equipment', 'condition')) {
                $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'needs_repair'])->default('good')->after('status');
            }

            if (!Schema::hasColumn('equipment', 'location')) {
                $table->string('location')->nullable()->after('condition');
            }

            if (!Schema::hasColumn('equipment', 'assigned_to')) {
                $table->string('assigned_to')->nullable()->after('location');
            }

            // notes already exists, keep it

            if (!Schema::hasColumn('equipment', 'training_required')) {
                $table->text('training_required')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('equipment', 'procurement_notes')) {
                $table->text('procurement_notes')->nullable()->after('training_required');
            }

            if (!Schema::hasColumn('equipment', 'purchase_date')) {
                $table->date('purchase_date')->nullable()->after('procurement_notes');
            }

            if (!Schema::hasColumn('equipment', 'purchase_price')) {
                $table->decimal('purchase_price', 15, 2)->nullable()->after('purchase_date');
            }

            if (!Schema::hasColumn('equipment', 'supplier')) {
                $table->string('supplier')->nullable()->after('purchase_price');
            }

            if (!Schema::hasColumn('equipment', 'warranty_until')) {
                $table->string('warranty_until')->nullable()->after('supplier');
            }

            if (!Schema::hasColumn('equipment', 'technical_specifications')) {
                $table->json('technical_specifications')->nullable()->after('warranty_until');
            }

            if (!Schema::hasColumn('equipment', 'accessories')) {
                $table->json('accessories')->nullable()->after('technical_specifications');
            }

            if (!Schema::hasColumn('equipment', 'documents')) {
                $table->json('documents')->nullable()->after('accessories');
            }

            if (!Schema::hasColumn('equipment', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('documents');
            }

            if (!Schema::hasColumn('equipment', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by');
            }
        });
    }

    public function down()
    {
        Schema::table('equipment', function (Blueprint $table) {
            // Drop columns if they exist
            $columns = [
                'name', 'description', 'category_id', 'type_id', 'manufacturer_id',
                'part_number', 'ratings', 'calibration_laboratory', 'calibration_interval_days',
                'condition', 'location', 'assigned_to', 'training_required', 'procurement_notes',
                'purchase_date', 'purchase_price', 'supplier', 'warranty_until',
                'technical_specifications', 'accessories', 'documents', 'created_by', 'updated_by'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('equipment', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
