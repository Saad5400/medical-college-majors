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
        Schema::table('facility_registration_requests', function (Blueprint $table) {
            // Nullable because it only applies to elective months; non-elective duration comes from the specialization
            $table->unsignedTinyInteger('elective_duration_months')->nullable()->after('month_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facility_registration_requests', function (Blueprint $table) {
            $table->dropColumn('elective_duration_months');
        });
    }
};
