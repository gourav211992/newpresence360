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
        Schema::table('erp_vendors_history', function (Blueprint $table) {
            $table->integer('credit_days_editable')->default(0)->after('credit_days');
        });

        Schema::table('erp_customers_history', function (Blueprint $table) {
            $table->integer('credit_days_editable')->default(0)->after('credit_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_vendors_history', function (Blueprint $table) {
            $table->dropColumn(['credit_days_editable']);
        });

        Schema::table('erp_customers_history', function (Blueprint $table) {
            $table->dropColumn(['credit_days_editable']);
        });
    }
};
