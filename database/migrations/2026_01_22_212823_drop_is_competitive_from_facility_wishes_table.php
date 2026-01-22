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
        Schema::table('facility_wishes', function (Blueprint $table) {
            $table->dropIndex(['facility_registration_request_id', 'is_competitive']);
            $table->dropColumn('is_competitive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_wishes', function (Blueprint $table) {
            $table->boolean('is_competitive')->default(true);
            $table->index(['facility_registration_request_id', 'is_competitive']);
        });
    }
};
