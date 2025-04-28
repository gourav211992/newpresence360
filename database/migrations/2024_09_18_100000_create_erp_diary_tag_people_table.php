<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErpDiaryTagPeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_diary_tag_people', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diary_id')->index()->nullable();
            $table->integer('tag_people_id')->index()->nullable();
            $table->integer('organization_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_diary_tag_people');
    }
}
