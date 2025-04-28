<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('ledger_group_id')->nullable()->after('payment_terms_id');
            $table->foreign('ledger_group_id')->references('id')->on('erp_groups')->onDelete('set null')  ->onUpdate('cascade');  
        });
    }

    public function down(): void
    {
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropForeign(['ledger_group_id']);
            $table->dropColumn('ledger_group_id');
        });
    }
};
