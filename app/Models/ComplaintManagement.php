<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintManagement extends Model
{
    use HasFactory,SoftDeletes, DefaultGroupCompanyOrg;
    protected $table = 'erp_complaint_management';
    protected $fillable = [
        'organization_id',
        'group_id',
        'company_id',
        'book_id',
        'document_number',
        'document_date',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'document_status',
        'approval_level',
        'revision_number',
        'revision_date',
        'userable_id',
        'type',
        'userable_type',
        'complaint_id',
        'status',
        'user_type_id',
        'notes',
        'description',
        'created_by',
        'party_name'
    ];
//    public function user()
//    {
//        return $this->belongsTo(User::class);
//    }
    public function userable()
    {
        return $this->belongsTo(AuthUser::class);
    }
    public function getUserNameAttribute()
    {
        if ($this->userable) {
            return $this->userable->name;
        }
        return 'Unknown'; // Fallback value
    }
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
