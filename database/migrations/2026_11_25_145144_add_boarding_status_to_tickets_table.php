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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('boarding_status')->default('not_checked')->after('status');
            $table->timestamp('checked_at')->nullable()->after('boarding_status');
            $table->foreignId('checked_by')->nullable()->after('checked_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checked_by');
            $table->dropColumn(['boarding_status', 'checked_at']);
        });
    }
};
