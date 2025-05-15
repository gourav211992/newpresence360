<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookDynamicField extends Model
{
    use HasFactory;

    protected $table = 'erp_book_dynamic_fields';

    protected $fillable = [
        'book_id',
        'dynamic_field_id'
    ];

    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function dynamic_field()
    {
        return $this -> belongsTo(DynamicField::class, 'dynamic_field_id');
    }
}
