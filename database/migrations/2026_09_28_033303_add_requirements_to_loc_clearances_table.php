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
            $table->json('requirements')->nullable()->after('encoded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loc_clearances', function (Blueprint $table) {
            $table->dropColumn('requirements');
        });
    }
};
