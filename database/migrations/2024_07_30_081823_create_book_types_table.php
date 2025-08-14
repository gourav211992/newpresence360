<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookTypesTable extends Migration
{
    public function up()
    {
        Schema::create('erp_book_types', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->string('name');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_book_types');
    }
}
