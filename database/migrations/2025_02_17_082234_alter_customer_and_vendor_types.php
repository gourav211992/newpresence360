<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantHelper;

class AlterCustomerAndVendorTypes extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE erp_customers MODIFY customer_type VARCHAR(255) NULL");
        DB::statement("ALTER TABLE erp_vendors MODIFY vendor_type VARCHAR(255) NULL");
    }

    public function down()
    {

        DB::statement("ALTER TABLE erp_customers MODIFY customer_type ENUM('" . implode("', '", ConstantHelper::CUSTOMER_TYPES) . "') NULL");
        DB::statement("ALTER TABLE erp_vendors MODIFY vendor_type ENUM('" . implode("', '", ConstantHelper::VENDOR_TYPES) . "') NULL");
    }
}