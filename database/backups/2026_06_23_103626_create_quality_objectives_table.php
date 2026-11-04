<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quality_objectives', function (Blueprint $table) {
            $table->id();
            $table->string('objective_id', 20)->unique();
            $table->string('section', 50);
            $table->text('description');
            $table->string('unit_of_measure', 20);

            // Dynamic fields - no hardcoded years
            $table->decimal('baseline_value', 10, 2)->nullable();
            $table->string('baseline_fyr', 20)->nullable();
            $table->foreign('baseline_fyr')->references('fyr')->on('quality_fyr_periods')->onDelete('set null');

            $table->decimal('target_value', 10, 2);
            $table->string('target_fyr', 20);
            $table->foreign('target_fyr')->references('fyr')->on('quality_fyr_periods')->onDelete('cascade');

            // For multi-year tracking
            $table->json('historical_targets')->nullable();

            $table->foreignId('owner_id')->constrained('users')->onDelete('restrict');
            $table->string('status')->default('active');
            $table->string('iso17025_clause')->nullable();
            $table->string('iso17025_requirement')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['section', 'status']);
            $table->index('owner_id');
            $table->index(['target_fyr', 'section']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quality_objectives');
    }
};
