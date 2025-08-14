<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('erp_ledgers', function (Blueprint $table) {
            // Drop the unique keys for the 'code' and 'name' columns
            $table->dropUnique('erp_ledgers_code_unique'); // Drop the unique index on 'code'
            $table->dropUnique('erp_ledgers_name_unique'); // Drop the unique index on 'name'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_ledgers', function (Blueprint $table) {
            // Re-add the unique constraints for 'code' and 'name' if needed
            $table->unique('erp_ledgers_code_unique');  // Re-add the unique constraint on 'code'
            $table->unique('erp_ledgers_code_unique');  // Re-add the unique constraint on 'name'
        });
    }
};
