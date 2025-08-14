<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyCostNullableInErpLandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_lands', function (Blueprint $table) {
            $table->decimal('cost', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_lands', function (Blueprint $table) {
            $table->decimal('cost', 15, 2)->nullable(false)->change();
        });
    }
}

