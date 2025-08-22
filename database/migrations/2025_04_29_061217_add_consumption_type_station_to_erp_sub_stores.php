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
        Schema::table('erp_sub_stores', function (Blueprint $table) {
            $table -> string('station_wise_consumption', 5) -> default('no') -> index() -> after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_sub_stores', function (Blueprint $table) {
            $table -> dropColumn('station_wise_consumption');
        });
    }
};
