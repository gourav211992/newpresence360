<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            $table->dropColumn('comapny_id'); // Fixing the typo by removing this column
        });
    }

    public function down() {
        Schema::table('erp_land_parcels', function (Blueprint $table) {
            $table->bigInteger('comapny_id')->unsigned()->nullable(); // In case of rollback
        });
    }
};
