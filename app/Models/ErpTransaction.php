<?php
namespace App\Models;

use App\Helpers\ConstantHelper;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Database\Eloquent\Model;

class ErpTransaction extends Model
{
    use DefaultGroupCompanyOrg;
    protected $table = 'erp_transactions'; // View name in the database

    protected $primaryKey = 'document_id';
    public $incrementing = false; 
    public $timestamps = false; 

    protected $guarded = []; 
    // Define relationships if necessary
    public function book()
    {
        return $this -> belongsTo(Book::class, 'book_id');
    }
    public function bookLevel()
    {
        return $this -> belongsTo(BookLevel::class,'book_id');
    }
    public function documentApproval()
    {
        return $this->hasMany(DocumentApproval::class, 'document_id', 'document_id')
            ->where('document_type', $this->document_type)
            ->where('revision_number', $this->revision_number);
    }
    public function getDocumentStatusAttribute()
    {
        if ($this->attributes['document_status'] == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            return ConstantHelper::APPROVED;
        }
        return $this->attributes['document_status'];
    }
    public function getDisplayStatusAttribute()
    {
        $status = str_replace('_', ' ', $this->document_status);
        return ucwords($status);
    }
}
