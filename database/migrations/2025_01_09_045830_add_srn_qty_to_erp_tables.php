<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSrnQtyToErpTables extends Migration
{
    public function up()
    {
        // Add srn_qty to erp_invoice_items
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->decimal('srn_qty', 15, 2)->after('invoice_qty')->default(0.00);
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->decimal('srn_qty', 15, 2)->after('invoice_qty')->default(0.00);
        });

        Schema::table('erp_sale_return_items', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('sr_item_id');
            $table->unsignedBigInteger('sr_item_id')->default(null)->after('si_item_id');
        });
        Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('sr_item_id')->default(null)->after('si_item_id');
        });

        // // Add srn_qty to erp_so_items
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->decimal('srn_qty', 15, 2)->after('dnote_qty')->default(0.00);
        });
        // Add srn_qty to erp_so_items
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->decimal('srn_qty', 15, 2)->after('dnote_qty')->default(0.00);
        });

        Schema::table('erp_sale_return_teds', function (Blueprint $table) {
            $table->double('ted_percentage', 15, 8)->change()->nullable();
        });
        Schema::table('erp_sale_return_ted_histories', function (Blueprint $table) {
            $table->decimal('ted_percentage', 15, 8)->change()->nullable();
        });
        Schema::table('erp_sale_returns', function (Blueprint $table) {
            $table->date('revision_date')->nullable()->after('revision_number');
            $table->unsignedBigInteger('store_id')->nullable()->after('group_id');
        });
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('revision_number')->nullable()->after('document_status');
            $table->unsignedBigInteger('department_id')->nullable()->after('store_code');
            $table->string('department_code')->nullable()->after('department_id');

        });
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->date('revision_date')->nullable()->after('revision_number');
            $table->unsignedBigInteger('store_id')->nullable()->after('group_id');


        });
        Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
            $table->double('order_qty', 15, 2)->after('inventory_uom_code');
            $table->unsignedBigInteger('store_id')->nullable()->after('sr_item_id');
            $table->string('store_code')->nullable()->after('store_id');
            $table->unsignedBigInteger('si_item_id')->change()->nullable();

        });
        Schema::table('erp_sale_return_item_attribute_histories', function (Blueprint $table) {
            $table->renameColumn('return_item_id', 'sale_return_item_id');
            $table->dropForeign('erp_sale_return_item_attribute_histories_id_foreign');
        });
        Schema::table('erp_sale_return_item_ted_histories', function (Blueprint $table) {
            $table->renameColumn('return_item_id', 'sale_return_item_id');
        });
        Schema::table('erp_sale_return_item_location_histories', function (Blueprint $table) {
            $table->dropForeign(['sale_return_id']);
            $table->foreign('sale_return_id')->references('id')->on('erp_sale_return_histories')->onDelete('cascade'); 
            $table->foreign('sale_return_item_id',"item_reference_history")->references('id')->on('erp_sale_return_items_histories')->onDelete('cascade'); $table->renameColumn('sr_item_id', 'sale_return_item_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
            $table->date('return_amount')->nullable()->after('order_qty');
            $table->dropForeign('erp_sale_return_items_histories_sr_item_id_foreign');
            $table->dropColumn('sr_item_id');
            $table->dropForeign('erp_sale_return_items_histories_sale_return_id_foreign');
        });
        Schema::table('erp_sale_return_item_attribute_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
        Schema::table('erp_sale_return_location_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
        Schema::table('erp_sale_return_ted_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });

        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->double('work_order_qty', 15, 2)->after('order_qty')->default(0.00);
        });
        Schema::table('erp_pwo_items', function (Blueprint $table) {
            $table->unsignedBigInteger('so_item_id',)->after('item_id')->nullable();
        });
    }

    public function down()
    {
        // Remove srn_qty from erp_invoice_items
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            $table->dropColumn('srn_qty');
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            $table->dropColumn('srn_qty');
        });
        Schema::table('erp_so_items_history', function (Blueprint $table) {
            $table->dropColumn('srn_qty');
        });
        Schema::table('erp_sale_returns', function (Blueprint $table) {
            $table->dropColumn('revision_date');
        });
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->dropColumn('revision_number');
        });
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            $table->dropColumn('revision_date');
        });

        Schema::table('erp_sale_return_items', function (Blueprint $table) {
            $table->dropColumn(['sr_item_id']);
        });
        Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
            $table->dropColumn(['order_qty', 'return_amount']);
            $table->unsignedBigInteger('sr_item_id')->nullable();
        });

        // Remove srn_qty from erp_so_items
        Schema::table('erp_so_items', function (Blueprint $table) {
            $table->dropColumn('srn_qty');
        });
        Schema::table('erp_sale_return_teds', function (Blueprint $table) {
            $table->dropColumn('ted_percentage');
        });
        Schema::table('erp_sale_return_ted_histories', function (Blueprint $table) {
            $table->dropColumn('ted_percentage');
        });
        Schema::table('erp_sale_return_item_attribute_histories', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('deleted_by');
        });
        Schema::table('erp_sale_return_location_histories', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('deleted_by');
        });
        Schema::table('erp_sale_return_ted_histories', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('deleted_by');
        });
        Schema::table('erp_sale_orders', function (Blueprint $table) {
            $table->dropColumn('work_order_qty');
        });
        Schema::table('erp_pwo_items', function (Blueprint $table) {
            $table->dropColumn('so_item_id');
        });
    }
}
