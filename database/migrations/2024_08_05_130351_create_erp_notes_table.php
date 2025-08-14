<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_notes', function (Blueprint $table) {
            $table->id();
            $table->text('remark')->nullable();
            $table->morphs('noteable');
            $table->unsignedBigInteger('created_by')->nullable()->index();
            //$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_notes');
    }
};
