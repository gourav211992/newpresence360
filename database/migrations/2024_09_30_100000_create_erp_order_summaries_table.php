<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErpOrderSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_order_summaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index()->nullable();
            $table->string('customer_code', 255)->index();
            $table->unsignedBigInteger('organization_id')->index();
            $table->date('date')->nullable();
            $table->decimal('total_order_value', 15, 2)->default(0);
            $table->unsignedBigInteger('created_by')->index();
            $table->unsignedBigInteger('updated_by')->index()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_order_summaries');
    }
}
