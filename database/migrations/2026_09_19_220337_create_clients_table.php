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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            
            // Client Personal & Account Details
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->date('birthdate')->nullable();
            $table->text('address')->nullable();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('profile_picture')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};