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
        Schema::table('erp_sale_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_sale_returns', 'revision_date')) {
                $table->date('revision_date')->nullable();
            }
            if (!Schema::hasColumn('erp_sale_returns', 'store_code')) {
                $table->string('store_code')->nullable();
            }
            if (Schema::hasColumn('erp_sale_returns', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->change();
            } else {
                $table->unsignedBigInteger('store_id')->nullable();
            }
        });
        Schema::table('erp_sale_return_teds', function (Blueprint $table) {
            if (Schema::hasColumn('erp_sale_return_teds', 'ted_percentage')) {
                $table->double('ted_percentage', 15, 8)->change()->nullable();
            } else {
                $table->double('ted_percentage', 15, 8)->nullable();
            }
        });
        Schema::table('erp_sale_return_ted_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_sale_return_ted_histories', 'ted_percentage')) {
                $table->double('ted_percentage', 15, 8)->change()->nullable();
            } else {
                $table->double('ted_percentage', 15, 8)->nullable();
            }
        });
        Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('erp_sale_return_items_histories', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable();
            }
            if (Schema::hasColumn('erp_sale_return_items_histories', 'sr_item_id')) {
                $table->unsignedBigInteger('sr_item_id')->change()->nullable();
            } else {
                $table->unsignedBigInteger('sr_item_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sale_returns', function (Blueprint $table) {
            if (Schema::hasColumn('erp_sale_returns', 'revision_date')) {
                $table->dropColumn('revision_date');
            }
            if (Schema::hasColumn('erp_sale_returns', 'store_code')) {
                $table->dropColumn('store_code');
            }
            if (Schema::hasColumn('erp_sale_returns', 'store_id')) {
                $table->dropColumn('store_id');            
            }
        });

        Schema::table('erp_sale_return_teds', function (Blueprint $table) {
            if (Schema::hasColumn('erp_sale_return_teds', 'ted_percentage')) {
                $table->dropColumn('ted_percentage');
            }
        });

        Schema::table('erp_sale_return_ted_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_sale_return_ted_histories', 'ted_percentage')) {
                $table->dropColumn('ted_percentage');
            }
        });

        Schema::table('erp_sale_return_items_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_sale_return_items_histories', 'store_id')) {
                $table->dropColumn('store_id');
            }
            if (Schema::hasColumn('erp_sale_return_items_histories', 'sr_item_id')) {
                $table->dropColumn('sr_item_id');
            }
        });
    }

};
