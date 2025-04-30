<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('erp_finance_fixed_asset_depreciation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id');
            $table->string('document_number', 255);
            $table->date('document_date')->nullable();
            $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES)->default(ConstantHelper::DOC_NO_TYPE_MANUAL);
            $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS)->nullable()->default(null);
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();

            $table->string('period', 255);
            $table->json('assets'); // Stores asset details as JSON
            $table->decimal('grand_total_current_value', 15, 2)->default(0);
            $table->decimal('grand_total_dep_amount', 15, 2)->default(0);
            $table->decimal('grand_total_after_dep_value', 15, 2)->default(0);





            $table->string('document_status', 50);
            $table->integer('approval_level')->default(1);
            $table->string('revision_number')->nullable();
            $table->date('revision_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('type', 100);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_finance_fixed_asset_depreciation');
    }
};
