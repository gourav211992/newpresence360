<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Deletable;

class Contact extends Model
{
    use HasFactory,SoftDeletes,Deletable;
    protected $table = 'erp_contacts';

    protected $fillable = [
        'primary',
        'salutation',
        'name',
        'email',
        'mobile',
        'phone',
        'contactable_id',
        'contactable_type',
        'status',
    ];


    public function contactable()
    {
        return $this->morphTo();
    }
}
