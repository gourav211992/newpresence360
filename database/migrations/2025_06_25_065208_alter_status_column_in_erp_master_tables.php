<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("ALTER TABLE erp_items MODIFY status VARCHAR(255) NULL");
        DB::statement("ALTER TABLE erp_customers MODIFY status VARCHAR(255) NULL");
        DB::statement("ALTER TABLE erp_vendors MODIFY status VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement("ALTER TABLE erp_items MODIFY status ENUM('active', 'inactive', 'draft') NOT NULL");
        DB::statement("ALTER TABLE erp_customers MODIFY status ENUM('active', 'inactive', 'draft') NOT NULL");
        DB::statement("ALTER TABLE erp_vendors MODIFY status ENUM('active', 'inactive', 'draft') NOT NULL");
    }
};
