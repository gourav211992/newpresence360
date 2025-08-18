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
        Schema::table('erp_ti_invoice_items', function (Blueprint $table) {
            // Add the column after id
            $table->unsignedBigInteger('ti_invoice_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_ti_invoice_items', function (Blueprint $table) {
            $table->dropColumn('ti_invoice_id');
        });
    }
};
