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
        Schema::create('erp_sub_stores', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30);
            $table->string('name');
            $table->string('type')->comment('Stock, Shop Floor, Others');
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('erp_sub_store_parents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('sub_store_id')->index();
            $table->foreign('sub_store_id')->references('id')->on('erp_sub_stores');
            $table->unsignedBigInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('erp_stores');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    
        Schema::create('erp_sub_store_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('store_id')->index();
            $table->foreign('store_id')->references('id')->on('erp_stores');
            $table->unsignedBigInteger('sub_store_id')->index();
            $table->foreign('sub_store_id')->references('id')->on('erp_sub_stores');
            $table->string('code', 30);
            $table->string('name');
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('erp_racks', function (Blueprint $table) {
           $table->unsignedBigInteger('sub_store_id')->after('erp_store_id')->nullable();
           $table->foreign('sub_store_id')->references('id')->on('erp_sub_stores');
           $table->unsignedBigInteger('sub_store_zone_id')->after('sub_store_id')->nullable();
           $table->foreign('sub_store_zone_id')->references('id')->on('erp_sub_store_zones');

        });

        Schema::table('erp_shelfs', function (Blueprint $table) {
           $table->unsignedBigInteger('sub_store_id')->after('erp_store_id')->nullable();
           $table->foreign('sub_store_id')->references('id')->on('erp_sub_stores');
           $table->unsignedBigInteger('sub_store_zone_id')->after('sub_store_id')->nullable();
           $table->foreign('sub_store_zone_id')->references('id')->on('erp_sub_store_zones');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_racks', function (Blueprint $table) {
            $table->dropForeign('erp_racks_sub_store_id_foreign');
            $table->dropForeign('erp_racks_sub_store_zone_id_foreign');
            $table->dropColumn(['sub_store_id', 'sub_store_zone_id']);
         });
 
         Schema::table('erp_shelfs', function (Blueprint $table) {
            $table->dropForeign('erp_shelfs_sub_store_id_foreign');
            $table->dropForeign('erp_shelfs_sub_store_zone_id_foreign');
            $table->dropColumn(['sub_store_id', 'sub_store_zone_id']);
         });
        Schema::dropIfExists('erp_sub_store_zones');
        Schema::dropIfExists('erp_sub_store_parents');
        Schema::dropIfExists('erp_sub_stores');
    }
};
