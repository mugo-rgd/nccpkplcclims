<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quarterly_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('quality_objectives')->onDelete('cascade');
            $table->foreignId('fyr_period_id')->constrained('quality_fyr_periods')->onDelete('cascade');
            $table->string('quarter', 20); // Q1, Q2, Q3, Q4
            $table->string('quarter_label', 30); // e.g., "Sept 2023"
            $table->decimal('actual_achievement', 10, 2);
            $table->date('data_collection_date');
            $table->foreignId('collected_by_id')->constrained('users')->onDelete('restrict');
            $table->text('remarks')->nullable();
            $table->string('evidence_url')->nullable();
            $table->boolean('alert_sent')->default(false);
            $table->timestamps();

            $table->unique(['objective_id', 'fyr_period_id', 'quarter'], 'unique_quarterly_achievement');
            $table->index(['fyr_period_id', 'quarter']);
            $table->index(['objective_id', 'fyr_period_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quarterly_achievements');
    }
};
