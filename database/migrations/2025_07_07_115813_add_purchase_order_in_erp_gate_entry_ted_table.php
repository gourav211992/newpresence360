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
        Schema::table('erp_gate_entry_ted', function (Blueprint $table) {
            $table->unsignedBigInteger('po_id') -> after('detail_id')->nullable();
            $table->unsignedBigInteger('jo_id') -> after('po_id')->nullable();
        });

        Schema::table('erp_gate_entry_ted_history', function (Blueprint $table) {
            $table->unsignedBigInteger('po_id') -> after('detail_id')->nullable();
            $table->unsignedBigInteger('jo_id') -> after('po_id')->nullable();
        });

        Schema::table('erp_mrn_extra_amounts', function (Blueprint $table) {
            $table->unsignedBigInteger('po_id') -> after('mrn_detail_id')->nullable();
            $table->unsignedBigInteger('jo_id') -> after('po_id')->nullable();
        });

        Schema::table('erp_mrn_extra_amount_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('po_id') -> after('mrn_detail_id')->nullable();
            $table->unsignedBigInteger('jo_id') -> after('po_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_extra_amount_histories', function (Blueprint $table) {
            $table->dropColumn('po_id');
            $table->dropColumn('jo_id');
        });

        Schema::table('erp_mrn_extra_amounts', function (Blueprint $table) {
            $table->dropColumn('po_id');
            $table->dropColumn('jo_id');
        });

        Schema::table('erp_gate_entry_ted_history', function (Blueprint $table) {
            $table->dropColumn('po_id');
            $table->dropColumn('jo_id');
        });

        Schema::table('erp_gate_entry_ted', function (Blueprint $table) {
            $table->dropColumn('po_id');
            $table->dropColumn('jo_id');
        });
    }
};
