<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateErpIssueTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_issue_types', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Name field
            $table->string('status'); // Status field
            $table->unsignedBigInteger('group_id'); // Foreign key for group_id
            $table->unsignedBigInteger('company_id'); // Foreign key for company_id
            $table->unsignedBigInteger('organization_id'); // Foreign key for organization_id
            $table->timestamps(); // Created at and updated at timestamps

            // Optional: Add foreign key constraints if needed
            // $table->foreign('group_id')->references('id')->on('your_groups_table')->onDelete('cascade');
            // $table->foreign('company_id')->references('id')->on('your_companies_table')->onDelete('cascade');
            // $table->foreign('organization_id')->references('id')->on('your_organizations_table')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_issue_types');
    }
}

