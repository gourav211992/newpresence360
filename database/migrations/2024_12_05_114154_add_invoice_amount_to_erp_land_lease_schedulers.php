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
        Schema::table('erp_land_lease_schedulers', function (Blueprint $table) {
            $table->double('invoice_amount', 15, 2) -> default(0) -> after('installment_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_land_lease_schedulers', function (Blueprint $table) {
            $table->dropColumn(['invoice_amount']);
        });
    }
};
