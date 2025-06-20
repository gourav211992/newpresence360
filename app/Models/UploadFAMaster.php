<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadFAMaster extends Model
{
    use HasFactory;
    protected $table = 'upload_fa_masters';
    protected $guarded = ['id'];
}
