<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailRecipient extends Model
{
    protected $table = 'erp_email_recipients';

    use HasFactory;

    protected $fillable = [
        'email_id', 
        'user_id', 
        'type'
    ];

    public function email()
    {
        return $this->belongsTo(Email::class);
    }

    public function user()
    {
        return $this->belongsTo(AuthUser::class);
    }
}
