<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = 'erp_attachments';

    use HasFactory;

    protected $fillable = [
        'email_id', 
        'file_path', 
        'file_name'
    ];

    public function email()
    {
        return $this->belongsTo(Email::class);
    }
}
