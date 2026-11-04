<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'public_quote_requests',
            function (Blueprint $table): void {

                $table->id();

                $table
                    ->string('reference_number')
                    ->unique();

                $table
                    ->string('name');

                $table
                    ->string('email');

                $table
                    ->string('phone', 50);

                $table
                    ->string('company')
                    ->nullable();

                $table
                    ->string('meter_type', 100);

                $table
                    ->unsignedInteger('quantity')
                    ->default(1);

                $table
                    ->text('requirements')
                    ->nullable();

                /*
                 * Workflow:
                 *
                 * pending
                 * reviewed
                 * converted
                 * rejected
                 */
                $table
                    ->string('status', 30)
                    ->default('pending');

                /*
                 * Once MCL converts the enquiry into
                 * an actual calibration request.
                 */
                $table
                    ->foreignId('calibration_id')
                    ->nullable()
                    ->constrained('calibrations')
                    ->nullOnDelete();

                /*
                 * Staff member who reviewed/converted it.
                 */
                $table
                    ->foreignId('reviewed_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table
                    ->timestamp('reviewed_at')
                    ->nullable();

                $table
                    ->timestamp('converted_at')
                    ->nullable();

                $table
                    ->text('internal_notes')
                    ->nullable();

                $table->timestamps();

                $table->index('status');
                $table->index('email');
                $table->index('phone');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'public_quote_requests'
        );
    }
};
