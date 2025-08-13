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
        Schema::table('erp_rate_contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('customer_code', 40)->nullable();

            $table->unsignedBigInteger('vendor_id')->nullable()->change();
            $table->string('vendor_code', 40)->nullable()->change();
        });

        Schema::table('erp_rate_contract_history', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('customer_code', 40)->nullable();

            $table->unsignedBigInteger('vendor_id')->nullable()->change();
            $table->string('vendor_code', 40)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_rate_contracts', function (Blueprint $table) {
            if (Schema::hasColumn('erp_rate_contracts', 'customer_id')) {
            $table->dropColumn('customer_id');
            }
            if (Schema::hasColumn('erp_rate_contracts', 'customer_code')) {
            $table->dropColumn('customer_code');
            }
        });

        Schema::table('erp_rate_contract_history', function (Blueprint $table) {
            if (Schema::hasColumn('erp_rate_contracts_history', 'customer_id')) {
            $table->dropColumn('customer_id');
            }
            if (Schema::hasColumn('erp_rate_contract_history', 'customer_code')) {
            $table->dropColumn('customer_code');
            }
        });
    }
};
