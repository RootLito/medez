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
        Schema::table('loc_clearances', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();
            $table->boolean('is_walk_in')->default(false)->after('status');
            $table->unsignedBigInteger('encoded_by')->nullable()->after('is_walk_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loc_clearances', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable(false)->change();
            $table->dropColumn(['is_walk_in', 'encoded_by']);
        });
    }
};