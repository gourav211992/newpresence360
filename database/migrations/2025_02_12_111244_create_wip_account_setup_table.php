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
    public function up()
    {
        Schema::create('erp_wip_accounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_id')->nullable();
            $table->integer('company_id')->nullable();
            $table->unsignedInteger('organization_id')->nullable();
            $table->json('book_id')->nullable();
            $table->unsignedBigInteger('ledger_id')->nullable()->index();
            $table->unsignedBigInteger('ledger_group_id')->nullable()->index();
            $table->foreign('ledger_id')->references('id')->on('erp_ledgers')->onDelete('cascade');
            $table->foreign('ledger_group_id')->references('id')->on('erp_groups')->onDelete('cascade');
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_wip_accounts');
    }
};
