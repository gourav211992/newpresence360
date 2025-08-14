<?php

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
        Schema::table('erp_user_signatures', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->after('id'); // Add employee_id column after the 'id' column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_user_signatures', function (Blueprint $table) {
            $table->dropColumn('employee_id'); // Remove the employee_id column
        });
    }
};
