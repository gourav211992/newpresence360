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
        Schema::rename('erp_pl_items', 'erp_pl_item_details');
        Schema::rename('erp_pl_items_history', 'erp_pl_item_details_history');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename( 'erp_pl_item_details', 'erp_pl_items');
        Schema::rename( 'erp_pl_item_details_history', 'erp_pl_items_history');
    }
};
