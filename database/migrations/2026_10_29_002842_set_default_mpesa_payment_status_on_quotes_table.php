<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Sets the default value to 'pending' so it never inserts NULL
            $table->string('mpesa_payment_status')->default('unpaid')->change();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Revert back to no default
            $table->string('mpesa_payment_status')->default(null)->change();
        });
    }
};
