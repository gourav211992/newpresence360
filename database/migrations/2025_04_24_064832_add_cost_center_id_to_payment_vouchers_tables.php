<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('currency_id');
        });

        Schema::table('erp_payment_vouchers_history', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('currency_id');
        });
    }

    public function down(): void
    {
        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('cost_center_id');
        });

        Schema::table('erp_payment_vouchers_history', function (Blueprint $table) {
            $table->dropColumn('cost_center_id');
        });
    }
};
