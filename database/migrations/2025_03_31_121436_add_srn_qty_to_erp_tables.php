<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration 
{
    public function up()
    {
        // Add srn_qty to erp_invoice_items
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_invoice_items', 'srn_qty')) {
                $table->decimal('srn_qty', 15, 2)->after('invoice_qty')->default(0.00);
            }
        });
        Schema::table('erp_invoice_items_history', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_invoice_items_history', 'srn_qty')) {
                $table->decimal('srn_qty', 15, 2)->after('invoice_qty')->default(0.00);
            }
        });

        Schema::table('erp_so_items', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_so_items', 'srn_qty')) {
                $table->decimal('srn_qty', 15, 2)->after('dnote_qty')->default(0.00);
            }
        });

        Schema::table('erp_so_items_history', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_so_items_history', 'srn_qty')) {
                $table->decimal('srn_qty', 15, 2)->after('dnote_qty')->default(0.00);
            }
        });

        Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_sale_return_items_histories', 'order_qty')) {
                $table->double('order_qty', 15, 2);
            }
            if (!Schema::hasColumn('erp_sale_return_items_histories', 'store_code')) {
                $table->string('store_code')->nullable();
            }
            if (Schema::hasColumn('erp_sale_return_items_histories', 'si_item_id')) {
                $table->unsignedBigInteger('si_item_id')->nullable()->change();
            }
        });

        Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_sale_return_items_histories', 'return_amount')) {
                $table->date('return_amount')->nullable()->after('order_qty');
            }
            if (Schema::hasColumn('erp_sale_return_items_histories', 'sr_item_id')) {
                //$table->dropColumn('sr_item_id');
            }
        });


        Schema::table('erp_sale_return_item_attribute_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_sale_return_item_attribute_histories', 'return_item_id')) {
                DB::statement('ALTER TABLE erp_sale_return_item_attribute_histories CHANGE COLUMN return_item_id sale_return_item_id BIGINT UNSIGNED');
            }
        });

        Schema::table('erp_sale_return_item_location_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_sale_return_item_location_histories', 'sr_item_id')) {
                $table->renameColumn('sr_item_id', 'sale_return_item_id');
            }
            if (!Schema::hasColumn('erp_sale_return_item_location_histories', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
            if (!Schema::hasColumn('erp_sale_return_item_location_histories', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
            }
            if (!Schema::hasColumn('erp_sale_return_item_location_histories', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable();
            }
        });

       
        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_sale_return_histories', 'revision_number')) {
                $table->unsignedBigInteger('revision_number')->nullable();
            }
            if (!Schema::hasColumn('erp_sale_return_histories', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable();
            }
            if (!Schema::hasColumn('erp_sale_return_histories', 'department_code')) {
                $table->string('department_code')->nullable();
            }
            if (!Schema::hasColumn('erp_sale_return_histories', 'revision_date')) {
                $table->date('revision_date')->nullable();
            }
            if (!Schema::hasColumn('erp_sale_return_histories', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('erp_invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('erp_invoice_items', 'srn_qty')) {
                $table->dropColumn('srn_qty');
            }
        });

        Schema::table('erp_so_items', function (Blueprint $table) {
            if (Schema::hasColumn('erp_so_items', 'srn_qty')) {
                $table->dropColumn('srn_qty');
            }
        });

        Schema::table('erp_sale_return_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_sale_return_histories', 'revision_number')) {
                $table->dropColumn('revision_number');
            }
            if (Schema::hasColumn('erp_sale_return_histories', 'revision_date')) {
                $table->dropColumn('revision_date');
            }
        });

        Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_sale_return_items_histories', 'order_qty')) {
                $table->dropColumn('order_qty');
            }
            if (Schema::hasColumn('erp_sale_return_items_histories', 'return_amount')) {
                $table->dropColumn('return_amount');
            }
        });
    }
};