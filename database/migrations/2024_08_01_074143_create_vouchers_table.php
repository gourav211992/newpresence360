<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_vouchers', function (Blueprint $table) {
            $table->id();
            $table->integer('voucher_no')->unique();
            $table->string('voucher_name');
            $table->unsignedBigInteger('book_type_id');
            $table->unsignedBigInteger('book_id');
            $table->date('date');
            $table->string('document')->nullable();
            $table->string('remarks')->nullable();

            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');
            
            $table->timestamps();

            $table->foreign('book_type_id')->references('id')->on('erp_book_types')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('erp_books')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_vouchers');
    }
}
