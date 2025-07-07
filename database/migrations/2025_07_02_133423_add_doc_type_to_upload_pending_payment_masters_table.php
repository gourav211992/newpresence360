<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
    {
        Schema::table('upload_pending_payment_masters', function (Blueprint $table) {
            $table->string('doc_type')->nullable()->after('id'); // Adjust position as needed
        });
    }

    public function down()
    {
        Schema::table('upload_pending_payment_masters', function (Blueprint $table) {
            $table->dropColumn('doc_type');
        });
    }
};
