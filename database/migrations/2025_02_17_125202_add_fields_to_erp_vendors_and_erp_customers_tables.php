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
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->integer('book_id')->nullable()->after('ledger_id'); 
            $table->string('book_code')->nullable()->after('book_id'); 
            $table->string('vendor_code_type')->nullable()->after('book_code');
            $table->string('vendor_initial')->nullable()->after('company_name');
         
           
        });

        Schema::table('erp_customers', function (Blueprint $table) {
            $table->integer('book_id')->nullable()->after('ledger_id'); 
            $table->string('book_code')->nullable()->after('book_id'); 
            $table->string('customer_code_type')->nullable()->after('book_code');
            $table->string('customer_initial')->nullable()->after('company_name');
        });
    }

    public function down()
    {
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropColumn(['vendor_initial', 'vendor_code_type', 'book_id', 'book_code']); 
        });
        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropColumn(['customer_initial', 'customer_code_type', 'book_id', 'book_code']); 
        });
    }
};
