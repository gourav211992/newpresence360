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
        // item type [bom, alt]
        Schema::table('erp_pslip_bom_consumptions', function (Blueprint $table) {
            $table->string('item_type')->default('bom')->comment('bom , alt')->after('attributes');
            $table->unsignedBigInteger('base_item_id')->nullable()->after('item_type');
            $table->double('required_qty',20,6)->default(0)->after('qty');
        });

        \DB::statement('
            UPDATE erp_pslip_bom_consumptions
            SET required_qty = consumption_qty
            WHERE required_qty = 0
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pslip_bom_consumptions', function (Blueprint $table) {
            $table->dropColumn('required_qty');
            $table->dropColumn('base_item_id');
            $table->dropColumn('item_type');
        });
    }
};
