<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErpDiariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_diaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index()->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_code', 255)->nullable();
            $table->unsignedBigInteger('organization_id')->index();
            $table->unsignedBigInteger('meeting_status_id')->index();
            $table->unsignedBigInteger('meeting_objective_id')->index();
            $table->string('customer_type', 255);
            $table->unsignedBigInteger('industry_id')->index();
            $table->string('contact_person', 255);
            $table->double('sales_figure')->default(0);
            $table->string('email', 255)->index();
            $table->string('location', 255)->nullable();
            $table->string('subject', 255)->nullable();
            $table->longText('description')->nullable();
            $table->string('document_path', 255)->nullable();
            $table->string('created_by_type', 100)->nullable();
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
        Schema::dropIfExists('erp_diaries');
    }
}
