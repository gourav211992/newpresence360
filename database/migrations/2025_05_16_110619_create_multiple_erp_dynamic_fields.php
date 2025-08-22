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
        Schema::create('erp_mr_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_mi_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_psv_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_pl_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_rc_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_si_dynamic_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('header_id');
            $table->unsignedBigInteger('dynamic_field_id');
            $table->unsignedBigInteger('dynamic_field_detail_id');
            $table->string('name');
            $table->string('value')->nullable();
            $table->timestamps();
        });
        Schema::create('erp_tr_dynamic_fields', function (Blueprint $table) {
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
        Schema::dropIfExists('erp_tr_dynamic_fields');
        Schema::dropIfExists('erp_si_dynamic_fields');
        Schema::dropIfExists('erp_rc_dynamic_fields');
        Schema::dropIfExists('erp_pl_dynamic_fields');
        Schema::dropIfExists('erp_psv_dynamic_fields');
        Schema::dropIfExists('erp_mi_dynamic_fields');
        Schema::dropIfExists('erp_mr_dynamic_fields');
    }
};
