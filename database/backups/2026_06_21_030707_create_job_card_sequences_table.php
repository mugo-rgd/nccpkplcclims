<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_card_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('year_month', 7)->unique();
            $table->integer('last_number')->default(0);
            $table->timestamps();
        });

        // Insert current month
        $yearMonth = date('Ym');
        DB::table('job_card_sequences')->insert([
            'year_month' => $yearMonth,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('job_card_sequences');
    }
};
