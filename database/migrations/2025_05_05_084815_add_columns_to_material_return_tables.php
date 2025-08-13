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
        if (Schema::hasTable('erp_mr_items')) {
            Schema::table('erp_mr_items', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_mr_items', 'station_id')) {
                    $table->unsignedBigInteger('station_id')->nullable();
                }
                if (!Schema::hasColumn('erp_mr_items', 'station_code')) {
                    $table->string('station_code')->nullable();
                }
                if (!Schema::hasColumn('erp_mr_items', 'to_station_id')) {
                    $table->unsignedBigInteger('to_station_id')->nullable();
                }
                if (!Schema::hasColumn('erp_mr_items', 'to_station_code')) {
                    $table->string('to_station_code')->nullable();
                }
            });
        }

        if (Schema::hasTable('erp_mr_item_locations')) {
            Schema::table('erp_mr_item_locations', function (Blueprint $table) {
                if (!Schema::hasColumn('erp_mr_item_locations', 'sub_store_id')) {
                    $table->unsignedBigInteger('sub_store_id')->nullable();
                }
                if (!Schema::hasColumn('erp_mr_item_locations', 'sub_store_code')) {
                    $table->string('sub_store_code')->nullable();
                }
                if (!Schema::hasColumn('erp_mr_item_locations', 'station_id')) {
                    $table->unsignedBigInteger('station_id')->nullable();
                }
                if (!Schema::hasColumn('erp_mr_item_locations', 'station_code')) {
                    $table->string('station_code')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('erp_mr_items')) {
            Schema::table('erp_mr_items', function (Blueprint $table) {
            if (Schema::hasColumn('erp_mr_items', 'station_id')) {
                $table->dropColumn('station_id');
            }
            if (Schema::hasColumn('erp_mr_items', 'station_code')) {
                $table->dropColumn('station_code');
            }
            if (Schema::hasColumn('erp_mr_items', 'to_station_id')) {
                $table->dropColumn('to_station_id');
            }
            if (Schema::hasColumn('erp_mr_items', 'to_station_code')) {
                $table->dropColumn('to_station_code');
            }
            });
        }

        if (Schema::hasTable('erp_mr_item_locations')) {
            Schema::table('erp_mr_item_locations', function (Blueprint $table) {
            if (Schema::hasColumn('erp_mr_item_locations', 'sub_store_id')) {
                $table->dropColumn('sub_store_id');
            }
            if (Schema::hasColumn('erp_mr_item_locations', 'sub_store_code')) {
                $table->dropColumn('sub_store_code');
            }
            if (Schema::hasColumn('erp_mr_item_locations', 'station_id')) {
                $table->dropColumn('station_id');
            }
            if (Schema::hasColumn('erp_mr_item_locations', 'station_code')) {
                $table->dropColumn('station_code');
            }
            });
        }
    }
};
