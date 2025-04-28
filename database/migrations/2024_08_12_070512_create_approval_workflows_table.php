<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('erp_approval_workflows')) {
            Schema::create('erp_approval_workflows', function (Blueprint $table) {
                $table->id();
                $table->integer('book_id'); // Book reference
                $table->integer('company_id'); // Company reference
                $table->integer('organization_id'); // Organization reference
                $table->json('user_id'); // Changed from integer to JSON
                $table->decimal('min_value', 15, 2)->default(0); // Minimum approval value
                $table->decimal('max_value', 15, 2); // Maximum approval value
                $table->enum('rights', ['anyone', 'all']); // Rights enum
                $table->timestamps(); // Timestamps
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_approval_workflows');
    }
}
