<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpeningAndClosingToItemDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_item_details', function (Blueprint $table) {
            $table->decimal('opening', 8, 2)->nullable();
            $table->decimal('closing', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_item_details', function (Blueprint $table) {
            $table->dropColumn(['opening', 'closing']);
        });
    }
}
