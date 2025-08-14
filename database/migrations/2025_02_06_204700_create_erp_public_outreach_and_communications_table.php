<?php

use App\Helpers\ConstantHelper;
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
        Schema::create('erp_public_outreach_and_communications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('book_id')->nullable();
            $table->string('document_number', 255);
            $table->date('document_date')->nullable();
            $table->enum('doc_number_type', ConstantHelper::DOC_NO_TYPES)->default(ConstantHelper::DOC_NO_TYPE_MANUAL);
            $table->enum('doc_reset_pattern', ConstantHelper::DOC_RESET_PATTERNS)->nullable()->default(null);
            $table->string('doc_prefix')->nullable();
            $table->string('doc_suffix')->nullable();
            $table->integer('doc_no')->nullable();
            $table->string('document_status', 50);
            $table->integer('approval_level')->default(1);
            $table->string('revision_number')->nullable();
            $table->date('revision_date')->nullable();
            $table->string('type', 100);
            $table->unsignedBigInteger('userable_id');
            $table->string('userable_type');
            $table->unsignedBigInteger('interaction_type_id');
            $table->unsignedBigInteger('user_type_id');
            $table->text('description')->nullable();
            $table->text('outcomes')->nullable();
            $table->foreign('user_type_id', 'fk_user_type_id')
                ->references('id')
                ->on('erp_stakeholder_user_types')
                ->onDelete('cascade');

            $table->foreign('interaction_type_id', 'fk_interaction_type_id')
                ->references('id')
                ->on('erp_interaction_types')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_public_outreach_and_communications');
    }
};
