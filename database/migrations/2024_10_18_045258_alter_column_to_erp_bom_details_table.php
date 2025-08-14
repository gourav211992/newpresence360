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
        Schema::table('erp_bom_details', function (Blueprint $table) {
            $table->decimal('qty',20,6)->default(0)->change();
            $table->decimal('item_cost',20,6)->default(0)->change();
            $table->decimal('superceeded_cost',20,6)->default(0)->change();
        });
        Schema::table('erp_bom_details_history', function (Blueprint $table) {
            $table->decimal('qty',20,6)->default(0)->change();
            $table->decimal('item_cost',20,6)->default(0)->change();
            $table->decimal('superceeded_cost',20,6)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bom_details', function (Blueprint $table) {
            $table->integer('qty')->default(0)->change();
            $table->decimal('item_cost',15,2)->default(0)->change();
            $table->decimal('superceeded_cost',15,2)->default(0)->change();
        });
        Schema::table('erp_bom_details', function (Blueprint $table) {
            $table->integer('qty')->default(0)->change();
            $table->decimal('item_cost',15,2)->default(0)->change();
            $table->decimal('superceeded_cost',15,2)->default(0)->change();
        });
    }
};
