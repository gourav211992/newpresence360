<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErpFinancialSetupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_financial_setup', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // For 'name' column
            $table->string('ledger_id'); // For 'ledger_id' column
            $table->string('ledger_group_id'); // For 'ledger_group_id' column
            $table->string('status')->default('1');
            $table->timestamps(); // To store created_at and updated_at timestamps

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_financial_setup');
    }
}
