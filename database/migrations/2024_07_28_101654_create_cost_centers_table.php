<?php

// database/migrations/XXXX_XX_XX_XXXXXX_create_cost_centers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCostCentersTable extends Migration
{
    public function up()
    {
        Schema::create('erp_cost_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cost_group_id')->constrained('erp_cost_groups')->onDelete('cascade');
            $table->string('name')->unique();
            $table->string('status');

            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('organization_id');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('erp_cost_centers');
    }
}
