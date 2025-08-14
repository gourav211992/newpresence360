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
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->string('address')->nullable()->after('proceed_date');
            $table->string('telex')->nullable()->after('address');
            $table->string('constitution')->nullable()->after('telex');
            $table->string('scheduled_tribe')->nullable()->after('constitution');
            $table->string('partner')->nullable()->after('scheduled_tribe');
            $table->string('partner_ship')->nullable()->after('partner');
            $table->integer('type')->nullable()->after('partner_ship');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_home_loans', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'telex',
                'constitution',
                'scheduled_tribe',
                'partner',
                'partner_ship',
            ]);
        });
    }
};
