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
        Schema::create('erp_pi_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_po_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_pwo_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_bom_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_mo_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_pslip_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_po_dynamic_fields');
        Schema::dropIfExists('erp_pi_dynamic_fields');
        Schema::dropIfExists('erp_mo_dynamic_fields');
        Schema::dropIfExists('erp_bom_dynamic_fields');
        Schema::dropIfExists('erp_pwo_dynamic_fields');
        Schema::dropIfExists('erp_pslip_dynamic_fields');
    }
};
