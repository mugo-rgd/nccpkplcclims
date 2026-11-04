<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'calibrations',
            function (Blueprint $table): void {
                $table
                    ->string('request_form_path')
                    ->nullable();

                $table
                    ->string('delivery_note_path')
                    ->nullable();

                $table
                    ->string('collection_note_path')
                    ->nullable();
                    $table
                    ->string('jobcard_form_path')
                    ->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'calibrations',
            function (Blueprint $table): void {
                $table->dropColumn([
                    'request_form_path',
                    'delivery_note_path',
                    'collection_note_path',
                    'jobcard_form_path',
                ]);
            }
        );
    }
};
