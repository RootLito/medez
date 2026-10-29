<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_no')->unique();
            $table->foreignId('application_id')
                ->constrained('loc_clearances')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('inspector_id')->nullable();
            $table->string('inspection_type')->default('Ocular Inspection');
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'rescheduled', 'cancelled'])
                ->default('scheduled');
            $table->enum('compliance_status', ['pending', 'compliant', 'non_compliant', 'conditional'])
                ->default('pending');
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
