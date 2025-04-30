<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('erp_crdr_report_schedulers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('ledger_id')->nullable();
            $table->bigInteger('ledger_group_id')->nullable();
            $table->string('report_type');
            $table->unsignedBigInteger('toable_id');
            $table->string('toable_type');
            $table->string('type');
            $table->dateTime('date');
            $table->timestamp('last_run')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_crdr_report_schedulers');
    }
};