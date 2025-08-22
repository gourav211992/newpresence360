<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add srn_qty to erp_invoice_items
        if (Schema::hasTable('erp_invoice_items') && !Schema::hasColumn('erp_invoice_items', 'srn_qty')) {
            Schema::table('erp_invoice_items', function (Blueprint $table) {
                $table->decimal('srn_qty', 15, 2)->after('invoice_qty')->default(0.00);
            });
        }

        if (Schema::hasTable('erp_invoice_items_history') && !Schema::hasColumn('erp_invoice_items_history', 'srn_qty')) {
            Schema::table('erp_invoice_items_history', function (Blueprint $table) {
                $table->decimal('srn_qty', 15, 2)->after('invoice_qty')->default(0.00);
            });
        }

        if (Schema::hasTable('erp_sale_return_items')) {
            Schema::table('erp_sale_return_items', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_sale_return_items', 'sr_item_id')) {
                    $table->unsignedBigInteger('sr_item_id')->default(null)->after('si_item_id');
                }
                if (!Schema::hasColumn('erp_sale_return_items', 'store_id')) {
                    $table->unsignedBigInteger('store_id')->nullable()->after('sr_item_id');
                }
            });
        }

        if (Schema::hasTable('erp_sale_return_items_histories') && !Schema::hasColumn('erp_sale_return_items_histories', 'sr_item_id')) {
            Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('sr_item_id')->default(null)->after('si_item_id');
            });
        }

        if (Schema::hasTable('erp_so_items') && !Schema::hasColumn('erp_so_items', 'srn_qty')) {
            Schema::table('erp_so_items', function (Blueprint $table) {
                $table->decimal('srn_qty', 15, 2)->after('dnote_qty')->default(0.00);
            });
        }

        if (Schema::hasTable('erp_so_items_history') && !Schema::hasColumn('erp_so_items_history', 'srn_qty')) {
            Schema::table('erp_so_items_history', function (Blueprint $table) {
                $table->decimal('srn_qty', 15, 2)->after('dnote_qty')->default(0.00);
            });
        }

        if (Schema::hasTable('erp_sale_return_teds')) {
            Schema::table('erp_sale_return_teds', function (Blueprint $table) {
                $table->double('ted_percentage', 15, 8)->nullable()->change();
            });
        }

        if (Schema::hasTable('erp_sale_return_ted_histories')) {
            Schema::table('erp_sale_return_ted_histories', function (Blueprint $table) {
                $table->decimal('ted_percentage', 15, 8)->nullable()->change();
            });
        }

        if (Schema::hasTable('erp_sale_returns')) {
            Schema::table('erp_sale_returns', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_sale_returns', 'revision_date')) {
                    $table->date('revision_date')->nullable()->after('revision_number');
                }
                if (!Schema::hasColumn('erp_sale_returns', 'store_id')) {
                    $table->unsignedBigInteger('store_id')->nullable()->after('group_id');
                }
            });
        }

        if (Schema::hasTable('erp_sale_return_histories')) {
            Schema::table('erp_sale_return_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_sale_return_histories', 'revision_number')) {
                    $table->unsignedBigInteger('revision_number')->nullable()->after('document_status');
                }
                if (!Schema::hasColumn('erp_sale_return_histories', 'department_id')) {
                    $table->unsignedBigInteger('department_id')->nullable()->after('store_code');
                }
                if (!Schema::hasColumn('erp_sale_return_histories', 'department_code')) {
                    $table->string('department_code')->nullable()->after('department_id');
                }
                if (!Schema::hasColumn('erp_sale_return_histories', 'revision_date')) {
                    $table->date('revision_date')->nullable()->after('revision_number');
                }
                if (!Schema::hasColumn('erp_sale_return_histories', 'store_id')) {
                    $table->unsignedBigInteger('store_id')->nullable()->after('group_id');
                }
            });
        }

        if (Schema::hasTable('erp_sale_return_items_histories')) {
            Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_sale_return_items_histories', 'order_qty')) {
                    $table->double('order_qty', 15, 2)->after('inventory_uom_code');
                }
                if (!Schema::hasColumn('erp_sale_return_items_histories', 'store_id')) {
                    $table->unsignedBigInteger('store_id')->nullable()->after('sr_item_id');
                }
                if (!Schema::hasColumn('erp_sale_return_items_histories', 'store_code')) {
                    $table->string('store_code')->nullable()->after('store_id');
                }
                if (Schema::hasColumn('erp_sale_return_items_histories', 'si_item_id')) {
                    $table->unsignedBigInteger('si_item_id')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('erp_sale_return_item_attribute_histories')) {
            Schema::table('erp_sale_return_item_attribute_histories', function (Blueprint $table) {
                if (Schema::hasColumn('erp_sale_return_item_attribute_histories', 'return_item_id')) {
                    $table->renameColumn('return_item_id', 'sale_return_item_id');
                }
                if (Schema::hasColumn('erp_sale_return_item_attribute_histories', 'id')) {
                    // $table->dropForeign('erp_sale_return_item_attribute_histories_id_foreign');
                }
                if (!Schema::hasColumn('erp_sale_return_item_attribute_histories', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->unsignedBigInteger('updated_by')->nullable();
                    $table->unsignedBigInteger('deleted_by')->nullable();
                }
            });
        }

        if (Schema::hasTable('erp_sale_return_item_ted_histories')) {
            Schema::table('erp_sale_return_item_ted_histories', function (Blueprint $table) {
                if (Schema::hasColumn('erp_sale_return_item_ted_histories', 'return_item_id')) {
                    $table->renameColumn('return_item_id', 'sale_return_item_id');
                }
            });
        }

        if (Schema::hasTable('erp_sale_return_item_location_histories')) {
            Schema::table('erp_sale_return_item_location_histories', function (Blueprint $table) {
                if (Schema::hasColumn('erp_sale_return_item_location_histories', 'sale_return_id')) {
                    // $table->dropForeign(['sale_return_id']);
                }
                // $table->foreign('sale_return_id')->references('id')->on('erp_sale_return_histories')->onDelete('cascade');

                if (Schema::hasColumn('erp_sale_return_item_location_histories', 'sr_item_id')) {
                    $table->renameColumn('sr_item_id', 'sale_return_item_id');
                }

                // $table->foreign('sale_return_item_id', 'item_reference_history')->references('id')->on('erp_sale_return_items_histories')->onDelete('cascade');

                if (!Schema::hasColumn('erp_sale_return_item_location_histories', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->unsignedBigInteger('updated_by')->nullable();
                    $table->unsignedBigInteger('deleted_by')->nullable();
                }
            });
        }

        if (Schema::hasTable('erp_sale_return_items_histories')) {
            Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_sale_return_items_histories', 'return_amount')) {
                    $table->date('return_amount')->nullable()->after('order_qty');
                }

                if (Schema::hasColumn('erp_sale_return_items_histories', 'sr_item_id')) {
                    // $table->dropForeign('erp_sale_return_items_histories_sr_item_id_foreign');
                    // $table->dropColumn('sr_item_id');
                }

                if (Schema::hasColumn('erp_sale_return_items_histories', 'sale_return_id')) {
                    // $table->dropForeign('erp_sale_return_items_histories_sale_return_id_foreign');
                }
            });
        }

        if (Schema::hasTable('erp_sale_return_location_histories')) {
            Schema::table('erp_sale_return_location_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_sale_return_location_histories', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->unsignedBigInteger('updated_by')->nullable();
                    $table->unsignedBigInteger('deleted_by')->nullable();
                }
            });
        }

        if (Schema::hasTable('erp_sale_return_ted_histories')) {
            Schema::table('erp_sale_return_ted_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_sale_return_ted_histories', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable();
                    $table->unsignedBigInteger('updated_by')->nullable();
                    $table->unsignedBigInteger('deleted_by')->nullable();
                }
            });
        }

        if (Schema::hasTable('erp_so_items') && !Schema::hasColumn('erp_so_items', 'work_order_qty')) {
            Schema::table('erp_so_items', function (Blueprint $table) {
                $table->double('work_order_qty', 15, 2)->after('order_qty')->default(0.00);
            });
        }

        if (Schema::hasTable('erp_pwo_items') && !Schema::hasColumn('erp_pwo_items', 'so_item_id')) {
            Schema::table('erp_pwo_items', function (Blueprint $table) {
                $table->unsignedBigInteger('so_item_id')->after('item_id')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('erp_invoice_items') && Schema::hasColumn('erp_invoice_items', 'srn_qty')) {
            Schema::table('erp_invoice_items', function (Blueprint $table) {
                $table->dropColumn('srn_qty');
            });
        }

        if (Schema::hasTable('erp_invoice_items_history') && Schema::hasColumn('erp_invoice_items_history', 'srn_qty')) {
            Schema::table('erp_invoice_items_history', function (Blueprint $table) {
                $table->dropColumn('srn_qty');
            });
        }

        if (Schema::hasTable('erp_so_items_history') && Schema::hasColumn('erp_so_items_history', 'srn_qty')) {
            Schema::table('erp_so_items_history', function (Blueprint $table) {
                $table->dropColumn('srn_qty');
            });
        }

        if (Schema::hasTable('erp_sale_returns') && Schema::hasColumn('erp_sale_returns', 'revision_date')) {
            Schema::table('erp_sale_returns', function (Blueprint $table) {
                $table->dropColumn('revision_date');
            });
        }

        if (Schema::hasTable('erp_sale_return_histories')) {
            Schema::table('erp_sale_return_histories', function (Blueprint $table) {
                if (Schema::hasColumn('erp_sale_return_histories', 'revision_number')) {
                    $table->dropColumn('revision_number');
                }
                if (Schema::hasColumn('erp_sale_return_histories', 'revision_date')) {
                    $table->dropColumn('revision_date');
                }
            });
        }

        if (Schema::hasTable('erp_sale_return_items') && Schema::hasColumn('erp_sale_return_items', 'sr_item_id')) {
            Schema::table('erp_sale_return_items', function (Blueprint $table) {
                $table->dropColumn(['sr_item_id']);
            });
        }

        if (Schema::hasTable('erp_sale_return_items_histories')) {
            Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
                if (Schema::hasColumn('erp_sale_return_items_histories', 'order_qty')) {
                    $table->dropColumn('order_qty');
                }
                if (Schema::hasColumn('erp_sale_return_items_histories', 'return_amount')) {
                    $table->dropColumn('return_amount');
                }
                if (!Schema::hasColumn('erp_sale_return_items_histories', 'sr_item_id')) {
                    $table->unsignedBigInteger('sr_item_id')->nullable();
                }
            });
        }

        if (Schema::hasTable('erp_so_items') && Schema::hasColumn('erp_so_items', 'srn_qty')) {
            Schema::table('erp_so_items', function (Blueprint $table) {
                $table->dropColumn('srn_qty');
            });
        }

        if (Schema::hasTable('erp_sale_return_teds') && Schema::hasColumn('erp_sale_return_teds', 'ted_percentage')) {
            Schema::table('erp_sale_return_teds', function (Blueprint $table) {
                $table->dropColumn('ted_percentage');
            });
        }

        if (Schema::hasTable('erp_sale_return_ted_histories') && Schema::hasColumn('erp_sale_return_ted_histories', 'ted_percentage')) {
            Schema::table('erp_sale_return_ted_histories', function (Blueprint $table) {
                $table->dropColumn('ted_percentage');
            });
        }

        if (Schema::hasTable('erp_sale_return_item_attribute_histories')) {
            Schema::table('erp_sale_return_item_attribute_histories', function (Blueprint $table) {
                $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
            });
        }

        if (Schema::hasTable('erp_sale_return_location_histories')) {
            Schema::table('erp_sale_return_location_histories', function (Blueprint $table) {
                $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
            });
        }

        if (Schema::hasTable('erp_sale_return_ted_histories')) {
            Schema::table('erp_sale_return_ted_histories', function (Blueprint $table) {
                $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
            });
        }

        if (Schema::hasTable('erp_so_items') && Schema::hasColumn('erp_so_items', 'work_order_qty')) {
            Schema::table('erp_so_items', function (Blueprint $table) {
                $table->dropColumn('work_order_qty');
            });
        }

        if (Schema::hasTable('erp_pwo_items') && Schema::hasColumn('erp_pwo_items', 'so_item_id')) {
            Schema::table('erp_pwo_items', function (Blueprint $table) {
                $table->dropColumn('so_item_id');
            });
        }
    }
};
