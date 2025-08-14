<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropEmailRecipientsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('erp_email_recipients');
    }

    public function down()
    {
        Schema::create('erp_email_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // Add other columns here based on your original schema
            $table->timestamps();
        });
    }
}

