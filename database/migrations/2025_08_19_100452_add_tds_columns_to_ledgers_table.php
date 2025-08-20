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
        Schema::table('erp_ledgers', function (Blueprint $table) {
            $table->decimal('tds_without_pan', 10, 2)->nullable()->after('tds_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
       
    }
};
