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
        Schema::create('facility_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialization_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month_index'); // 1-12
            $table->unsignedInteger('max_seats')->default(0);
            $table->timestamps();

            // Unique constraint: one entry per facility, specialization, and month
            $table->unique(['facility_id', 'specialization_id', 'month_index'], 'facility_spec_month_unique');

            // Indexes for efficient queries
            $table->index(['facility_id', 'month_index']);
            $table->index(['specialization_id', 'month_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_seats');
    }
};
