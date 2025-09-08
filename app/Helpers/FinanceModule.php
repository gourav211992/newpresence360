<?php

namespace App\Helpers;

use App\Models\FixedAssetRegistration;
use App\Models\FixedAssetSub;

class FinanceModule
{
    public static function getFixedAssetRDV($uid = null, $asset_code = null, $sub_asset_code = null, $asset_name = null)
    {
        try {
            $assetSub = null;
            $parentAsset = null;

            // 1 Case: UID provided (using id of FixedAssetSub)
            if (!empty($uid)) {
                $assetSub = FixedAssetSub::where('uid',$uid)->first();
               

                if (!$assetSub) {
                    return [
                        'success' => false,
                        'message' => 'Asset not found with the provided UID',
                        'data'    => null
                    ];
                }

                $parentAsset = $assetSub->asset; // relation from sub to parent
            }

            // 2 Case: asset_code + sub_asset_code provided
            elseif (!empty($sub_asset_code)) {
                $assetSub = FixedAssetSub::where('sub_asset_code', $sub_asset_code)->first();

                if (!$assetSub) {
                    return [
                        'success' => false,
                        'message' => 'Asset not found with the provided sub_asset_code',
                        'data'    => null
                    ];
                }

                $parentAsset = $assetSub->asset;
            }

            // 3️ Case: only asset_code provided
            elseif (!empty($asset_code) && !empty($asset_name)) {
                $parentAsset = FixedAssetRegistration::where('asset_code', $asset_code)->where('asset_name',$asset_name)->first();

                if (!$parentAsset) {
                    return [
                        'success' => false,
                        'message' => 'Asset not found with the provided asset_code & asset_name',
                        'data'    => null
                    ];
                }
            }

            else {
                return [
                    'success' => false,
                    'message' => 'Either UID or asset_code (with optional sub_asset_code) is required',
                    'data'    => null
                ];
            }

            if (!$parentAsset) {
                return [
                    'success' => false,
                    'message' => 'Parent asset not found',
                    'data'    => null
                ];
            }

            $rdvValue = null;
            if (!empty($parentAsset->capitalize_date) && !empty($parentAsset->current_value)) {
                $rdvValue = \App\Http\Controllers\FixedAsset\ITDepreciationController::getIncomeTaxRDV(
                    $parentAsset->capitalize_date,
                    $parentAsset->depreciation_percentage ?? 0,
                    $parentAsset->current_value
                );
            }

            return [
                'success' => true,
                'message' => 'RDV calculated successfully',
                'rdv'            => $rdvValue,
                'data'    => [
                    'rdv'            => $rdvValue,
                    'asset_code'     => $asset_code ?? ($parentAsset->asset_code ?? null),
                    'capitalize_date'=> $parentAsset->capitalize_date ?? null,
                    'base_value'     => $parentAsset->current_value ?? null,
                ]
            ];

        } catch (\Exception $e) {
            \Log::error('Error in getFixedAssetRDV: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while calculating RDV',
                'data'    => null,
                'error'   => $e->getMessage()
            ];
        }
    }
}
