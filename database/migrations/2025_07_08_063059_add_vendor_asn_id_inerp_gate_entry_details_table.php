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
        // Schema::table('erp_gate_entry_details', function (Blueprint $table) {
        //     $table->unsignedBigInteger('vendor_asn_id') -> after('jo_id')->nullable();
        //     $table->unsignedBigInteger('vendor_asn_item_id') -> after('vendor_asn_id')->nullable();
        // });

        // Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
        //     $table->unsignedBigInteger('vendor_asn_id') -> after('jo_id')->nullable();
        //     $table->unsignedBigInteger('vendor_asn_item_id') -> after('vendor_asn_id')->nullable();
        // });

        if (!Schema::hasColumn('erp_gate_entry_details', 'vendor_asn_id')) {
            Schema::table('erp_gate_entry_details', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_asn_id')->nullable()->after('jo_id');
            });
        }

        if (!Schema::hasColumn('erp_gate_entry_details', 'vendor_asn_item_id')) {
            Schema::table('erp_gate_entry_details', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_asn_item_id')->nullable()->after('vendor_asn_id');
            });
        }

        if (!Schema::hasColumn('erp_gate_entry_details_history', 'vendor_asn_id')) {
            Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_asn_id')->nullable()->after('jo_id');
            });
        }

        if (!Schema::hasColumn('erp_gate_entry_details_history', 'vendor_asn_item_id')) {
            Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_asn_item_id')->nullable()->after('vendor_asn_id');
            });
        }

        // Schema::table('erp_mrn_details', function (Blueprint $table) {
        //     $table->unsignedBigInteger('vendor_asn_id') -> after('jo_id')->nullable();
        //     $table->unsignedBigInteger('vendor_asn_item_id') -> after('vendor_asn_id')->nullable();
        // });

        // Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
        //     $table->unsignedBigInteger('vendor_asn_id') -> after('jo_id')->nullable();
        //     $table->unsignedBigInteger('vendor_asn_item_id') -> after('vendor_asn_id')->nullable();
        // });

        // Drop from erp_mrn_details
        if (Schema::hasColumn('erp_mrn_details', 'vendor_asn_item_id')) {
            Schema::table('erp_mrn_details', function (Blueprint $table) {
                $table->dropColumn('vendor_asn_item_id');
            });
        }

        if (Schema::hasColumn('erp_mrn_details', 'vendor_asn_id')) {
            Schema::table('erp_mrn_details', function (Blueprint $table) {
                $table->dropColumn('vendor_asn_id');
            });
        }

        // Drop from erp_mrn_detail_histories
        if (Schema::hasColumn('erp_mrn_detail_histories', 'vendor_asn_item_id')) {
            Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
                $table->dropColumn('vendor_asn_item_id');
            });
        }

        if (Schema::hasColumn('erp_mrn_detail_histories', 'vendor_asn_id')) {
            Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
                $table->dropColumn('vendor_asn_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
        //     $table->dropColumn('vendor_asn_id');
        //     $table->dropColumn('vendor_asn_item_id');
        // });

        // Schema::table('erp_mrn_details', function (Blueprint $table) {
        //     $table->dropColumn('vendor_asn_id');
        //     $table->dropColumn('vendor_asn_item_id');
        // });

        // Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
        //     $table->dropColumn('vendor_asn_id');
        //     $table->dropColumn('vendor_asn_item_id');
        // });

        // Schema::table('erp_gate_entry_details', function (Blueprint $table) {
        //     $table->dropColumn('vendor_asn_id');
        //     $table->dropColumn('vendor_asn_item_id');
        // });

        // Drop from erp_mrn_detail_histories
        Schema::table('erp_mrn_detail_histories', function (Blueprint $table) {
            if (Schema::hasColumn('erp_mrn_detail_histories', 'vendor_asn_id')) {
                $table->dropColumn('vendor_asn_id');
            }
            if (Schema::hasColumn('erp_mrn_detail_histories', 'vendor_asn_item_id')) {
                $table->dropColumn('vendor_asn_item_id');
            }
        });

        // Drop from erp_mrn_details
        Schema::table('erp_mrn_details', function (Blueprint $table) {
            if (Schema::hasColumn('erp_mrn_details', 'vendor_asn_id')) {
                $table->dropColumn('vendor_asn_id');
            }
            if (Schema::hasColumn('erp_mrn_details', 'vendor_asn_item_id')) {
                $table->dropColumn('vendor_asn_item_id');
            }
        });

        // Drop from erp_gate_entry_details_history
        Schema::table('erp_gate_entry_details_history', function (Blueprint $table) {
            if (Schema::hasColumn('erp_gate_entry_details_history', 'vendor_asn_id')) {
                $table->dropColumn('vendor_asn_id');
            }
            if (Schema::hasColumn('erp_gate_entry_details_history', 'vendor_asn_item_id')) {
                $table->dropColumn('vendor_asn_item_id');
            }
        });

        // Drop from erp_gate_entry_details
        Schema::table('erp_gate_entry_details', function (Blueprint $table) {
            if (Schema::hasColumn('erp_gate_entry_details', 'vendor_asn_id')) {
                $table->dropColumn('vendor_asn_id');
            }
            if (Schema::hasColumn('erp_gate_entry_details', 'vendor_asn_item_id')) {
                $table->dropColumn('vendor_asn_item_id');
            }
        });
    }
};
