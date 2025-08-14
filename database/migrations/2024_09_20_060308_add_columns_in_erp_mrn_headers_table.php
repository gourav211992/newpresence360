<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            // Drop the columns
            $table->dropColumn('discount');
            $table->dropColumn('discount_amount');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            // Add the columns back with the desired changes
            $table->decimal('item_discount', 15, 2)->nullable()->after('total_item_amount'); // change 'some_column' to the column after which you want to place this
            $table->decimal('total_discount', 15, 2)->nullable()->after('item_discount');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            // Drop the columns
            $table->dropColumn('discount');
            $table->dropColumn('discount_amount');
        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            // Add the columns back with the desired changes
            $table->decimal('item_discount', 15, 2)->nullable()->after('total_item_amount'); // change 'some_column' to the column after which you want to place this
            $table->decimal('total_discount', 15, 2)->nullable()->after('item_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            // To reverse, drop the new columns and add the old ones back
            $table->dropColumn('item_discount');
            $table->dropColumn('total_discount');

        });

        Schema::table('erp_mrn_header_histories', function (Blueprint $table) {
            // Add the original columns back
            $table->decimal('discount', 15, 2)->nullable()->after('total_item_amount'); // Adjust the 'after' as necessary
            $table->decimal('discount_amount', 15, 2)->nullable()->after('discount');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            // To reverse, drop the new columns and add the old ones back
            $table->dropColumn('item_discount');
            $table->dropColumn('total_discount');
        });

        Schema::table('erp_mrn_headers', function (Blueprint $table) {
            // Add the original columns back
            $table->decimal('discount', 15, 2)->nullable()->after('total_item_amount'); // Adjust the 'after' as necessary
            $table->decimal('discount_amount', 15, 2)->nullable()->after('discount');
        });
    }
};

