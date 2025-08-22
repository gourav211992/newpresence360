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
        Schema::table('erp_mfg_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->unsignedBigInteger('production_bom_id')->nullable()->index();
            $table->unsignedBigInteger('production_route_id')->nullable()->index();
            $table->unsignedBigInteger('sf_item_id')->nullable();
            $table->json('sf_item_attributes')->nullable();
            $table->double('sf_qty',[20,6])->default(0);
        });

        Schema::create('erp_mo_production_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mo_id')->index();
            $table->unsignedBigInteger('item_id')->index();
            $table->string('item_code')->nullable()->index();
            $table->unsignedBigInteger('uom_id')->index();
            $table->json('attributes')->nullable();
            $table->double('required_qty',[20,6])->default(0);
            $table->double('produced_qty',[20,6])->default(0);
            $table->double('rate',[20,4])->default(0);
            $table->timestamps();
        });

        Schema::table('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->unsignedBigInteger('mo_id')->nullable()->index();
        });
        Schema::table('erp_pwo_station_consumptions', function (Blueprint $table) {
            $table->unsignedBigInteger('mo_id')->nullable()->index();
        });
        Schema::table('erp_mo_items', function (Blueprint $table) {
            $table->enum('rm_type',['rm','sf'])->default('rm')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mfg_orders', function (Blueprint $table) {
            $table->dropIndex(['item_id']);
            $table->dropColumn('item_id');
            $table->dropIndex(['production_bom_id']);
            $table->dropColumn('production_bom_id');
            $table->dropIndex(['production_route_id']);
            $table->dropColumn('production_route_id');
            $table->dropIndex(['sf_item_id']);
            $table->dropColumn('sf_item_id');
            $table->dropColumn('sf_item_attributes');
            $table->dropColumn('sf_qty');
        });

        Schema::dropIfExists('erp_mo_production_items');
        Schema::table('erp_pwo_so_mapping', function (Blueprint $table) {
            $table->dropIndex(['mo_id']);
            $table->dropColumn('mo_id');
        });
        Schema::table('erp_pwo_station_consumptions', function (Blueprint $table) {
            $table->dropIndex(['mo_id']);
            $table->dropColumn('mo_id');
        });
        Schema::table('erp_mo_items', function (Blueprint $table) {
            $table->dropIndex(['rm_type']);
            $table->dropColumn('rm_type');
        });
    }
};
