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
        Schema::table('loan_application_logs', function (Blueprint $table) {
            // Check if the 'action_type' column exists
            if (Schema::hasColumn('loan_application_logs', 'action_type')) {
                // Modify 'action_type' to string if it exists
                $table->string('action_type', 50)->change();
            } else {
                // Otherwise, create the 'action_type' column as a string
                $table->string('action_type', 50)->nullable()->after('loan_type'); // Adjust position with 'after' if needed
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_application_logs', function (Blueprint $table) {
            // Revert 'action_type' back to enum if needed
            if (Schema::hasColumn('loan_application_logs', 'action_type')) {
                $table->enum('action_type', ['pending', 'submitted', 'approve', 'reject', 'assessment', 'disbursal', 'recovery', 'disbursement', 'recover', 'settlement'])->change();
            }
        });
    }
    
};
