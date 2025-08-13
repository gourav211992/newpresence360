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
       Schema::table('upload_item_masters', function (Blueprint $table) {
            $table->boolean('is_traded_item')->default(0)->after('sub_type');
            $table->boolean('is_asset')->default(0)->after('is_traded_item');
            $table->unsignedBigInteger('asset_category_id')->nullable()->after('is_asset');
            $table->string('brand_name', 255)->nullable()->after('asset_category_id');
            $table->string('model_no', 255)->nullable()->after('brand_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_item_masters', function (Blueprint $table) {
            $table->dropColumn([
                'is_traded_item',
                'is_asset',
                'asset_category',
                'brand_name',
                'model_no',
            ]);
        });
    }
};
