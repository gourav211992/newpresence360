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
        Schema::table('erp_jo_products', function (Blueprint $table) {
            $table->double('inter_org_so_qty', 20, 6) -> default(0.00) -> after('order_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_jo_products', function (Blueprint $table) {
            $table->dropColumn(['inter_org_so_qty']);
        });
    }
};
