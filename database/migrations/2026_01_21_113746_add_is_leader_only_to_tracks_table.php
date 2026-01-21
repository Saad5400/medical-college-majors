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
        Schema::table('tracks', function (Blueprint $table) {
            $table->boolean('is_leader_only')->default(false)->after('max_users');
            // JSON array of month indices (1-12) that are elective
            $table->json('elective_months')->nullable()->after('is_leader_only');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn(['is_leader_only', 'elective_months']);
        });
    }
};
