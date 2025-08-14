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
        // Drop the `erp_financial_setup` table if it exists
        Schema::dropIfExists('erp_financial_setup');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // If needed, you can define the table structure to recreate it
        Schema::create('erp_financial_setup', function (Blueprint $table) {
            $table->id();
            // Add columns and constraints based on the original table structure
            $table->timestamps();
        });
    }
};
