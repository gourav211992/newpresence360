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
        Schema::table('erp_material_return_header', function (Blueprint $table) {
            //add to_store_id , to_store_code , from_sub_store_id , from_sub_store_code , to_sub_store_id , to_sub_store_code column
            $table->unsignedBigInteger('to_store_id')->nullable()->after('document_status');
            $table->string('to_store_code', 50)->nullable()->after('to_store_id');
            $table->unsignedBigInteger('from_sub_store_id')->nullable()->after('to_store_code');
            $table->string('from_sub_store_code', 50)->nullable()->after('from_sub_store_id');
            $table->unsignedBigInteger('to_sub_store_id')->nullable()->after('from_sub_store_code');
            $table->string('to_sub_store_code', 50)->nullable()->after('to_sub_store_id');
        }); 
        Schema::table('erp_material_return_header_history', function (Blueprint $table) {
            //add to_store_id , to_store_code , from_sub_store_id , from_sub_store_code , to_sub_store_id , to_sub_store_code column
            $table->unsignedBigInteger('to_store_id')->nullable()->after('document_status');
            $table->string('to_store_code', 50)->nullable()->after('to_store_id');
            $table->unsignedBigInteger('from_sub_store_id')->nullable()->after('to_store_code');
            $table->string('from_sub_store_code', 50)->nullable()->after('from_sub_store_id');
            $table->unsignedBigInteger('to_sub_store_id')->nullable()->after('from_sub_store_code');
            $table->string('to_sub_store_code', 50)->nullable()->after('to_sub_store_id');
        });
        Schema::table('erp_mr_items', function (Blueprint $table) {
            //add to_store_id , to_store_code , from_sub_store_id , from_sub_store_code , to_sub_store_id , to_sub_store_code column
            $table->unsignedBigInteger('from_sub_store_id')->nullable()->after('to_store_code');
            $table->string('from_sub_store_code', 50)->nullable()->after('from_sub_store_id');
            $table->unsignedBigInteger('to_sub_store_id')->nullable()->after('from_sub_store_code');
            $table->string('to_sub_store_code', 50)->nullable()->after('to_sub_store_id');
        });
        Schema::table('erp_mr_items_history', function (Blueprint $table) {
            //add to_store_id , to_store_code , from_sub_store_id , from_sub_store_code , to_sub_store_id , to_sub_store_code column
            $table->unsignedBigInteger('from_sub_store_id')->nullable()->after('to_store_code');
            $table->string('from_sub_store_code', 50)->nullable()->after('from_sub_store_id');
            $table->unsignedBigInteger('to_sub_store_id')->nullable()->after('from_sub_store_code');
            $table->string('to_sub_store_code', 50)->nullable()->after('to_sub_store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_material_return_header', function (Blueprint $table) {
            //
            //remove to_store_id , to_store_code , from_sub_store_id , from_sub_store_code , to_sub_store_id , to_sub_store_code column
            $table->dropColumn(['to_store_id', 'to_store_code', 'from_sub_store_id', 'from_sub_store_code', 'to_sub_store_id', 'to_sub_store_code']);
        });
        Schema::table('erp_material_return_header_history', function (Blueprint $table) {
            //
            //remove to_store_id , to_store_code , from_sub_store_id , from_sub_store_code , to_sub_store_id , to_sub_store_code column
            $table->dropColumn(['to_store_id', 'to_store_code', 'from_sub_store_id', 'from_sub_store_code', 'to_sub_store_id', 'to_sub_store_code']);
        });
        Schema::table('erp_mr_items', function (Blueprint $table) {
            //
            //remove to_store_id , to_store_code , from_sub_store_id , from_sub_store_code , to_sub_store_id , to_sub_store_code column
            $table->dropColumn(['from_sub_store_id', 'from_sub_store_code', 'to_sub_store_id', 'to_sub_store_code']);
        });
        Schema::table('erp_mr_items_history', function (Blueprint $table) {
            //
            //remove to_store_id , to_store_code , from_sub_store_id , from_sub_store_code , to_sub_store_id , to_sub_store_code column
            $table->dropColumn(['from_sub_store_id', 'from_sub_store_code', 'to_sub_store_id', 'to_sub_store_code']);
        });
    }
};
