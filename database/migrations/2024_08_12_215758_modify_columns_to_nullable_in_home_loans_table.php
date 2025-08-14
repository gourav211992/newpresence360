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
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->string('scheme_for')->nullable()->change();
            $table->string('gir_no')->nullable()->change();
            $table->string('earning_member')->nullable()->change();
            $table->string('no_of_depends')->nullable()->change();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_home_loans', function (Blueprint $table) {
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `scheme_for` `scheme_for` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;');
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `gir_no` `gir_no` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;');
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `earning_member` `earning_member` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;');
            DB::statement('ALTER TABLE `erp_home_loans` CHANGE `no_of_depends` `no_of_depends` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;');

        });
    }
};
