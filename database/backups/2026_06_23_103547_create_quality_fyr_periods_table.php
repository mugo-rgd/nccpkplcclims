<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quality_fyr_periods', function (Blueprint $table) {
            $table->id();
            $table->string('fyr', 20)->unique(); // e.g., "2023-2024"
            $table->string('display_name', 50); // e.g., "FYR 2023/2024"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->json('quarters')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'is_closed']);
            $table->index('start_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('quality_fyr_periods');
    }
};
