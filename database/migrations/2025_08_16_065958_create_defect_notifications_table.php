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
        Schema::create('erp_defect_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable(); // User class type
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->integer('approval_level')->default(1);
            $table->integer('revision_number')->default(0);
            $table->date('revision_date')->nullable();
            $table->string('book_code', 100)->nullable();
            $table->unsignedBigInteger('book_id');
            $table->string('document_number', 100);
            $table->date('document_date');
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();
            $table->string('doc_number_type')->nullable();
            $table->string('doc_reset_pattern')->nullable();
            $table->string('document_status', 50);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('equipment_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('defect_type_id')->nullable();
            $table->string('problem')->nullable();
            $table->string('priority')->nullable();
            $table->datetime('report_date_time')->nullable();
            $table->string('attachment',50)->nullable();
            $table->string('detailed_oberservation')->nullable();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->index();
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
        Schema::dropIfExists('defect_notifications');
    }
};
