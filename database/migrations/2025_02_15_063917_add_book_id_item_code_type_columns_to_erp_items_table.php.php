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
            $table->integer('book_id')->nullable()->after('uom_id');
            $table->string('book_code', 255)->nullable()->after('book_id')->index();
            $table->string('item_code_type', 255)->nullable()->after('book_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('erp_items', function (Blueprint $table) {
            $table->dropColumn('book_id');
            $table->dropColumn('book_code');
            $table->dropColumn('item_code_type');
        });
    }
};
