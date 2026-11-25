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
        Schema::table('train_routes', function (Blueprint $table) {
            $table->integer('car_count')->default(10)->after('base_price');
            $table->integer('seats_per_car')->default(54)->after('car_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('train_routes', function (Blueprint $table) {
            $table->dropColumn(['car_count', 'seats_per_car']);
        });
    }
};
