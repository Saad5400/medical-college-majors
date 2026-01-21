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
        Schema::create('facility_wishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_registration_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('priority'); // 1-5, lower is higher priority

            // For registered facilities
            $table->foreignId('facility_id')->nullable()->constrained()->nullOnDelete();
            // For elective months - custom specialization
            $table->foreignId('specialization_id')->nullable()->constrained()->nullOnDelete();

            // For custom/unregistered facilities and specializations
            $table->string('custom_facility_name')->nullable();
            $table->string('custom_specialization_name')->nullable();

            // Flags
            $table->boolean('is_custom')->default(false); // True if using custom facility/specialization
            $table->boolean('is_competitive')->default(true); // False once is_custom becomes true (and all after)

            $table->timestamps();

            // Each request can only have one wish per priority
            $table->unique(['facility_registration_request_id', 'priority'], 'request_priority_unique');

            $table->index(['facility_registration_request_id', 'is_competitive']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_wishes');
    }
};
