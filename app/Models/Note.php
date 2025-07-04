<?php

namespace App\Models;
use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory,SoftDeletes;
    protected $table ='erp_notes';
    protected $fillable = [
        'remark',
        'vendor_id',
        'noteable_id',
        'noteable_type',
        'created_by_type',
        'created_by', 
    ];


    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($note) {
            if (Auth::check()) {
                $note->created_by = Auth::id();
            } 
        });
    }
    
    public function noteable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdByEmployee()
    {
        return $this->belongsTo(Employee::class,'created_by','id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

}
