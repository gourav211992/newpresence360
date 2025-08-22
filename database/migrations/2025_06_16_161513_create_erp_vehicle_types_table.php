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

        if (! Schema::hasTable('erp_vehicle_types')) {
            Schema::create('erp_vehicle_types', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->unsignedBigInteger('company_id')->nullable();
                $table->string('name');
                $table->decimal('capacity', 10, 2)->nullable(); 
                $table->unsignedBigInteger('uom_id')->nullable(); 
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'inactive', 'block', 'transfer', 'blacklist'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vehicle_types');
    }
};
