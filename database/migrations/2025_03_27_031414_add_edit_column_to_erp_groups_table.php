<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('erp_groups', function (Blueprint $table) {
            $table->boolean('edit')->default(true)->after('master_group_id'); // Replace 'existing_column' with the column after which you want to place 'edit'
        });
    }

    public function down()
    {
        Schema::table('erp_groups', function (Blueprint $table) {
            $table->dropColumn('edit');
        });
    }
};
