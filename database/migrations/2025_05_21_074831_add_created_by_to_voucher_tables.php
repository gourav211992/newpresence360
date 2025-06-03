<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('erp_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->after('document_status')->nullable();
        });

        Schema::table('erp_vouchers_history', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->after('document_status')->nullable();
        });

        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->after('document_status')->nullable();
        });

        Schema::table('erp_payment_vouchers_history', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->after('document_status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('erp_vouchers', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });

        Schema::table('erp_vouchers_history', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });

        Schema::table('erp_payment_vouchers', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });

        Schema::table('erp_payment_vouchers_history', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};
