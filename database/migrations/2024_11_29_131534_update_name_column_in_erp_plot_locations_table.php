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
        Schema::table('erp_plot_locations', function (Blueprint $table) {
            $table->text('name')->nullable()->change(); // Change datatype to text
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_plot_locations', function (Blueprint $table) {
            $table->string('name')->nullable()->change(); // Revert back to string
        });
    }
};
