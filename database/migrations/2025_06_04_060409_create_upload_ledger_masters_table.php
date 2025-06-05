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
        Schema::create('upload_ledger_masters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->text('ledger_groups')->nullable();
            $table->string('tax_type')->nullable();
            $table->decimal('tax_percentage', 10, 2)->nullable();
            $table->string('tds_section')->nullable();
            $table->decimal('tds_percentage', 10, 2)->nullable();
            $table->string('tcs_section')->nullable();
            $table->decimal('tcs_percentage', 10, 2)->nullable();

            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->text('import_remarks')->nullable();
            $table->string('import_status')->default('Draft')->nullable();
            
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_ledger_masters');
    }
};
