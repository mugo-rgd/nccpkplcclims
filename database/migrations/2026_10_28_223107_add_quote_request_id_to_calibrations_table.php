<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->foreignId('quote_request_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('public_quote_requests')
                ->nullOnDelete();

            $table->index('quote_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('calibrations', function (Blueprint $table) {
            $table->dropForeign(['quote_request_id']);
            $table->dropIndex(['quote_request_id']);
            $table->dropColumn('quote_request_id');
        });
    }
};
