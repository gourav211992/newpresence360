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
        Schema::table('erp_logistics_lorry_receipt', function (Blueprint $table) {
        $table->string('consignee_status')->default('pending')->after('document_status'); 
      });

        Schema::table('erp_logistics_lorry_receipt_history', function (Blueprint $table) {
            $table->string('consignee_status')->default('pending')->after('document_status'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::table('erp_logistics_lorry_receipt', function (Blueprint $table) {
           $table->dropColumn('consignee_status');
        });

        Schema::table('erp_logistics_lorry_receipt_history', function (Blueprint $table) {
            $table->dropColumn('consignee_status');
        });
    }
};
