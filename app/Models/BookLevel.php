<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookLevel extends Model
{
    use HasFactory;
    protected $table = 'erp_book_levels';
    protected $fillable = [
        'book_id',
        'min_value',
        'max_value',
        'rights',
        'level',
        'company_id',
        'organization_id'
    ];


    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function approvers()
    {
        return $this->hasMany(ApprovalWorkflow::class, 'book_level_id');
    }
}
