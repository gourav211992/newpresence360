<?php

use App\Helpers\ConstantHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function __construct()
    {
        $this->connection = 'mysql_master';
    }
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('erp_services', function (Blueprint $table) {
            $table->enum('type', ConstantHelper::ERP_SERVICE_TYPES) -> default(ConstantHelper::ERP_TRANSACTION_SERVICE_TYPE);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('erp_services', function (Blueprint $table) {
            $table->dropColumn(['type']);
        });
    }
};
