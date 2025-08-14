<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
class LoanAddress extends Model
{
    protected $table = 'erp_loan_addresses';

    use HasFactory,DefaultGroupCompanyOrg,Deletable;
    protected $guarded = ['id'];

    public $referencingRelationships = [
        'homeLoan' => 'home_loan_id',
    ];

    public function homeLoan()
    {
        return $this->belongsTo(HomeLoan::class, 'home_loan_id');
    }

    public function city()
    {
        return $this->hasOne(LoanDocument::class, 'home_loan_id');
    }

    public static function createUpdateAddress($request, $edit_loanId, $homeLoan){
        $address = $request->Address ?? [];
        if(count($address) > 0){
            $same_as_data = 0;
            if(isset($address['same_as'])){
                $same_as_data = 1;
            }
            $addressData = [];
            if(!isset($address['same_as'])) {
                $addressData = [
                    'p_address1' => $address['p_address1'] ?? null,
                    'p_address2' => $address['p_address2'] ?? null,
                    'p_city' => $address['p_city'] ?? null,
                    'p_state' => $address['p_state'] ?? null,
                    'p_pin' => $address['p_pin'] ?? null,
                    'p_resi_code' => $address['p_resi_code'] ?? null,
                ];
            }

            static::updateOrCreate([
                'home_loan_id' => $edit_loanId
            ],
                array_merge([
                    'home_loan_id' => !empty($homeLoan->id) ? $homeLoan->id : $edit_loanId,
                    'address1' => $address['address1'] ?? null,
                    'address2' => $address['address2'] ?? null,
                    'city' => $address['city'] ?? null,
                    'state' => $address['state'] ?? null,
                    'pin_code' => $address['pin_code'] ?? null,
                    'years_current_addr' => $address['years_current_addr'] ?? null,
                    'residence_phn' => $address['residence_phn'] ?? null,
                    'office_phn' => $address['office_phn'] ?? null,
                    'fax_num' => $address['fax_num'] ?? null,
                    'same_as' => $same_as_data
                ], $addressData)
            );
        }
    }
}
