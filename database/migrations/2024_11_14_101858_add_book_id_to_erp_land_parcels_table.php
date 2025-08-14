<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id')->nullable()->after('id'); // Replace 'column_name' with the appropriate column after which you want to add this field
        });
    }

    public function down()
    {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            $table->dropColumn('book_id');
        });
    }
};
