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
        // if (!Schema::hasTable('erp_loan_documents')) {
        Schema::table('erp_loan_documents', function (Blueprint $table) {
            DB::statement('ALTER TABLE `erp_loan_documents` CHANGE `adhar_card` `adhar_card` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_documents` CHANGE `gir_no` `gir_no` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_documents` CHANGE `plot_doc` `plot_doc` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_documents` CHANGE `land_doc` `land_doc` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_documents` CHANGE `income_proof` `income_proof` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
        });
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_loan_documents', function (Blueprint $table) {
            DB::statement('ALTER TABLE `erp_loan_documents` CHANGE `adhar_card` `adhar_card` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_documents` CHANGE `gir_no` `gir_no` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_documents` CHANGE `plot_doc` `plot_doc` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_documents` CHANGE `land_doc` `land_doc` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_documents` CHANGE `income_proof` `income_proof` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
        });
    }
};
