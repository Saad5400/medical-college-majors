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
        Schema::create('major_registration_request', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('major_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_request_id')->constrained()->cascadeOnDelete();
            $table->integer('sort')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('major_registration_request');
    }
};
