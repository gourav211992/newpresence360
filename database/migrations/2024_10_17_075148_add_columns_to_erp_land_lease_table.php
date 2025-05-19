<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToErpLandLeaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('erp_land_leases', function (Blueprint $table) {
            // Adding 'attachments' as nullable text (can be used for file paths or JSON data)
            $table->text('attachments')->nullable()->after('created_at');

            // Adding 'installment_amount' as a decimal with default value 0.00
            $table->decimal('installment_amount', 15, 2)->default(0.00)->after('attachments');

            // Adding 'otherextra_charges' as a decimal with default value 0.00
            $table->decimal('otherextra_charges', 15, 2)->default(0.00)->after('installment_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('erp_land_leases', function (Blueprint $table) {
            // Dropping the columns if the migration is rolled back
            $table->dropColumn('attachments');
            $table->dropColumn('installment_amount');
            $table->dropColumn('otherextra_charges');
        });
    }
}
