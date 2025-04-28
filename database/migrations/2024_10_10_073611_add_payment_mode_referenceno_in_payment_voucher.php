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
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->after('amount');
            $table->string('payment_mode')->nullable()->after('bankCode');
            $table->string('reference_no')->nullable()->after('payment_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('payment_date');
            $table->dropColumn('payment_mode');
            $table->dropColumn('reference_no');
        });
    }
};
