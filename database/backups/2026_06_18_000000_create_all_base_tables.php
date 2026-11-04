<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('staff_number')->nullable()->unique();
            $table->string('phone_number', 20)->nullable();
            $table->text('signature_path')->nullable();
            $table->string('signature_filename', 255)->nullable();
            $table->boolean('use_auto_signature')->default(false);
            $table->timestamp('signature_updated_at')->nullable();
            $table->string('station')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->enum('role', ['engineer', 'checker', 'supervisor', 'admin', 'manager', 'customer', 'technician', 'investigator'])->default('customer');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->text('session_id')->nullable();
            $table->string('legacy_role')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['role', 'is_active']);
            $table->index(['status', 'created_at']);
            $table->index('last_login_at');
            $table->index('staff_number');
        });

        // 2. Create password_reset_tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. Create sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 4. Create customers table (based on your existing structure)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address');
            $table->string('tax_id')->nullable();
            $table->decimal('credit_limit', 12, 2)->nullable();
            $table->string('payment_terms')->default('30_days');
            $table->timestamp('registration_date')->nullable();
            $table->enum('status', ['active', 'pending', 'suspended'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->json('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'is_active']);
            $table->index('company_name');
            $table->index('email');
            $table->index('phone');
            $table->index('created_at');
        });

        // 5. Create equipments table
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('serial_number')->unique();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('accuracy_class')->nullable();
            $table->string('status')->default('active');
            $table->json('specifications')->nullable();
            $table->timestamps();

            $table->index('serial_number');
            $table->index(['customer_id', 'status']);
        });

        // 6. Create calibrations table
        Schema::create('calibrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipments')->onDelete('cascade');
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending');
            $table->json('test_parameters')->nullable();
            $table->json('test_results')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('scheduled_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['equipment_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['performed_by', 'created_at']);
            $table->index('scheduled_date');
        });

        // 7. Create test_results table
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_id')->constrained('calibrations')->onDelete('cascade');
            $table->string('parameter');
            $table->decimal('nominal_value', 15, 6);
            $table->decimal('measured_value', 15, 6);
            $table->decimal('tolerance_min', 15, 6);
            $table->decimal('tolerance_max', 15, 6);
            $table->string('unit', 20);
            $table->boolean('is_within_tolerance')->default(false);
            $table->decimal('error_percentage', 10, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['calibration_id', 'created_at']);
            $table->index('is_within_tolerance');
        });

        // 8. Create certificates table
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_id')->constrained('calibrations')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('certificate_number')->unique();
            $table->string('pdf_path')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->timestamp('issued_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->string('signed_by')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('certificate_number');
            $table->index(['customer_id', 'created_at']);
            $table->index(['calibration_id']);
            $table->index(['issued_date', 'expiry_date']);
        });

        // 9. Create job_cards table
        Schema::create('job_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('job_number')->unique();
            $table->string('status')->default('pending');
            $table->text('description')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('job_number');
            $table->index(['customer_id', 'status']);
        });

        // 10. Create service_types table
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });

        // 11. Create meter_types table
        Schema::create('meter_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('accuracy_class')->nullable();
            $table->string('voltage_rating')->nullable();
            $table->string('current_rating')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });

        // 12. Create test_parameters table
        Schema::create('test_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit', 20);
            $table->decimal('default_nominal', 15, 6)->nullable();
            $table->decimal('default_tolerance_min', 15, 6)->nullable();
            $table->decimal('default_tolerance_max', 15, 6)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('name');
            $table->index('category');
            $table->index('is_active');
        });

        // 13. Create complaints table
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('calibration_id')->nullable()->constrained('calibrations')->onDelete('set null');
            $table->string('reference_number')->unique();
            $table->string('subject');
            $table->text('description');
            $table->string('status')->default('open');
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('reference_number');
            $table->index(['customer_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        // 14. Create dispatches table
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('dispatch_number')->unique();
            $table->string('status')->default('pending');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();

            $table->index('dispatch_number');
            $table->index(['customer_id', 'status']);
        });

        // 15. Create dispatch_equipments table
        Schema::create('dispatch_equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained('dispatches')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipments')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->text('condition_notes')->nullable();
            $table->timestamps();

            $table->index(['dispatch_id', 'equipment_id']);
        });

        // 16. Create quotes table
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('quote_number')->unique();
            $table->date('valid_until')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->text('terms')->nullable();
            $table->timestamps();

            $table->index('quote_number');
            $table->index(['customer_id', 'status']);
            $table->index('valid_until');
        });

        // 17. Create quote_items table
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->onDelete('cascade');
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->timestamps();

            $table->index('quote_id');
        });

        // 18. Create diary_entries table
        Schema::create('diary_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('entry_date');
            $table->string('status')->default('pending');
            $table->string('entry_type')->default('task');
            $table->timestamps();

            $table->index(['user_id', 'entry_date']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down()
    {
        // Drop tables in reverse order (to handle foreign keys)
        Schema::dropIfExists('diary_entries');
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('dispatch_equipments');
        Schema::dropIfExists('dispatches');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('test_parameters');
        Schema::dropIfExists('meter_types');
        Schema::dropIfExists('service_types');
        Schema::dropIfExists('job_cards');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('test_results');
        Schema::dropIfExists('calibrations');
        Schema::dropIfExists('equipments');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
