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
        Schema::create('erp_maintenance_checklist_detail_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('erp_maintenance_id');
            $table->unsignedBigInteger('erp_equip_maintenance_checklist_id');

            $table->string('checklist_name')->nullable();
            $table->string('checklist_answer');

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
        Schema::dropIfExists('erp_maintenance_checklist_detail_histories');
    }
};
