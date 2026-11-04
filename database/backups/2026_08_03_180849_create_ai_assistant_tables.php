<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title')->nullable();

            $table
                ->string('status', 30)
                ->default('active');

            $table
                ->string('openai_response_id')
                ->nullable()
                ->index();

            $table->timestamps();
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('conversation_id')
                ->constrained('ai_conversations')
                ->cascadeOnDelete();

            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('role', 30);

            $table->longText('content');

            $table
                ->json('metadata')
                ->nullable();

            $table
                ->unsignedInteger('input_tokens')
                ->nullable();

            $table
                ->unsignedInteger('output_tokens')
                ->nullable();

            $table->timestamps();

            $table->index([
                'conversation_id',
                'created_at',
            ]);
        });

        Schema::create('ai_tool_logs', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('conversation_id')
                ->nullable()
                ->constrained('ai_conversations')
                ->nullOnDelete();

            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('tool_name');

            $table->json('arguments')->nullable();

            $table->json('result')->nullable();

            $table->boolean('successful')->default(true);

            $table->text('error_message')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tool_logs');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
