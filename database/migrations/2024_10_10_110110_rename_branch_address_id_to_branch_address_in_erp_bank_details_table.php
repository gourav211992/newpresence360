<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('erp_bank_details', function (Blueprint $table) {
            // Drop foreign key constraint first
            if (Schema::hasColumn('erp_bank_details', 'branch_address_id')) {
                $table->dropForeign(['branch_address_id']);
                $table->dropColumn('branch_address_id');
            }
            // Add the new column branch_address
            if (!Schema::hasColumn('erp_bank_details', 'branch_address')) {
                $table->string('branch_address')->after('branch_name')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('erp_bank_details', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_bank_details', 'branch_address_id')) {
                $table->unsignedBigInteger('branch_address_id')->nullable()->after('branch_name');
            }
        });
    }
};