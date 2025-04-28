<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    use HasFactory;

    protected $table = 'erp_approval_workflows';
    protected $fillable = [
        'book_id',
        'company_id',
        'organization_id',
        'user_id',
        'book_level_id',
        "user_type",
    ];

    public function legal()
    {
        return $this->belongsTo(Legal::class, 'book_id', ownerKey: 'series');
    }

    public function allLegal()
    {
        return $this->belongsTo(Legal::class, 'book_id', 'series');  // Fetch all records, including drafts
    }

    public function level()
    {
        return $this->belongsTo(BookLevel::class, 'book_level_id');
    }

    public function bookLevel()
    {
        return $this->belongsTo(BookLevel::class, 'book_level_id');
    }
    public function user()
    {
        // if ($this->user_type === 'employee') {
        //     return $this->belongsTo(Employee::class, 'user_id');
        // }

        // return $this->belongsTo(User::class, 'user_id');
        return $this -> belongsTo(AuthUser::class, 'user_id');
    }

}
