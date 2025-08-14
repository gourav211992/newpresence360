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
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            //add delivery_status column
            $table->tinyInteger('delivery_status')->default(0)->after('document_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_invoices', function (Blueprint $table) {
            //remove delivery_status column
            $table->dropColumn('delivery_status');
        });
    }
};
