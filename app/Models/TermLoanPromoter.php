<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class TermLoanPromoter extends Model
{
    protected $table = 'erp_term_loan_promoters';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'term_loan_id');
    }

    public static function fetchRecord($id){
        return HomeLoan::with([
            'termLoanAddress',
            'termLoanPromoter',
            'constitutions.Promoters',
            'constitutions.Partner',
            'meanFinance',
            'termLoanNetWorth.termLoanNetWorthExperience',
            'termLoanNetWorth.termLoanNetWorthProperty',
            'termLoanNetWorth.termLoanNetWorthLiability',
            'termLoanDocument',
            'disbursalLoan',
            'loanApplicationLog',
            'recoveryScheduleLoan',
            'loanDisbursement.loanDisbursementDoc',
            'recoveryLoan.recoveryLoanDoc',
            'loanSettlement'

        ])->find($id);
    }

    public static function deleteHomeLoanAndRelatedRecords($termLoanId)
    {
        DB::beginTransaction();

        try {
            DB::table('erp_term_loan_addresses')
                ->where('term_loan_id', $termLoanId)
                ->delete();

            DB::table('erp_term_loan_promoters')
                ->where('term_loan_id', $termLoanId)
                ->delete();

            DB::table('erp_term_loan_finance_means')
                ->where('term_loan_id', $termLoanId)
                ->delete();

            DB::table('erp_term_loan_documents')
                ->where('term_loan_id', $termLoanId)
                ->delete();

            DB::table('erp_term_loan_constitution_partner_details')
                ->whereIn('term_loan_constitution_id', function ($query) use ($termLoanId) {
                    $query->select('id')
                        ->from('erp_term_loan_constitutions')
                        ->where('term_loan_id', $termLoanId);
                })
                ->delete();

            DB::table('erp_term_loan_constitution_promoters')
                ->whereIn('term_loan_constitution_id', function ($query) use ($termLoanId) {
                    $query->select('id')
                        ->from('erp_term_loan_constitutions')
                        ->where('term_loan_id', $termLoanId);
                })
                ->delete();

            DB::table('erp_term_loan_constitutions')
                ->where('term_loan_id', $termLoanId)
                ->delete();


            DB::table('erp_term_loan_net_worth_experiences')
                ->whereIn('term_loan_net_worth_id', function ($query) use ($termLoanId) {
                    $query->select('id')
                        ->from('erp_term_loan_net_worths')
                        ->where('term_loan_id', $termLoanId);
                })
                ->delete();

            DB::table('erp_term_loan_net_worth_liabilities')
                ->whereIn('term_loan_net_worth_id', function ($query) use ($termLoanId) {
                    $query->select('id')
                        ->from('erp_term_loan_net_worths')
                        ->where('term_loan_id', $termLoanId);
                })
                ->delete();

            DB::table('erp_term_loan_net_worth_properties')
                ->whereIn('term_loan_net_worth_id', function ($query) use ($termLoanId) {
                    $query->select('id')
                        ->from('erp_term_loan_net_worths')
                        ->where('term_loan_id', $termLoanId);
                })
                ->delete();

            DB::table('erp_term_loan_net_worths')
                ->where('term_loan_id', $termLoanId)
                ->delete();

            DB::table('erp_home_loans')
                ->where('id', $termLoanId)
                ->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete term loan and related records: ' . $e->getMessage());
        }
    }
}
