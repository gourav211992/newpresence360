<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper;

class Organization extends Model
{
    use HasFactory;

    protected $appends = ['full_logo_path'];

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function UserOrganizationMapping()
    {
        return $this->hasMany(UserOrganizationMapping::class);
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public static function getOrganization(){
        $user = Helper::getAuthenticatedUser();
        return self::where('id', $user->organization_id)->first();
    }

    public function currency() {
        return $this->belongsTo(Currency::class,'currency_id');
    }

    // public function getAliasAttribute()
    // {
    //     return $this -> attributes['alias'] ?? $this -> attributes['name'];
    // }
    public function categories()
    {
        return $this->hasMany(Category::class)->whereNull('parent_id');
    }
    public function ledgerGroups()
    {
        return $this->hasMany(Group::class);
    }
    public function books()
    {
        return $this->hasMany(Book::class);
    }
    public function items()
    {
        return $this->hasMany(Item::class, 'organization_id');
    }
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function compliances()
    {
        // dd($this->morphone(Compliance::class,'morphable'));
        return $this->morphone(Compliance::class, 'morphable');
    }

    public function getFullLogoPathAttribute(){
        return $this->logo_path
            ? env('ADMIN_URL').'/storage/'.$this->logo_path
            : null;
    }

}
