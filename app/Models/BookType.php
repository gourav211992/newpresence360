<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookType extends Model
{
    protected $table = 'erp_book_types';

    use HasFactory;

    protected $fillable = ['name', 'status', 'service_id', 'group_id', 'company_id', 'organization_id'];


    public function service()
    {
        return $this->belongsTo(OrganizationService::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class,'booktype_id');
    }

}
