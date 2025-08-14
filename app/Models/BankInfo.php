<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;

class BankInfo extends Model
{
    use HasFactory,Deletable,SoftDeletes;

    protected $table = 'erp_bank_infos';

    protected $fillable = [
        'bank_name',
        'beneficiary_name',
        'account_number',
        're_enter_account_number',
        'ifsc_code',
        'primary',
        'cancel_cheque_status',
        'cancel_cheque',
        'morphable_id',
        'morphable_type',
        'status',
    ];

    protected $casts = [
        'cancel_cheque' => 'array',
    ];

    public function morphable()
    {
        return $this->morphTo();
    }

    public function getCancelChequeUrlsAttribute()
    {
        return $this->generateFileUrls($this->cancel_cheque);
    }

    protected function generateFileUrls($filePaths)
    {
        // Convert to array if it's not already
        $filePaths = is_array($filePaths) ? $filePaths : [$filePaths];

        // Generate URLs for the file paths
        return array_map(function ($filePath) {
            // Remove any escape characters
            $filePath = str_replace('\\', '/', $filePath);
            return Storage::url($filePath);
        }, $filePaths);
    }
}
