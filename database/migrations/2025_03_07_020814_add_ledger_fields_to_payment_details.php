<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('erp_payment_voucher_details', function (Blueprint $table) {
            $table->unsignedBigInteger('ledger_id')->nullable()->after('payment_voucher_id');
            $table->unsignedBigInteger('ledger_group_id')->nullable()->after('ledger_id');
            $table->unsignedBigInteger('party_id')->nullable()->default(null)->change();
            $table->string('party_type')->nullable()->default(null)->change();
        });

        Schema::table('erp_payment_voucher_details_history', function (Blueprint $table) {
            $table->unsignedBigInteger('ledger_id')->nullable()->after('payment_voucher_id');
            $table->unsignedBigInteger('ledger_group_id')->nullable()->after('ledger_id');
            $table->unsignedBigInteger('party_id')->nullable()->default(null)->change();
            $table->string('party_type')->nullable()->default(null)->change();
        });
    }

    public function down() {
        Schema::table('erp_payment_voucher_details', function (Blueprint $table) {
            $table->dropColumn(['ledger_id', 'ledger_group_id']);
            $table->unsignedBigInteger('party_id')->nullable(false)->change();
            $table->string('party_type')->nullable(false)->change();
        });

        Schema::table('erp_payment_voucher_details_history', function (Blueprint $table) {
            $table->dropColumn(['ledger_id', 'ledger_group_id']);
            $table->unsignedBigInteger('party_id')->nullable(false)->change();
            $table->string('party_type')->nullable(false)->change();
        });
    }
};
