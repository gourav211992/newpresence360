<?php

namespace App\Models;

use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StakeholderInteraction extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;
    protected $table = 'erp_stakeholder_interactions';
    protected $connection = 'mysql';
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
//        'user_id',
        'interaction_type_id',
        'interaction_date',
        'notes',
        'followup_actions',
        'user_type_id',
        'userable_type',
        'created_by',
        'party_name'
    ];
//    public function user()
//    {
//        return $this->belongsTo(User::class);
//    }
//
    public function interactionType()
    {
        return $this->belongsTo(ErpInteractionType::class);
    }
    public function userable()
    {
        return $this->belongsTo(AuthUser::class);
    }
    public function getUserNameAttribute()
    {
//        dd($this->userable);
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
