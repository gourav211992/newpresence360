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
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->unsignedBigInteger('land_lease_id')->nullable()->after('so_item_id');
            $table->unsignedBigInteger('lease_schedule_id')->nullable()->after('land_lease_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->dropColumn(['land_lease_id', 'lease_schedule_id']);
        });
    }
};
