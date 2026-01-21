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
        Schema::create('facility_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month_index'); // 1-12 - the month this request is for
            $table->foreignId('assigned_facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->timestamps();

            // Each user can only have one request per month
            $table->unique(['user_id', 'month_index']);

            $table->index(['month_index', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_registration_requests');
    }
};
