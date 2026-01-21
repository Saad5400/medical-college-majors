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
        // Rename the majors table to tracks
        Schema::rename('majors', 'tracks');

        // Rename the pivot table
        Schema::rename('major_registration_request', 'track_registration_requests');

        // Update the foreign key column in the pivot table
        Schema::table('track_registration_requests', function (Blueprint $table) {
            $table->renameColumn('major_id', 'track_id');
        });

        // Update the foreign key column in the users table
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('major_id', 'track_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename columns back
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('track_id', 'major_id');
        });

        Schema::table('track_registration_requests', function (Blueprint $table) {
            $table->renameColumn('track_id', 'major_id');
        });

        // Rename tables back
        Schema::rename('track_registration_requests', 'major_registration_request');
        Schema::rename('tracks', 'majors');
    }
};
