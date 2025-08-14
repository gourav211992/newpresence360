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
        Schema::table('erp_voucher_references', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_voucher_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_voucher_references', function (Blueprint $table) {
            $table->dropColumn('payment_voucher_id');
        });
    }
};
