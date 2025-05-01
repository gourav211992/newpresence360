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
        Schema::table('erp_cost_centers', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['cost_group_id']);

            // Then drop the column
            $table->dropColumn('cost_group_id');
        });
    }

    public function down()
    {
        Schema::table('erp_cost_centers', function (Blueprint $table) {
            // Recreate the column and foreign key constraint
            $table->foreignId('cost_group_id')
                  ->constrained('erp_cost_groups')
                  ->onDelete('cascade');
        });
    }
};
