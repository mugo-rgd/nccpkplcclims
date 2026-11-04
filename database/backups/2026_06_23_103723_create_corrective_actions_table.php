<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->string('car_id', 20)->unique();
            $table->foreignId('objective_id')->constrained('quality_objectives')->onDelete('cascade');
            $table->text('issue_description');
            $table->text('root_cause')->nullable();
            $table->text('action_plan');
            $table->foreignId('action_owner_id')->constrained('users')->onDelete('restrict');
            $table->date('deadline');
            $table->string('status')->default('Open');
            $table->date('opened_date');
            $table->date('closed_date')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('deadline');
            $table->index('action_owner_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('corrective_actions');
    }
};
