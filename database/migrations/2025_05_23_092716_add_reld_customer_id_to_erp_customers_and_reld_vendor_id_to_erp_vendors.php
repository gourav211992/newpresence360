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
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('reld_customer_id')->nullable()->after('related_party');
        });
    
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('reld_vendor_id')->nullable()->after('related_party');
        });
    }
    
    public function down()
    {
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropColumn('reld_customer_id');
        });
    
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropColumn('reld_vendor_id');
        });
    }
};
