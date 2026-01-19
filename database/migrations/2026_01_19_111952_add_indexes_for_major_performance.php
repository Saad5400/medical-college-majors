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
        Schema::table('users', function (Blueprint $table) {
            $table->index('major_id');
        });

        Schema::table('major_registration_request', function (Blueprint $table) {
            $table->index(['registration_request_id', 'sort']);
            $table->index('major_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['major_id']);
        });

        Schema::table('major_registration_request', function (Blueprint $table) {
            $table->dropIndex(['registration_request_id', 'sort']);
            $table->dropIndex(['major_id']);
        });
    }
};
