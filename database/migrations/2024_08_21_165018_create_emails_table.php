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
    Schema::create('erp_emails', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('subject')->nullable();
        $table->text('body');
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->timestamps();

        $table->foreign('parent_id')->references('id')->on('erp_emails')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_emails');
    }
};
