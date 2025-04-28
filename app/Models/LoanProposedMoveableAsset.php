<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProposedMoveableAsset extends Model
{
    protected $table = 'erp_loan_proposed_moveable_assets';

    use HasFactory;
    protected $guarded = ['id'];

    public function loanProposedLoan()
    {
        return $this->belongsTo(LoanProposedLoan::class, 'loan_proposed_loan_id');
    }
}
