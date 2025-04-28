<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanAppraisalScoring extends Model
{
    use HasFactory;

    protected $table = 'erp_loan_appraisal_credit_scoring';

    protected $fillable = [
        'loan_id',
        'loan_appraisal_id',
        'loan_type',
        'credit_data',
        'document_completeness',
        'basic_eligibility',
        'collateral_credit_history',
        'remarks',
        'financial_analysis',
        'collateral_1',
        'collateral_2',
        'compliance_and_risk',
        'community'
    ];

    protected $casts = [
        'credit_data' => 'array',
        'document_completeness' => 'array',
        'basic_eligibility' => 'array',
        'collateral_credit_history' => 'array',
    ];

    /**
     * Relationship with Loan
     */
    public function loan()
    {
        return $this->belongsTo(HomeLoan::class, 'loan_id');
    }

    /**
     * Relationship with Loan Appraisal
     */
    public function loanAppraisal()
    {
        return $this->belongsTo(ErpLoanAppraisal::class, 'loan_appraisal_id');
    }

    /**
     * Create or update credit scoring record
     */
    public static function createOrUpdate(array $data)
    {
        return self::updateOrCreate(
            [
                'loan_id' => $data['loan_id'] ?? null,
                'loan_appraisal_id' => $data['loan_appraisal_id'] ?? null,
            ],
            [
                'loan_type' => $data['loan_type'] ?? null,
                'credit_data' => $data['credit_data'] ?? null,
                'document_completeness' => isset($data['document_completeness']) ? $data['document_completeness'] : null,
                'basic_eligibility' => isset($data['basic_eligibility']) ? $data['basic_eligibility'] : null,
                'collateral_credit_history' => isset($data['collateral_credit_history']) ? $data['collateral_credit_history'] : null,
                'financial_analysis' => isset($data['financial_analysis']) ? $data['financial_analysis'] : null,
                'collateral_1' => isset($data['collateral_1']) ? $data['collateral_1'] : null,
                'collateral_2' => isset($data['collateral_2']) ? $data['collateral_2'] : null,
                'compliance_and_risk' => isset($data['compliance_and_risk']) ? $data['compliance_and_risk'] : null,
                'community' => isset($data['community']) ? $data['community'] : null,
                'remarks' => $data['remarks'] ?? null,
            ]
        );
    }

}
