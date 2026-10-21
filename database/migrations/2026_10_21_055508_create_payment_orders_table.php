<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('loc_clearances')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->string('assessment_no')->unique();
            $table->decimal('project_cost', 15, 2);
            $table->string('category_type');
            $table->decimal('base_clearance_fee', 10, 2);
            $table->decimal('excess_cost', 10, 2)->default(0.00);
            $table->decimal('excess_fee', 10, 2)->default(0.00);
            $table->decimal('total_clearance_fee', 10, 2);
            $table->decimal('processing_fee', 10, 2)->default(0.00);
            $table->decimal('inspection_fee', 10, 2)->default(0.00);
            $table->decimal('surcharge_fee', 10, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2);
            $table->text('remarks')->nullable();
            $table->string('assessed_by');
            $table->string('approved_by');
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
