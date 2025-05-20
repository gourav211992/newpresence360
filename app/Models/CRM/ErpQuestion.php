<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpQuestion extends Model
{
    use HasFactory;

    public function feedback()
    {
        return $this->hasOne(ErpFeedback::class,'question_id','id');
    }
}
