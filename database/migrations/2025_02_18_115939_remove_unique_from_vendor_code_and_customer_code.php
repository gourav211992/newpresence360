<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropUnique(['vendor_code']); 
        });

        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropUnique(['customer_code']); 
        });
    }

    public function down()
    {
        // No need to re-add unique constraints here
        
    }
};
