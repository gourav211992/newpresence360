<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMinMaxValuesToErpAmendmentWorkflowsTable extends Migration
{
    public function up()
    {
        Schema::table('erp_amendment_workflows', function (Blueprint $table) {
            $table->decimal('min_value', 15, 2)->nullable()->after('user_id');
            $table->decimal('max_value', 15, 2)->nullable()->after('min_value');
        });
    }

    public function down()
    {
        Schema::table('erp_amendment_workflows', function (Blueprint $table) {
            $table->dropColumn(['min_value', 'max_value']);
        });
    }
}
