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
        Schema::create('train_routes', function (Blueprint $table) {
            $table->id();
            $table->string('train_number')->unique();
            $table->string('departure_station');
            $table->string('arrival_station');
            $table->dateTime('departure_time');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('train_routes');
    }
};
