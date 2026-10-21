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
        Schema::table('zone_locations', function (Blueprint $table) {
            // Add the foreign key constraint to the existing application_id column
            $table->foreign('application_id')
                  ->references('id')
                  ->on('loc_clearances')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zone_locations', function (Blueprint $table) {
            // Drop the foreign key constraint if rolled back
            $table->dropForeign(['application_id']);
        });
    }
};