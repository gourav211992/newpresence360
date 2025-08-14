<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpFeedback extends Model
{
    use HasFactory;
     protected $fillable = [
        'customer_id',
        'question_id',
        'customer_code',
        'organization_id',
        'feedback',
    ];

    public function question()
    {
        return $this->belongsTo(ErpQuestion::class,'question_id','id');
    }
}
