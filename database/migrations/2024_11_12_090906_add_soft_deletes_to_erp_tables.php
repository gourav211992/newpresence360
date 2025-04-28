<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSoftDeletesToErpTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_alternate_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_alternate_uoms', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_item_subtypes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_customers', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_notes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_bank_infos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_currency', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_hsns', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_units', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_bank_details', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_product_section_details', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_item_specifications', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('erp_product_specification_details', function (Blueprint $table) {
            $table->softDeletes();
        });

        $this->moveDeletedAtAfterUpdatedAt('erp_alternate_items');
        $this->moveDeletedAtAfterUpdatedAt('erp_alternate_uoms');
        $this->moveDeletedAtAfterUpdatedAt('erp_item_subtypes');
        $this->moveDeletedAtAfterUpdatedAt('erp_customers');
        $this->moveDeletedAtAfterUpdatedAt('erp_notes');
        $this->moveDeletedAtAfterUpdatedAt('erp_bank_infos');
        $this->moveDeletedAtAfterUpdatedAt('erp_vendors');
        $this->moveDeletedAtAfterUpdatedAt('erp_currency');
        $this->moveDeletedAtAfterUpdatedAt('erp_hsns');
        $this->moveDeletedAtAfterUpdatedAt('erp_units');
        $this->moveDeletedAtAfterUpdatedAt('erp_bank_details');
        $this->moveDeletedAtAfterUpdatedAt('erp_product_section_details');
        $this->moveDeletedAtAfterUpdatedAt('erp_item_specifications');
        $this->moveDeletedAtAfterUpdatedAt('erp_product_specification_details');
    }


    protected function moveDeletedAtAfterUpdatedAt(string $table)
    {
        DB::statement("ALTER TABLE {$table} MODIFY COLUMN deleted_at TIMESTAMP NULL AFTER updated_at");
    }

    public function down()
    {
        Schema::table('erp_alternate_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_alternate_uoms', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_item_subtypes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_notes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_bank_infos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_currency', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_hsns', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_units', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_bank_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_product_section_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_item_specifications', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('erp_product_specification_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
