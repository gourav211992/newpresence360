<?php
namespace App\Models;

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

}
