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
            $table->boolean('create_ledger')->default(0)->after('payment_terms_id');
        });

        Schema::table('erp_customers', function (Blueprint $table) {
            $table->boolean('create_ledger')->default(0)->after('payment_terms_id');
        });

        Schema::table('erp_vendors_history', function (Blueprint $table) {
            $table->boolean('create_ledger')->default(0)->after('payment_terms_id');
        });

        Schema::table('erp_customers_history', function (Blueprint $table) {
            $table->boolean('create_ledger')->default(0)->after('payment_terms_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropColumn('create_ledger');
        });

        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropColumn('create_ledger');
        });

        Schema::table('erp_vendors_history', function (Blueprint $table) {
            $table->dropColumn('create_ledger');
        });

        Schema::table('erp_customers_history', function (Blueprint $table) {
            $table->dropColumn('create_ledger');
        });
    }
};
