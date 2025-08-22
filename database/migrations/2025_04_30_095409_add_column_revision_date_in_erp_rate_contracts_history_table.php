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
        Schema::table('erp_rate_contract_history', function (Blueprint $table) {
            //
            if(!Schema::hasColumn('erp_rate_contract_history','revision_date'))
            {
                $table->date('revision_date')->nullable()->after('revision_number');
            }
            if(!Schema::hasColumn('erp_rate_contract_history','currency_id'))
            {
                $table->date('currency_id')->nullable()->after('vendor_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_rate_contract_history', function (Blueprint $table) {
            //
            
            if(Schema::hasColumn('erp_rate_contract_history','revision_date'))
            {
                $table->dropColumn('revision_date');
            }
            if(Schema::hasColumn('erp_rate_contract_history','currency_id'))
            {
                $table->dropColumn('currency_id');
            }
        });
    }
};
