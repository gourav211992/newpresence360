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
        Schema::table('erp_item_specifications', function (Blueprint $table) {


            if (Schema::hasColumn('erp_item_specifications', 'description')) {
                $table->renameColumn('description', 'value');
            }
        });
    }

    public function down()
    {
        Schema::table('erp_item_specifications', function (Blueprint $table) {
            if (Schema::hasColumn('erp_item_specifications', 'value')) {
                $table->renameColumn('value', 'description');
            }
        });
    }
};
