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
        Schema::create('erp_equip_maintenance_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('erp_equip_maintenance_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            
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
        Schema::dropIfExists('erp_equip_maintenance_checklists');
    }
};
