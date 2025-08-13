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
        Schema::table('erp_ledgers', function (Blueprint $table) {
            $table->string('tax_type')->nullable();
            $table->decimal('tax_percentage', 10, 2)->nullable()->after('tax_type');
            $table->string('tds_section')->nullable()->after('tax_percentage');
            $table->decimal('tds_percentage', 10, 2)->nullable()->after('tds_section');
            $table->string('tcs_section')->nullable()->after('tds_percentage');
            $table->decimal('tcs_percentage', 10, 2)->nullable()->after('tcs_section');
        });
    }

    public function down()
    {
        Schema::table('erp_ledgers', function (Blueprint $table) {
            $table->dropColumn([
                'tax_type',
                'tax_percentage',
                'tds_section',
                'tds_percentage',
                'tcs_section',
                'tcs_percentage'
            ]);
        });
    }
};
