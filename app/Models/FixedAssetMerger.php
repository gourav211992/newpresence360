<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Helpers\Helper;
use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;
use App\Helpers\ServiceParametersHelper;
use Carbon\Carbon;


class FixedAssetMerger extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_merger';

    protected $guarded = ['id'];
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    public function asset()
    {
        return $this->belongsTo(FixedAssetRegistration::class, 'asset_id');
    }
    public function location()
    {
        return $this->belongsTo(ErpStore::class, 'location_id');
    }
    public function cost_center()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }
    public function subAsset()
    {
        return $this->belongsTo(FixedAssetSub::class, 'sub_asset_id');
    }
    public function category()
    {
        return $this->belongsTo(ErpAssetCategory::class, 'category_id');
    }
    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }
    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class, 'ledger_group_id');
    }
    // public function getAssetsAttribute()
    // {
    //     $assetIds = json_decode($this->attributes['assets'], true) ?? [];

    //     return FixedAssetRegistration::whereIn('id', $assetIds)->get();
    // }
    public static function makeRegistration($id)
    {
        $request = FixedAssetMerger::find($id);
        $book = Book::find($request->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }
        


        $exitingReg = FixedAssetRegistration::where('reference_series', $request->book_id)->where('reference_doc_id', $request->id)->first();
        if ($exitingReg) {
            return array(
                'message' => 'Registration already posted',
                'status' => false
            );
        }
        $exitingVoucher = FixedAssetRegistration::where('document_number', $request->document_number)->where('book_id', $request->book_id)->first();
        if ($exitingVoucher) {
            return array(
                'message' => 'Registration already posted with same Doc No# ' . $request->document_number,
                'status' => false
            );
        }
        $exitingVouchers = Voucher::where('voucher_no', $request->document_number)->where('book_id', $request->book_id)->first();
        if ($exitingVouchers) {
            return array(
                'message' => 'Voucher already posted with same Doc No# ' . $request->document_number,
                'status' => false
            );
        }

        $existingAsset = FixedAssetRegistration::where('asset_code', $request->asset_code)->first();

        if ($existingAsset) {
            return array(
                'status' => false,
                'message' => 'Asset Code ' . $existingAsset->asset_code . ' already exists.',
                'data' => []
            );
        }




        // Step 1: Create main asset registration (only once per asset_code)
        $data = [
            'organization_id' => $request->organization_id,
            'group_id' => $request->group_id,
            'company_id' => $request->company_id,
            'created_by' => $request->created_by,
            'type' => $request->type,
            'book_id' => $glPostingBookId,
            'document_number' => $request->document_number,
            'document_date' => $request->document_date,
            'asset_code' => $request->asset_code,
            'asset_name' => $request->asset_name,
            'quantity' => $request->quantity,
            'category_id' => $request->category_id,
            'it_category_id'=>$request->it_category_id??null,
            'reference_doc_id' => $request->id,
            'reference_series' => 'fixed-asset-merger',
            'ledger_id' => $request->ledger_id,
            'ledger_group_id' => $request->ledger_group_id,
            'capitalize_date' => $request->capitalize_date,
            'last_dep_date' => $request->capitalize_date,
            'currency_id' => $request->currency_id,
            'location_id' => $request->location_id,
            'cost_center_id' => $request->cost_center_id,
            'maintenance_schedule' => $request->maintenance_schedule,
            'depreciation_method' => $request->depreciation_method,
            'useful_life' => $request->useful_life,
            'salvage_value' => $request->salvage_value,
            'depreciation_percentage' => $request->depreciation_percentage,
            'depreciation_percentage_year' => $request->depreciation_percentage,
            'total_depreciation' => $request->total_depreciation,
            'dep_type' => $request->dep_type,
            'current_value' => $request->current_value,
            'current_value_after_dep' => $request->current_value,
            'document_status' => 'approved',
            'approval_level' => 1,
            'revision_number' => 0,
            'revision_date' => null,
            'status' => 'active',

        ];

        $asset = FixedAssetRegistration::create($data);
        FixedAssetSub::generateSubAssets($asset->id, $asset->asset_code, $asset->quantity, $asset->current_value, $asset->salvage_value);
        //delete old assets
        foreach (json_decode($request->asset_details) as $item) {
            foreach ($item->sub_asset_id as $sub) {
                $old = FixedAssetSub::find($sub);
                if ($old) {
                    if ($old->last_dep_date != $old->capitalize_date) {
                        $old->expiry_date = Carbon::parse($request->capitalize_date)->subDay()->format('Y-m-d');
                        $old->save();
                    } else {
                        $old->expiry_date = $old->last_dep_date;
                        $old->save();
                    }
                }
            }
        }
        return array(
            'status' => true,
            'message' => "Registration Added",
            'data' => []
        );
    }
}
