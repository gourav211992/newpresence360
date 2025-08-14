<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('erp_finance_fixed_asset_sub_history', function (Blueprint $table) {
            $table->id();

            // History tracking reference
            $table->unsignedBigInteger('source_id')->index()->comment('Refers to erp_finance_fixed_asset_sub.id');

            $table->unsignedBigInteger('parent_id');
            $table->string('sub_asset_code');
            $table->decimal('current_value', 15, 2);
            $table->timestamps(); // includes created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_finance_fixed_asset_sub_history');
    }
};
