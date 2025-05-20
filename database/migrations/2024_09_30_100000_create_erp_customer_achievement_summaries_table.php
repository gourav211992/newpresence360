<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErpCustomerAchievementSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_customer_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index()->nullable();
            $table->string('customer_code', 255)->index();
            $table->unsignedBigInteger('organization_id')->index();
            $table->string('channel_partner_name', 255)->nullable();
            $table->string('location_code', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('sales_rep_code', 255)->nullable();
            $table->double('ly_sale')->nullable();
            $table->double('cy_sale')->nullable();
            $table->integer('apr')->nullbale();
            $table->integer('may')->nullbale();
            $table->integer('jun')->nullbale();
            $table->integer('jul')->nullbale();
            $table->integer('aug')->nullbale();
            $table->integer('sep')->nullbale();
            $table->integer('oct')->nullbale();
            $table->integer('nov')->nullbale();
            $table->integer('dec')->nullbale();
            $table->integer('jan')->nullbale();
            $table->integer('feb')->nullbale();
            $table->integer('mar')->nullbale();
            $table->string('year', 255);
            $table->decimal('total_target', 10, 2)->default(0);
            $table->unsignedBigInteger('created_by')->index()->nullable();
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
        Schema::dropIfExists('erp_customer_targets');
    }
}
