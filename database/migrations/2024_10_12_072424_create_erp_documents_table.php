<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ConstantHelper;

class CreateErpDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_documents', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('service')->nullable()->index(); // Document name
            $table->string('name')->nullable(); // Document description
            $table->unsignedBigInteger('group_id')->nullable()->index(); // Foreign key for group
            $table->unsignedBigInteger('company_id')->nullable()->index(); // Foreign key for company
            $table->unsignedBigInteger('organization_id')->nullable()->index(); // Foreign key for organization
            $table->enum('status', ConstantHelper::STATUS)->default(ConstantHelper::ACTIVE); // Document status
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_documents'); // Drop the table if it exists
    }
}
