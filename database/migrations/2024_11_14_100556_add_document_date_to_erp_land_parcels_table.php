<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('erp_land_parcels', function (Blueprint $table) {
        $table->date('document_date')->nullable()->after('document_no'); // Replace 'column_name' with the column after which you want to add `document_date`
    });
}

public function down()
{
    Schema::table('erp_land_parcels', function (Blueprint $table) {
        $table->dropColumn('document_date');
    });
}

};
