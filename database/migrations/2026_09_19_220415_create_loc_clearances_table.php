<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loc_clearances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');

            // Header Info
            $table->string('application_no')->nullable();
            $table->date('date_issued')->nullable();
            $table->date('date_of_receipt')->nullable();
            $table->string('or_number')->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();

            // 1-6. Applicant & Representative Info
            $table->string('applicant_name');
            $table->string('corporation_name')->nullable();
            $table->string('representative_address_tel')->nullable();
            $table->string('corporation_address_tel')->nullable();
            $table->string('authorized_representative_name')->nullable();
            $table->string('authorized_representative_address_tel')->nullable();

            // 7-10. Project Specs & Measurements
            $table->string('project_type')->nullable();
            $table->string('project_nature')->nullable();
            $table->text('project_location')->nullable();
            $table->decimal('lot_area_sqm', 10, 2)->nullable();
            $table->decimal('building_area_sqm', 10, 2)->nullable();
            $table->decimal('perimeter_m', 10, 2)->nullable();

            // 11-12. Tenure & Right Over Land
            $table->string('right_over_land')->nullable(); // owner, heirs, lease, others
            $table->string('right_over_land_specify')->nullable();
            $table->string('project_tenure')->nullable(); // permanent, temporary

            // 13. Existing Land Use
            $table->string('existing_land_use')->nullable(); // residential, commercial, institutional, industrial, vacant_idle, agriculture, others
            $table->string('existing_land_use_specify')->nullable();
            $table->string('agriculture_crops_specify')->nullable();
            $table->boolean('is_tenanted')->default(false);

            // 14. Project Cost
            $table->decimal('project_cost', 15, 2)->nullable();

            // 15. Written Notice Details
            $table->boolean('has_written_notice')->default(false);
            $table->string('notice_issuing_officer')->nullable();
            $table->string('notice_order_number')->nullable();
            $table->date('notice_date')->nullable();

            // 16. Related Action Details
            $table->boolean('has_related_action')->default(false);
            $table->string('action_office_filed')->nullable();
            $table->date('action_date_filed')->nullable();
            $table->string('action_taken')->nullable();

            // 17. Release Mode & Status
            $table->string('preferred_mode_of_release')->nullable(); // pick_up, mail_applicant, mail_representative
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loc_clearances');
    }
};
