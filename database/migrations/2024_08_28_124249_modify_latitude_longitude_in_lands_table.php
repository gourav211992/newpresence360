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
    Schema::table('erp_lands', function (Blueprint $table) {
        $table->decimal('latitude', 10, 7)->change();
        $table->decimal('longitude', 10, 7)->change();
    });
}

public function down()
{
    Schema::table('erp_lands', function (Blueprint $table) {
        $table->decimal('latitude', 8, 5)->change(); // Revert to original size if needed
        $table->decimal('longitude', 8, 5)->change();
    });
}

};
