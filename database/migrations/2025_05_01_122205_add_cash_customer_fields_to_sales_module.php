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
            $table->string('customer_email')->after('customer_code')->nullable();
            $table->string('customer_phone_no', 20)->after('customer_email')->nullable();
            $table->string('customer_gstin', 20)->after('customer_phone_no')->nullable();
        });
        Schema::table('erp_sale_orders_history', function (Blueprint $table) {
            $table->string('customer_email')->after('customer_code')->nullable();
            $table->string('customer_phone_no', 20)->after('customer_email')->nullable();
            $table->string('customer_gstin', 20)->after('customer_phone_no')->nullable();
        });
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->string('customer_email')->after('customer_code')->nullable();
            $table->string('customer_phone_no', 20)->after('customer_email')->nullable();
            $table->string('customer_gstin', 20)->after('customer_phone_no')->nullable();
        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->string('customer_email')->after('customer_code')->nullable();
            $table->string('customer_phone_no', 20)->after('customer_email')->nullable();
            $table->string('customer_gstin', 20)->after('customer_phone_no')->nullable();
        });
        Schema::table('erp_sale_returns', function (Blueprint $table) {
            $table->string('customer_email')->after('customer_code')->nullable();
            $table->string('customer_phone_no', 20)->after('customer_email')->nullable();
            $table->string('customer_gstin', 20)->after('customer_phone_no')->nullable();
        });
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->string('customer_email')->after('customer_code')->nullable();
            $table->string('customer_phone_no', 20)->after('customer_email')->nullable();
            $table->string('customer_gstin', 20)->after('customer_phone_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'customer_phone_no'])->after('customer_code')->nullable();
        });
        Schema::table('erp_sale_orders_history', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'customer_phone_no'])->after('customer_code')->nullable();
        });
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'customer_phone_no'])->after('customer_code')->nullable();

        });
        Schema::table('erp_sale_invoices_history', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'customer_phone_no'])->after('customer_code')->nullable();

        });
        Schema::table('erp_sale_returns', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'customer_phone_no'])->after('customer_code')->nullable();

        });
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'customer_phone_no'])->after('customer_code')->nullable();

        });
    }
};
