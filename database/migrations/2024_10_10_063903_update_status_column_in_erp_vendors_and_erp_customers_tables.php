<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE erp_vendors CHANGE status status ENUM('" . implode("','", ConstantHelper::USER_STATUS) . "') NOT NULL DEFAULT '" . ConstantHelper::DRAFT . "'");
        DB::statement("ALTER TABLE erp_customers CHANGE status status ENUM('" . implode("','", ConstantHelper::USER_STATUS) . "') NOT NULL DEFAULT '" . ConstantHelper::DRAFT . "'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE erp_vendors CHANGE status status ENUM('" . ConstantHelper::ACTIVE . "', '" . ConstantHelper::INACTIVE . "') NOT NULL DEFAULT '" . ConstantHelper::ACTIVE . "'");
        DB::statement("ALTER TABLE erp_customers CHANGE status status ENUM('" . ConstantHelper::ACTIVE . "', '" . ConstantHelper::INACTIVE . "') NOT NULL DEFAULT '" . ConstantHelper::ACTIVE . "'");
    }
};
