<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'public_quote_requests',
            function (Blueprint $table): void {

                $table
                    ->foreignId('equipment_type_id')
                    ->nullable()
                    ->after('company')
                    ->constrained(
                        'kplc_equipment_types'
                    )
                    ->restrictOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'public_quote_requests',
            function (Blueprint $table): void {

                $table->dropConstrainedForeignId(
                    'equipment_type_id'
                );
            }
        );
    }
};
