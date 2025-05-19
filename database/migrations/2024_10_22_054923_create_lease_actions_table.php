<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaseActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erp_land_leases_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable(); // Foreign key for erp_land_leases, can be null
            $table->text('comment')->nullable();
            $table->date('action_date')->nullable(); // To store the selected date
            $table->json('attachments')->nullable(); // Store multiple attachments as JSON
            $table->enum('status', ['renew', 'close', 'terminate', 'reminder'])->default('reminder'); // Status column
            $table->timestamps();

            // Foreign key constraint (no need for onDelete cascade if nullable)
            $table->foreign('source_id')->references('id')->on('erp_land_leases')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('erp_land_leases_actions');
    }
}
