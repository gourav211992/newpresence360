<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermLoanConstitutionPromoter extends Model
{
    protected $table = 'erp_term_loan_constitution_promoters';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function termLoanConstitution()
    {
        return $this->belongsTo(TermLoanConstitution::class, 'term_loan_constitution_id');
    }
}
