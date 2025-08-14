<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermLoanConstitution extends Model
{
    protected $table = 'erp_term_loan_constitutions';

    use HasFactory;
    use SoftDeletes;
    protected $guarded = ['id'];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'term_loan_id');
    }

    public function Promoters()
    {
        return $this->hasMany(TermLoanConstitutionPromoter::class, 'term_loan_constitution_id');
    }

    public function Partner()
    {
        return $this->hasMany(TermLoanConstitutionPartnerDetail::class, 'term_loan_constitution_id');
    }
}
