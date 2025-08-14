<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'shifts';
    protected $appends = ['label'];
    
    public function getLabelAttribute()
    {
        $start = date('h:i A', strtotime($this->start_time));
        $end = date('h:i A', strtotime($this->end_time));
        $time = $start . ' - ' . $end;
        return $this->name . ' (' . $time . ')';
    }
}   
