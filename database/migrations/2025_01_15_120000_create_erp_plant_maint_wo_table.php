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
        Schema::create('erp_plant_maint_wo', function (Blueprint $table) {
            $table->id();

            // Organization and Company Information
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            
            // Book and Document Information
            $table->unsignedBigInteger('book_id');
            $table->string('book_code');
            $table->string('document_number');
            $table->date('document_date')->nullable();
            $table->string('doc_number_type')->default('Manually');
            $table->string('doc_reset_pattern')->nullable();
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();
            
            // Location Information
            $table->unsignedBigInteger('location_id')->nullable();
            
            // Reference Information
            $table->enum('reference_type', ['equipment', 'defect'])->nullable();
            $table->json('equipment_details')->nullable()->comment('JSON containing all equipment and defect details: equipment_id, category, maintenance_type, priority, scheduled_date, defect_notification_id, defect_type, problem_description, report_date_time, reported_by, detailed_observations, etc.');
            
            // Work Order Data (JSON fields as requested)
            $table->json('spare_parts')->nullable()->comment('JSON array of spare parts data');
            $table->json('checklist_data')->nullable()->comment('JSON array of checklist items');
            
            // Document Management
            $table->string('document_status', 50)->default('Draft');
            $table->integer('approval_level')->default(1);
            $table->string('upload_file')->nullable();
            $table->text('final_remark')->nullable();
            
            // Supporting Documents (from form analysis)
            $table->json('supporting_documents')->nullable()->comment('JSON array of supporting document file paths');
            
            // Completion Information
            $table->date('completion_date')->nullable();
            $table->text('work_description')->nullable();
            $table->text('work_performed')->nullable();
            
            // Revision and Approval
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            
            // Audit Fields
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
