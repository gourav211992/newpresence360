<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailRecipientsTable extends Migration
{
    public function up()
    {
        Schema::create('erp_email_recipients', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('email_id'); // Foreign key for the email
            $table->Integer('user_id'); // Foreign key for the user (employee)
            $table->string('type'); // Column to store the type of recipient
            $table->timestamps(); // Created at and Updated at timestamps

            // Foreign key constraints
            $table->foreign('email_id')
                  ->references('id')->on('erp_emails')
                  ->onDelete('cascade'); // Assumes the emails table has an id primary key
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_email_recipients');
    }
}
