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
            $table->unsignedBigInteger('storage_uom_id')->nullable()->after('uom_id');
            $table->float('storage_uom_conversion', 8, 2)->nullable()->after('storage_uom_id');
            $table->integer('storage_uom_count')->nullable()->after('storage_uom_conversion');
        });
    }

    public function down()
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn('storage_uom_id');
            $table->dropColumn('storage_uom_conversion');
            $table->dropColumn('storage_uom_count');
        });
    }

};
