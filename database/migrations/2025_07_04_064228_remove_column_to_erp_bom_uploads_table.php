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
            $table->dropColumn([
                'product_attribute_name_1',
                'product_attribute_value_1',
                'product_attribute_name_2',
                'product_attribute_value_2',
                'product_attribute_name_3',
                'product_attribute_value_3',
                'product_attribute_name_4',
                'product_attribute_value_4',
                'product_attribute_name_5',
                'product_attribute_value_5',
                'attribute_name_1',
                'attribute_value_1',
                'attribute_name_2',
                'attribute_value_2',
                'attribute_name_3',
                'attribute_value_3',
                'attribute_name_4',
                'attribute_value_4',
                'attribute_name_5',
                'attribute_value_5',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_bom_uploads', function (Blueprint $table) {
            //
        });
    }
};
