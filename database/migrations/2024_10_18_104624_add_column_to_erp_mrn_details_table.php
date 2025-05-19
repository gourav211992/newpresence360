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
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->decimal('purchase_bill_qty', 10,2)->nullable()->after('accepted_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            $table->dropColumn('purchase_bill_qty');
        });
    }
};
