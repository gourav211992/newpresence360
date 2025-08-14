<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalProcessesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('erp_approval_processes')) {
            Schema::create('erp_approval_processes', function (Blueprint $table) {
                $table->id(); // Primary key
                $table->unsignedBigInteger('user_id'); // Foreign key for user
                $table->unsignedBigInteger('voucher_id'); // Foreign key for voucher
                $table->unsignedBigInteger('book_id'); // Foreign key for book
                $table->timestamps(); // Timestamps for created_at and updated_at

            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('erp_approval_processes');
    }
}
