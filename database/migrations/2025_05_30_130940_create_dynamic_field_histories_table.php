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
        if (!Schema::hasTable('erp_so_dynamic_fields_history')) {
            Schema::create('erp_so_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('erp_sr_dynamic_fields_history')) {
            Schema::create('erp_sr_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('erp_mr_dynamic_fields_history')) {
            Schema::create('erp_mr_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('erp_mi_dynamic_fields_history')) {
            Schema::create('erp_mi_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('erp_psv_dynamic_fields_history')) {
            Schema::create('erp_psv_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('erp_pl_dynamic_fields_history')) {
            Schema::create('erp_pl_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('erp_rc_dynamic_fields_history')) {
            Schema::create('erp_rc_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('erp_si_dynamic_fields_history')) {
            Schema::create('erp_si_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('erp_tr_dynamic_fields_history')) {
            Schema::create('erp_tr_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('erp_mrn_dynamic_fields_history')) {
            Schema::create('erp_mrn_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('erp_ge_dynamic_fields_history')) {
            Schema::create('erp_ge_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('erp_exp_dynamic_fields_history')) {
            Schema::create('erp_exp_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('erp_pb_dynamic_fields_history')) {
            Schema::create('erp_pb_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('erp_pr_dynamic_fields_history')) {
            Schema::create('erp_pr_dynamic_fields_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('header_id');
                $table->unsignedBigInteger('dynamic_field_id');
                $table->unsignedBigInteger('dynamic_field_detail_id');
                $table->string('name');
                $table->string('value')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_pr_dynamic_fields_history');
        Schema::dropIfExists('erp_pb_dynamic_fields_history');
        Schema::dropIfExists('erp_exp_dynamic_fields_history');
        Schema::dropIfExists('erp_ge_dynamic_fields_history');
        Schema::dropIfExists('erp_mrn_dynamic_fields_history');
        Schema::dropIfExists('erp_tr_dynamic_fields_history');
        Schema::dropIfExists('erp_si_dynamic_fields_history');
        Schema::dropIfExists('erp_rc_dynamic_fields_history');
        Schema::dropIfExists('erp_pl_dynamic_fields_history');
        Schema::dropIfExists('erp_psv_dynamic_fields_history');
        Schema::dropIfExists('erp_mi_dynamic_fields_history');
        Schema::dropIfExists('erp_mr_dynamic_fields_history');
        Schema::dropIfExists('erp_sr_dynamic_fields_history');
        Schema::dropIfExists('erp_so_dynamic_fields_history');
    }
};
