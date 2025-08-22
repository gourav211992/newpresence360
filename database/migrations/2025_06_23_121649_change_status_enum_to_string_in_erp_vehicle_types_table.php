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
        Schema::table('erp_vehicle_types', function (Blueprint $table) {
            $table->string('status', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_vehicle_types', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive' , 'block','transfer','blacklist'])->default('active')->change();
        });
    }
};
