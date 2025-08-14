<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

class CreateErpGroupMasterSharingTable extends Migration
{
   
    public function up()
    {
        Schema::create('erp_group_master_sharing', function (Blueprint $table) {
            $table->id(); 
            $table->integer('group_id')->nullable(); 
            $table->string('master_name')->nullable();
            $table->string('alias')->nullable();
            $table->enum('sharing_policy', ConstantHelper::SHARING_POLICY)->nullable();
            $table->string('default')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_group_master_sharing');
    }
}
