<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

class CreateErpMeetingStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_meeting_status', function (Blueprint $table) {
            $table->id();
            $table->string('alias', 100)->nullable();
            $table->string('title', 299)->nullable();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);;
            $table->integer('organization_id')->index();
            $table->string('icon', 50)->nullable();
            $table->string('color_code', 299)->nullable();
            $table->integer('sequence')->nullable();
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
        Schema::dropIfExists('erp_meeting_status');
    }
}
