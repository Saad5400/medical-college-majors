<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert track_id from varchar to bigint using explicit casting
        DB::statement('ALTER TABLE users ALTER COLUMN track_id TYPE bigint USING track_id::bigint');

        // Add foreign key constraint
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('track_id')->references('id')->on('tracks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['track_id']);
        });

        // Convert track_id back to varchar
        DB::statement('ALTER TABLE users ALTER COLUMN track_id TYPE varchar(255) USING track_id::varchar');
    }
};
