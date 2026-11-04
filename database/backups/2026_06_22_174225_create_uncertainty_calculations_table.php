<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uncertainty_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_result_id')->constrained('test_results')->cascadeOnDelete();

            $table->string('load_type'); // low_load, base_load, inductive_load, maximum_load

            // Standard uncertainties
            $table->decimal('u1_repeatability', 10, 6)->nullable();
            $table->decimal('u2_reference_std', 10, 6)->nullable();
            $table->decimal('u3_resolution', 10, 6)->nullable();

            // Combined uncertainty
            $table->decimal('combined_uncertainty', 10, 6)->nullable();
            $table->decimal('expanded_uncertainty', 10, 6)->nullable();
            $table->decimal('confidence_level', 5, 2)->default(95.00);

            $table->timestamps();

            $table->index('test_result_id');
            $table->index('load_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('uncertainty_calculations');
    }
};
