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
            $table->string('taxpayer_type')->nullable()->after('display_name'); 
            $table->string('gst_status')->nullable()->after('taxpayer_type');    
            $table->string('block_status')->nullable()->after('gst_status');    
            $table->date('deregistration_date')->nullable()->after('block_status'); 
            $table->string('legal_name')->nullable()->after('deregistration_date');
            $table->unsignedBigInteger('gst_state_id')->nullable()->after('legal_name'); 
        });

        Schema::table('erp_customers', function (Blueprint $table) {
            $table->string('taxpayer_type')->nullable()->after('display_name');  
            $table->string('gst_status')->nullable()->after('taxpayer_type');    
            $table->string('block_status')->nullable()->after('gst_status');    
            $table->date('deregistration_date')->nullable()->after('block_status');  
            $table->string('legal_name')->nullable()->after('deregistration_date');  
            $table->unsignedBigInteger('gst_state_id')->nullable()->after('legal_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('erp_vendors', function (Blueprint $table) {
            $table->dropColumn([
                'taxpayer_type','gst_status','block_status','deregistration_date','legal_name' ,'gst_state_id'        
            ]);
        });

        Schema::table('erp_customers', function (Blueprint $table) {
            $table->dropColumn([
                'taxpayer_type','gst_status','block_status','deregistration_date','legal_name','gst_state_id'   
            ]);
        });
    }
};
