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
        Schema::table('erp_payment_voucher_details', function (Blueprint $table) {
            $table->renameColumn('user_id','party_id');
            $table->renameColumn('user_type','party_type');
            $table->string('type')->after('user_type');
            $table->string('partyCode')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_payment_voucher_details', function (Blueprint $table) {
            $table->renameColumn('party_id','user_id');
            $table->renameColumn('party_type','user_type');
            $table->dropColumn('type');
            $table->dropColumn('partyCode');
        });
    }
};
