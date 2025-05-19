<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE `erp_land_parcels_history` CHANGE COLUMN `approvalStatus` `document_status` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `erp_land_parcels_history` CHANGE COLUMN `approvalLevel` `approval_level` INT(11) NULL");
        DB::statement("ALTER TABLE `erp_land_parcels_history` CHANGE COLUMN `landable_id` `created_by` INT(11) NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `erp_land_parcels_history` CHANGE COLUMN `document_status` `approvalStatus` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `erp_land_parcels_history` CHANGE COLUMN `approval_level` `approvalLevel` INT(11) NULL");
        DB::statement("ALTER TABLE `erp_land_parcels_history` CHANGE COLUMN `created_by` `landable_id` INT(11) NULL");
    }

};
