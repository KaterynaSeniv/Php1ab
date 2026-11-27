<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conductor_route_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conductor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('train_route_id')->constrained('train_routes')->cascadeOnDelete();
            $table->dateTime('shift_start');
            $table->dateTime('shift_end');
            $table->string('cars_scope')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->unique(['conductor_id', 'train_route_id']);
            $table->index(['conductor_id', 'shift_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conductor_route_assignments');
    }
};
