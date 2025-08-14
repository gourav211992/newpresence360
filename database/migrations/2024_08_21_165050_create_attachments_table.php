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
    if (!Schema::hasTable('erp_attachments')) {
    Schema::create('erp_attachments', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('email_id');
        $table->string('file_path');
        $table->string('file_name');
        $table->timestamps();

        $table->foreign('email_id')->references('id')->on('erp_emails')->onDelete('cascade');
    });
}
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_attachments');
    }
};
