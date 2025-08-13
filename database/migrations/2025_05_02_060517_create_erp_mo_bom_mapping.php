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
        Schema::create('erp_mo_bom_mapping', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mo_id')->nullable();
            $table->unsignedBigInteger('mo_product_id')->nullable();
            $table->unsignedBigInteger('old_mo_product_id')->nullable();
            $table->unsignedBigInteger('so_id')->nullable();
            $table->unsignedBigInteger('bom_id')->nullable();
            $table->unsignedBigInteger('bom_detail_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_code')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->json('attributes')->nullable();
            $table->enum('rm_type',['rm','sf'])->default('rm')->index();
            $table->double('bom_qty', 20,6)->default(0);
            $table->double('consumption_qty', 20,6)->default(0);
            $table->unsignedBigInteger('station_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('sub_section_id')->nullable();
            $table->timestamps();
        });

        Schema::table('erp_pwo_bom_mapping', function (Blueprint $table) {
            $table->double('bom_qty', 20,6)->default(0)->after('uom_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_pwo_bom_mapping', function (Blueprint $table) {
            $table->dropColumn('bom_qty');
        });
        Schema::dropIfExists('erp_mo_bom_mapping');
    }
};
