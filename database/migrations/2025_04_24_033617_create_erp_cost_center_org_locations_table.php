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
    Schema::create('erp_cost_center_org_locations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cost_center_id');
        $table->unsignedBigInteger('location_id');
        $table->unsignedBigInteger('organization_id');
        $table->timestamps();
       });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_cost_center_org_locations');
    }
};
