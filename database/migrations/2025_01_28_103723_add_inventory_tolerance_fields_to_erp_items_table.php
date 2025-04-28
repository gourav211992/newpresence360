<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->decimal('po_positive_tolerance', 10, 2)->after('shelf_life_days')->nullable();
            $table->decimal('po_negative_tolerance', 10, 2)->nullable();
            $table->decimal('so_positive_tolerance', 10, 2)->nullable();
            $table->decimal('so_negative_tolerance', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn(['po_positive_tolerance', 'po_negative_tolerance', 'so_positive_tolerance', 'so_negative_tolerance']);
        });
    }
};
