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
        Schema::create('erp_plant_maint_wo_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();

            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('equipment_id');

            $table->string('doc_date')->nullable();

            $table->string('upload_document')->nullable();
            $table->text('final_remarks')->nullable();

            $table->unsignedBigInteger('book_id');
            $table->string('document_number');
            $table->date('document_date')->nullable();
             $table->string('doc_number_type')->default('Manually'); 
            $table->string('doc_reset_pattern')->nullable();  
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();

            $table->string('document_status', 50);
            $table->integer('approval_level')->default(1);
            $table->string('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->string('reference_number')->nullable();

            $table->string('status')->default('active')->index();
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
        Schema::dropIfExists('plant_maint_wo_histories');
    }
};
