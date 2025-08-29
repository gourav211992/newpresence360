<?php

namespace App\Models;
use App\Models\Scopes\DefaultGroupCompanyOrgScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PaymentVoucherDetails extends Model
{
    use HasFactory;
    protected $table = 'erp_payment_voucher_details';
    protected $fillable = ['party_type', 'party_id'];

    public function getPartyAttribute()
    {
        if ($this->type === 'vendor') {
            return $this->vendor;
        } else {
            return $this->customer;
        }
    }

    /**
     * Define vendor relationship.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'ledger_id', 'ledger_id')
            ->withoutGlobalScope(DefaultGroupCompanyOrgScope::class);
    }

    /**
     * Define customer relationship.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'ledger_id', 'ledger_id');
    }

    public function voucher()
    {
        return $this->belongsTo(PaymentVoucher::class, 'payment_voucher_id');
    }

    public function partyName()
    {

        return $this->morphTo(__FUNCTION__, 'party_type', 'party_id');
    }
    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id')->where('status', 1);
    }
    public function ledger_group()
    {
        return $this->belongsTo(Group::class, 'ledger_group_id');
    }

    public function invoice()
    {
        return $this->hasMany(VoucherReference::class, 'voucher_details_id');
    }
    public function getOrganizationAttribute()
    {
        return $this->invoice()
            ->with([
                'voucher' => function ($query) {
                    $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                        ->withoutGlobalScope('defaultLocation');
                }
            ])
            ->first()?->voucher?->organization;
    }
}
