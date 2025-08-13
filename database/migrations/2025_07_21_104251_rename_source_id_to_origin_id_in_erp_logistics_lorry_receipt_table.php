<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE `erp_logistics_lorry_receipt` CHANGE `source_id` `origin_id` BIGINT(20) UNSIGNED NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `erp_logistics_lorry_receipt` CHANGE `origin_id` `source_id` BIGINT(20) UNSIGNED NULL");
    }
};
