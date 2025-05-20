<?php

use App\Helpers\ConstantHelper;
use App\Helpers\ServiceParametersHelper;
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
        Schema::table('erp_material_issue_header', function (Blueprint $table) {
            $table->string('issue_type')->default("Location Transfer")->after('book_code');
        });
        Schema::table('erp_material_issue_header_history', function (Blueprint $table) {
            $table->string('issue_type')->default("Location Transfer")->after('book_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_material_issue_header', function (Blueprint $table) {
            $table->dropColumn(['issue_type']);
        });
        Schema::table('erp_material_issue_header_history', function (Blueprint $table) {
            $table->dropColumn(['issue_type']);
        });
    }
};
