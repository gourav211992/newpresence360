<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('erp_email_recipients', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('email_id');
        $table->unsignedBigInteger('user_id');
        $table->enum('type', ['to', 'cc']);
        $table->timestamps();

        $table->foreign('email_id')->references('id')->on('erp_emails')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_email_recipients');
    }
};
