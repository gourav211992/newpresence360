<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_budgets', function (Blueprint $table) {
        $table->id(); // Auto-incrementing ID
        $table->string('series'); // Series field
        $table->string('documentno')->nullable(); // Document number
        $table->string('type')->nullable(); // Type (e.g., Purchase)
        $table->string('unit')->nullable(); // Unit (e.g., IT)
        $table->string('budget')->nullable(); // Budget
        $table->string('period')->nullable(); // Period (e.g., Quarter)
        $table->string('companies')->nullable(); // Companies (JSON field)
        $table->string('branch')->nullable(); // Branch
        $table->string('ledger')->nullable(); // Ledger
        $table->json('details')->nullable(); // Details (JSON field)
        $table->decimal('total_percent', 5, 2)->nullable(); // Percentage
        $table->decimal('total_value', 15, 2)->nullable(); // Value
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
        Schema::dropIfExists('budgets');
    }
}

