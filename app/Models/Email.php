<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $table = 'erp_emails';

    use HasFactory;

    protected $fillable = [
        'user_id', 
        'subject', 
        'body', 
        'parent_id',
        'legal_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(AuthUser::class, 'user_id', 'id');
    }

    public function recipients()
    {
        return $this->hasMany(EmailRecipient::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function parent()
    {
        return $this->belongsTo(Email::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Email::class, 'parent_id')->with('user', 'attachments','employee')->orderBy('created_at', 'desc'); // Corrected relationship names;
    }
}
