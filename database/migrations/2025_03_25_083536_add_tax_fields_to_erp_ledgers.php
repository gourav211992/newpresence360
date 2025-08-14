<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('erp_ledgers', function (Blueprint $table) {
            $table->string('tax_type')->nullable()->after('ledger_group_id');
            $table->decimal('tax_percentage', 10, 2)->nullable()->after('tax_type');
        });
    }

    public function down()
    {
        Schema::table('erp_ledgers', function (Blueprint $table) {
            $table->dropColumn(['tax_type', 'tax_percentage']);
        });
    }
};


