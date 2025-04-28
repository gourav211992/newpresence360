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
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('reference_number');
            $table->string('store_code')->nullable()->after('store_id');
            $table->unsignedBigInteger('department_id')->nullable()->after('store_code');
            $table->string('department_code')->nullable()->after('department_id');
        });
        Schema::table('erp_sale_orders_history', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('reference_number');
            $table->string('store_code')->nullable()->after('store_id');
            $table->unsignedBigInteger('department_id')->nullable()->after('store_code');
            $table->string('department_code')->nullable()->after('department_id');
        });
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('store_code');
            $table->string('department_code')->nullable()->after('department_id');
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('store_code');
            $table->string('department_code')->nullable()->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->dropColumn(['store_id', 'store_code', 'department_id', 'department_code']);
        });
        
        Schema::table('erp_sale_orders_history', function (Blueprint $table) {
            $table->dropColumn(['store_id', 'store_code', 'department_id', 'department_code']);
        });
        
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'department_code']);
        });
        
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'department_code']);
        });
    }

};
