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
            
            // Common fields (same as BOM controller pattern)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('type')->nullable(); // User class type
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->integer('approval_level')->default(1);
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->string('book_code', 100)->nullable();
            
            // Document fields (from form)
            $table->unsignedBigInteger('book_id');
            $table->string('document_number', 100);
            $table->date('document_date');
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();
            $table->string('doc_number_type')->nullable();
            $table->string('doc_reset_pattern')->nullable();
            $table->enum('document_status', ['draft', 'submitted', 'approved', 'rejected', 'completed'])->default('draft');
            
            // Location and Equipment fields
            $table->unsignedBigInteger('location_id')->nullable();
            
            // Form data fields (JSON)
            $table->json('spare_parts')->nullable(); // Spare parts data
            $table->json('checklist_data')->nullable(); // Checklist data
            $table->json('equipment_details')->nullable(); // Equipment details data
            
            $table->text('final_remark')->nullable();
            $table->string('upload_file')->nullable();

            // Standard fields
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->index();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['organization_id', 'group_id', 'company_id']);
            $table->index('document_number');
            $table->index('document_status');
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
