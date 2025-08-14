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
        Schema::create('erp_finance_fixed_asset_revaluation_impairement_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id');
            $table->string('document_number');
            $table->date('document_date')->nullable();
            $table->enum('doc_number_type', ['Auto', 'Manually'])->default('Manually');
            $table->enum('doc_reset_pattern', ['Never', 'Yearly', 'Quarterly', 'Monthly'])->nullable();
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();

           
            $table->json('asset_details'); 
            $table->string('document_type',250);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('document',250)->nullable();
            $table->string('reamrks',250)->nullable();
            

            
            
            $table->string('document_status', 50);
            $table->integer('approval_level')->default(1);
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('type', 100);
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_finance_fixed_asset_revaluation_impairement_history');
    }
};
