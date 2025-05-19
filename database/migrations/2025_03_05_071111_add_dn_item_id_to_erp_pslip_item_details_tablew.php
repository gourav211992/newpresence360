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
        Schema::table('erp_pslip_item_details', function (Blueprint $table) {
            $table->unsignedBigInteger('dn_item_id') -> after('pslip_item_id') -> nullable(); 
        });
        Schema::table('erp_pslip_item_details_history', function (Blueprint $table) {
            $table->unsignedBigInteger('dn_item_id') -> after('pslip_item_id') -> nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pslip_item_details', function (Blueprint $table) {
            $table->dropColumn(['dn_item_id']); 
        });
        Schema::table('erp_pslip_item_details_history', function (Blueprint $table) {
            $table->dropColumn(['dn_item_id']); 
        });
    }
};
