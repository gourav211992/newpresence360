<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('erp_ledgers_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('book_id')->nullable();
            $table->unsignedBigInteger('doc_no')->nullable();
            $table->string('prefix')->nullable();
            $table->string('code');
            $table->string('name');
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->string('ledger_group_id');
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('document_status')->nullable();
            $table->integer('revision_number')->default(0);
            $table->integer('approval_level')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('tax_type')->nullable();
            $table->decimal('tax_percentage', 10, 2)->nullable();
            $table->string('tds_section')->nullable();
            $table->decimal('tds_percentage', 10, 2)->nullable();
            $table->string('tcs_section')->nullable();
            $table->decimal('tcs_percentage', 10, 2)->nullable();
            $table->string('ledger_code_type', 250);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_ledgers_history');
    }
};
