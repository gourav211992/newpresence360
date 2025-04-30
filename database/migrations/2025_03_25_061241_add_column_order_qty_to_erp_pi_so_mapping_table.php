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
        Schema::table('erp_pi_so_mapping', function (Blueprint $table) {
            $table->double('order_qty', 20, 6)->default(0)->after('item_code');
            $table->double('bom_qty', 20, 6)->default(0)->after('order_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pi_so_mapping', function (Blueprint $table) {
           $table->dropColumn('order_qty'); 
           $table->dropColumn('bom_qty'); 
        });
    }
};
