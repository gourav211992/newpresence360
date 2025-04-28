<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumberPattern extends Model
{
    protected $table = 'erp_number_patterns';

    use HasFactory;
    protected $fillable = [
        'book_id',
        'company_id',
        'organization_id',
        'series_numbering',
        'reset_pattern',
        'prefix',
        'starting_no',
        'suffix',
        'current_no'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public static function incrementIndex($org_id, $book_id){
        $pattern = self::where('organization_id', $org_id)
                     ->where('book_id', $book_id)
                     ->orderBy('id', 'DESC')
                     ->first();

        if ($pattern && $pattern->series_numbering == 'Auto') {
            return $pattern->increment('current_no');
        } else {
            return false;
        }
    }
}
