<?php

namespace App\Models\JobOrder;

use App\Models\TermsAndCondition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoTerm extends Model
{
    use HasFactory;

    protected $table = 'erp_jo_terms';

    protected $fillable = [
        'jo_id',
        'term_id',
        'term_code',
        'remarks'
    ];

    public function termAndCondition()
    {
        return $this->belongsTo(TermsAndCondition::class, 'term_id');
    }
    
}
