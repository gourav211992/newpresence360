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
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_so_items_history', 'mi_qty')) {
                $table->decimal('mi_qty', 20, 6)->after('invoice_qty')->default(0.00);
            }
            if (!Schema::hasColumn('erp_so_items_history', 'pwo_qty')) {
                $table->decimal('pwo_qty', 20, 6)->after('mi_qty')->default(0.00);
            }
            if (!Schema::hasColumn('erp_so_items_history', 'pslip_qty')) {
                $table->decimal('pslip_qty', 20, 6)->after('pwo_qty')->default(0.00);
            }
            if (!Schema::hasColumn('erp_so_items_history', 'plist_qty')) {
                $table->decimal('plist_qty', 20, 6)->after('pslip_qty')->default(0.00);
            }
            if (!Schema::hasColumn('erp_so_items_history', 'delivery_date')) {
                $table->date('delivery_date')->after('rate')->default(null);
            }
            if (!Schema::hasColumn('erp_so_items_history', 'header_discounts')) {
                $table->json('header_discounts')->after('header_discount_amount')->default(null);
            }
            if (!Schema::hasColumn('erp_so_items_history', 'plist_item_id')) {
                $table->unsignedBigInteger('plist_item_id')->after('plist_qty')->default(null);
            }
            if (Schema::hasColumn('erp_so_items_history', 'work_order_qty')) {
                $table->dropColumn(['work_order_qty']);
            }
        });
        Schema::table('erp_so_items', function (Blueprint $table) {
            if (Schema::hasColumn('erp_so_items', 'work_order_qty')) {
                $table->dropColumn(['work_order_qty']);
            }
        });
        if (!Schema::hasTable('erp_so_item_bom_history')) {
            Schema::create('erp_so_item_bom_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('source_id');
                $table->unsignedBigInteger('sale_order_id');
                $table->unsignedBigInteger('so_item_id');
                $table->unsignedBigInteger('bom_id');
                $table->unsignedBigInteger('bom_detail_id');
                $table->unsignedBigInteger('uom_id');
                $table->unsignedBigInteger('item_id');
                $table->string('item_code');
                $table->json('item_attributes')->nullable();
                $table->double('qty', 20, 6);
                $table->unsignedBigInteger('station_id');
                $table->string('station_name');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            if (Schema::hasColumn('erp_so_items_history', 'mi_qty')) {
                $table->dropColumn(['mi_qty']);
            }
            if (Schema::hasColumn('erp_so_items_history', 'pwo_qty')) {
                $table->dropColumn(['pwo_qty']);
            }
            if (Schema::hasColumn('erp_so_items_history', 'pslip_qty')) {
                $table->dropColumn(['pslip_qty']);
            }
            if (Schema::hasColumn('erp_so_items_history', 'plist_qty')) {
                $table->dropColumn(['plist_qty']);
            }
            if (Schema::hasColumn('erp_so_items_history', 'delivery_date')) {
                $table->dropColumn(['delivery_date']);
            }
            if (Schema::hasColumn('erp_so_items_history', 'header_discounts')) {
                $table->dropColumn(['header_discounts']);
            }
            if (Schema::hasColumn('erp_so_items_history', 'plist_item_id')) {
                $table->dropColumn(['plist_item_id']);
            }
            if (!Schema::hasColumn('erp_so_items_history', 'work_order_qty')) {
                $table->decimal('work_order_qty', 20, 6) -> default(0);
            }
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_so_items', 'work_order_qty')) {
                $table->decimal('work_order_qty', 20, 6) -> default(0);
            }
        });
        Schema::dropIfExists('erp_so_item_bom_history');
    }
};
