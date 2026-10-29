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
        Schema::table('payment_orders', function (Blueprint $table) {
            // Added right after 'status' column
            $table->string('or_no')
                ->nullable()
                ->after('status')
                ->comment('MTO Official Receipt Number');

            $table->date('payment_date')
                ->nullable()
                ->after('or_no')
                ->comment('Date payment was received at the Treasury');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->dropColumn(['or_no', 'payment_date']);
        });
    }
};
