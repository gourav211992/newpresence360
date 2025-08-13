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
        Schema::table('erp_bom_uploads', function (Blueprint $table) {
            $table->string('product_attribute_name_1')->nullable()->after('product_attributes');    
            $table->string('product_attribute_value_1')->nullable()->after('product_attribute_name_1');    
            $table->string('product_attribute_name_2')->nullable()->after('product_attribute_value_1');    
            $table->string('product_attribute_value_2')->nullable()->after('product_attribute_name_2');    
            $table->string('product_attribute_name_3')->nullable()->after('product_attribute_value_2');    
            $table->string('product_attribute_value_3')->nullable()->after('product_attribute_name_3');    
            $table->string('product_attribute_name_4')->nullable()->after('product_attribute_value_3');    
            $table->string('product_attribute_value_4')->nullable()->after('product_attribute_name_4');    
            $table->string('product_attribute_name_5')->nullable()->after('product_attribute_value_4');    
            $table->string('product_attribute_value_5')->nullable()->after('product_attribute_name_5');
            
            $table->string('section_id')->nullable()->after('station_name');
            $table->string('section_name')->nullable()->after('section_id');
            $table->string('sub_section_id')->nullable()->after('section_name');
            $table->string('sub_section_name')->nullable()->after('sub_section_id');
            $table->string('vendor_id')->nullable()->after('sub_section_name');
            $table->string('vendor_code')->nullable()->after('vendor_id');
            $table->string('vendor_name')->nullable()->after('vendor_code');
            
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bom_uploads', function (Blueprint $table) {
            $table->dropColumn('product_attribute_name_1');
            $table->dropColumn('product_attribute_value_1');
            $table->dropColumn('product_attribute_name_2'); 
            $table->dropColumn('product_attribute_value_2');
            $table->dropColumn('product_attribute_name_3');
            $table->dropColumn('product_attribute_value_3');
            $table->dropColumn('product_attribute_name_4');
            $table->dropColumn('product_attribute_value_4');
            $table->dropColumn('product_attribute_name_5');
            $table->dropColumn('product_attribute_value_5');
            $table->dropColumn('section_id');
            $table->dropColumn('section_name');
            $table->dropColumn('sub_section_id');
            $table->dropColumn('sub_section_name');
            $table->dropColumn('vendor_id');
            $table->dropColumn('vendor_code');
            $table->dropColumn('vendor_name');
            
        });
    }
};
