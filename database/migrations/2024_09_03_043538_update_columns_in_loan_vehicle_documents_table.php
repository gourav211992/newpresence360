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
        // if (!Schema::hasTable('erp_loan_vehicle_documents')) {
        Schema::table('erp_loan_vehicle_documents', function (Blueprint $table) {
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `adhar_card` `adhar_card` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `pan_gir_no` `pan_gir_no` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `vehicle_doc` `vehicle_doc` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `security_doc` `security_doc` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `partnership_doc` `partnership_doc` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `affidavit_doc` `affidavit_doc` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `scan_doc` `scan_doc` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
        });
    // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_loan_vehicle_documents', function (Blueprint $table) {
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `adhar_card` `adhar_card` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `pan_gir_no` `pan_gir_no` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `vehicle_doc` `vehicle_doc` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `security_doc` `security_doc` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `partnership_doc` `partnership_doc` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `affidavit_doc` `affidavit_doc` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
            DB::statement('ALTER TABLE `erp_loan_vehicle_documents` CHANGE `scan_doc` `scan_doc` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;');
        });
    }
};
