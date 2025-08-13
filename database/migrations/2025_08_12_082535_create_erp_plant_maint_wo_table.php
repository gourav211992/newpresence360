<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_plant_maint_wo', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id');
            $table->string('book_code');
            $table->string('document_number');
            $table->date('document_date')->nullable();
            $table->string('doc_number_type')->default('Manually');
            $table->string('doc_reset_pattern')->nullable();
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();
            
            // Reference Information
            $table->unsignedBigInteger('location_id')->nullable();
            $table->enum('reference_type', ['equipment', 'defect'])->nullable();
            $table->json('reference_details')->nullable()->comment('JSON containing equipment or defect notification details');
            
            // Equipment/Defect Details
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->unsignedBigInteger('defect_notification_id')->nullable();
            $table->string('category')->nullable();
            $table->enum('maintenance_type', ['Preventive', 'Corrective', 'Predictive', 'Breakdown'])->default('Preventive');
            $table->string('defect_type')->nullable();
            $table->text('problem_description')->nullable();
            
            // Priority and Scheduling
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->dateTime('report_date_time')->nullable();
            $table->unsignedBigInteger('reported_by')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('completion_date')->nullable();
            
            // Work Details
            $table->text('work_description')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('detailed_observations')->nullable();
            
            // Checklist and Spare Parts
            $table->json('checklist')->nullable()->comment('JSON array of checklist items');
            $table->json('spare_parts')->nullable()->comment('JSON array of spare parts');
            
            // Cost and Duration
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->integer('estimated_duration_minutes')->nullable();
            $table->integer('actual_duration_minutes')->nullable();
            
            // Status and Approval
            $table->string('status', 50)->default('Draft');
            $table->text('remarks')->nullable();

            $table->string('document_status', 50);
            $table->integer('approval_level')->default(1);
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_plant_maint_wo');
    }
};
