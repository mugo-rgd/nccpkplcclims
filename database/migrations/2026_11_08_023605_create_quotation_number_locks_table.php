
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_number_locks', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
        });

        DB::table('quotation_number_locks')->insert([
            'id' => 1,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_number_locks');
    }
};
