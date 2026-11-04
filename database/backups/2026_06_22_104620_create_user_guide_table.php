<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_guide', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category');
            $table->integer('order')->default(0);
            $table->text('content');
            $table->text('content_html')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_published')->default(true);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('user_guide_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_guide_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained('user_guide')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('helpful');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_guide_feedback');
        Schema::dropIfExists('user_guide_categories');
        Schema::dropIfExists('user_guide');
    }
};
