<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up()
        {
            Schema::create('erp_user_signatures', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Name
                $table->string('designation'); // Designation
                $table->unsignedBigInteger('organization_id'); // Organization ID
                $table->unsignedBigInteger('group_id'); // Group ID
                $table->unsignedBigInteger('company_id'); // Company ID
                $table->string('sign_upload_file'); // Sign Upload File
                $table->unsignedBigInteger('created_by'); // Created By
                $table->string('type'); // Type
                $table->timestamps();
                $table->softDeletes();
            });
        }

        public function down()
        {
            Schema::dropIfExists('erp_user_signatures');
        }
    };
