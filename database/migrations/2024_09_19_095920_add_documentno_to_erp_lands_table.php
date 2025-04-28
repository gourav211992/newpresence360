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
        $table->string('documentno')->nullable()->after('series'); // Adjust 'after' if needed
    });
}

public function down()
{
    Schema::table('erp_lands', function (Blueprint $table) {
        $table->dropColumn('documentno');
    });
}

};
