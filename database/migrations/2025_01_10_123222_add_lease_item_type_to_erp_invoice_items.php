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
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->string('lease_item_type')->nullable()->default(NULL)->after('land_lease_id');
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->string('lease_item_type')->nullable()->default(NULL)->after('land_lease_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['lease_item_type']);
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->dropColumn(['lease_item_type']);
        });
    }
};
