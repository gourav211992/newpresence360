<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('erp_vehicle_bank_securities', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('opening_acc')->nullable();
            $table->string('bank_name1')->nullable();
            $table->longText('bank_addr1')->nullable();
            $table->string('bank_name2')->nullable();
            $table->longText('bank_addr2')->nullable();
            $table->string('acc_nature')->nullable();
            $table->longText('borrowing_detail')->nullable();
            $table->longText('security_offerd')->nullable();
            $table->longText('security_desc')->nullable();
            $table->string('security_market_val')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_vehicle_bank_securities');
    }
};
