<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'file_path')) {
                $table->string('file_path')->nullable()->after('type');
            }

            if (!Schema::hasColumn('messages', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }

            if (!Schema::hasColumn('messages', 'file_type')) {
                $table->string('file_type')->nullable()->after('file_name');
            }

            if (!Schema::hasColumn('messages', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn([
                'file_path',
                'file_name',
                'file_type',
                'file_size',
            ]);
        });
    }
};
