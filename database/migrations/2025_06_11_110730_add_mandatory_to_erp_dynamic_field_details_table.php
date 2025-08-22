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
        Schema::table('erp_dynamic_field_details', function (Blueprint $table) {
            $table->boolean('mandatory')->default(false)->after('data_type'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_dynamic_field_details', function (Blueprint $table) {
            $table->dropColumn('mandatory');
        });
    }
};
