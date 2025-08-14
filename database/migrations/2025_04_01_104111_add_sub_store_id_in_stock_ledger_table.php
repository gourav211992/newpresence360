<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::dropIfExists('stock_ledger_details');
        
        Schema::table('stock_ledger', function (Blueprint $table) {
            if(!Schema::hasColumn('stock_ledger', 'lot_number')) {
                $table->string('lot_number',191)->nullable()->after('document_number');
            }
            if(!Schema::hasColumn('stock_ledger', 'sub_store_id')) {
                $table->unsignedBigInteger('sub_store_id')->after('store_id')->nullable();

            }
            if(!Schema::hasColumn('stock_ledger', 'sub_store')) {
                $table->string('sub_store')->after('store')->nullable();

            }
            if(!Schema::hasColumn('stock_ledger', 'stock_type')) {
                $table->string('stock_type')->after('bin')->default('R')->comment('R - Regular, W - WIP');
            }
        });

        Schema::create('stock_ledger_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_ledger_id')->index();
            $table->unsignedBigInteger('rack_id')->nullable();
            $table->unsignedBigInteger('shelf_id')->nullable();
            $table->unsignedBigInteger('bin_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->string('rack', 191)->nullable();
            $table->string('shelf', 191)->nullable();
            $table->string('bin', 191)->nullable();
            $table->string('zone', 191)->nullable();
            $table->decimal('quantity', 15,2)->default('0.00');
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledger_details');

        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->dropColumn(['lot_number','stock_type','sub_store_id']);
        });
    }
};
