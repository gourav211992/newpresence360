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
        // if (!Schema::hasTable('erp_home_loans')) {
        Schema::table('erp_home_loans', function (Blueprint $table) {
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `series` `series` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;');
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `appli_no` `appli_no` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;');
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `ref_no` `ref_no` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;');
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `loan_amount` `loan_amount` VARCHAR(255) NULL;');
        });
    //   }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_home_loans', function (Blueprint $table) {
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `series` `series` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `appli_no` `appli_no` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `ref_no` `ref_no` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `loan_amount` `loan_amount` INT(11) NOT NULL;');
        });
    }
};
