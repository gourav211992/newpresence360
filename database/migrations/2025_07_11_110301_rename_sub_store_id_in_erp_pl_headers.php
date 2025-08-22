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
        DB::statement("ALTER TABLE erp_pl_headers CHANGE sub_store_id main_sub_store_id BIGINT UNSIGNED");
        DB::statement("ALTER TABLE erp_pl_headers CHANGE sub_store_code main_sub_store_code VARCHAR(255)");

        DB::statement("ALTER TABLE erp_pl_headers_history CHANGE sub_store_id main_sub_store_id BIGINT UNSIGNED");
        DB::statement("ALTER TABLE erp_pl_headers_history CHANGE sub_store_code main_sub_store_code VARCHAR(255)");
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE erp_pl_headers CHANGE main_sub_store_id sub_store_id BIGINT UNSIGNED");
        DB::statement("ALTER TABLE erp_pl_headers CHANGE main_sub_store_code sub_store_code VARCHAR(255)");

        DB::statement("ALTER TABLE erp_pl_headers_history CHANGE main_sub_store_id sub_store_id BIGINT UNSIGNED");
        DB::statement("ALTER TABLE erp_pl_headers_history CHANGE main_sub_store_code sub_store_code VARCHAR(255)");
    }
};
