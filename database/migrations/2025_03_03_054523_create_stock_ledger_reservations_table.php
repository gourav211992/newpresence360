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
        Schema::create('stock_ledger_reservations', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('stock_ledger_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('quantity', 15,2)->nullable();
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->decimal('reserved_qty', 15,2)->default(0.00)->after('issue_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ledger', function (Blueprint $table) {
            $table->dropColumn('reserved_qty');
        });
        
        Schema::dropIfExists('stock_ledger_reservations');
    }
};
