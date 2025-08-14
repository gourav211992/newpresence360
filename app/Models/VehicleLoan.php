<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\HomeLoan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleLoan extends Model
{
    protected $table = 'erp_vehicle_loans';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(VehicleLoan::class, 'vehicle_id');
    }

    public static function fetchRecord($id){
        return HomeLoan::with([
            'dataVehicle', 
            'bankSecurity',
            'vehicleScheme',
            'financeSecurity',
            'netWorth',
            'guarantorAddress',
            'vehicleDocuments',
            'disbursalLoan',
            'loanApplicationLog',
            'recoveryScheduleLoan',
            'loanDisbursement.loanDisbursementDoc',
            'recoveryLoan.recoveryLoanDoc',
            'loanSettlement'
        ])->find($id);
    }

    public static function deleteHomeLoanAndRelatedRecords($vehicleLoanId)
    {
        DB::beginTransaction();

        try {
            DB::table('erp_vehicle_loans')
                ->where('vehicle_id', $vehicleLoanId)
                ->delete();

            DB::table('erp_vehicle_bank_securities')
                ->where('vehicle_id', $vehicleLoanId)
                ->delete();

            DB::table('erp_loan_vehicle_scheme_costs')
                ->where('vehicle_id', $vehicleLoanId)
                ->delete();

            DB::table('erp_loan_finance_loan_securities')
                ->where('vehicle_id', $vehicleLoanId)
                ->delete();

            DB::table('erp_loan_guarantor_parties')
                ->where('vehicle_id', $vehicleLoanId)
                ->delete();

            DB::table('erp_loan_guarantor_party_addresses')
                ->where('vehicle_id', $vehicleLoanId)
                ->delete();

            DB::table('erp_home_loans')
                ->where('id', $vehicleLoanId)
                ->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete vehicle loan and related records: ' . $e->getMessage());
        }
    }
}
