<?php

namespace App\Services\Integration;

use App\Models\ERP\ErpConsignee;
use App\Models\Organization;

class ConsigneeService
{
    /**
     * Store or update consignees with address.
     *
     * @param int   $organizationId
     * @param array $consigneesData
     * @return array
     */
    public function storeOrUpdate(int $organizationId, array $consigneesData): array
    {
        $results = [];

        $user = request()->user();
        \DB::transaction(function () use ($organizationId, $user, $consigneesData, &$results) {
            $organization = Organization::find($organizationId);

            foreach ($consigneesData as $consigneeData) {
                // Insert or update consignee
                $consignee = ErpConsignee::updateOrCreate(
                    [
                        'organization_id'  => $organization ? $organization->id : $organizationId,
                        'consignee_code'   => $consigneeData['consignee_code'],
                    ],
                    [
                        'company_id'     => $organization ? $organization->company_id : null,
                        'group_id'       => $organization ? $organization->group_id : null,
                        'is_customer'    => $consigneeData['is_customer'],
                        'is_vendor'      => $consigneeData['is_vendor'],
                        'consignee_name' => $consigneeData['consignee_name'],
                        'email'          => $consigneeData['email'] ?? null,
                        'phone'          => $consigneeData['phone'] ?? null,
                        'mobile'         => $consigneeData['mobile'] ?? null,
                        'created_by'     => $user->id,
                        'updated_by'     => $user->id
                    ]
                );

                // Save address only if something is provided
                if (!empty($consigneeData['address']) || $consigneeData['state_id'] || $consigneeData['city_id']) {
                    $consignee->addresses()->updateOrCreate(
                        [
                            'addressable_id'   => $consignee->id,
                            'addressable_type' => ErpConsignee::class,
                            'address'          => $consigneeData['address'] ?? null,
                        ],
                        [
                            'country_id'        => $consigneeData['country_id'] ?? null,
                            'state_id'          => $consigneeData['state_id'] ?? null,
                            'city_id'           => $consigneeData['city_id'] ?? null,
                            'type'              => 'shipping',
                            'is_shipping'       => 1,
                            'pincode'          =>  $consigneeData['pincode'] ?? null,
                            'line_1'            => $consigneeData['address'] ?? null,
                            'name'              => $consigneeData['consignee_name'],
                            'email'             => $consigneeData['email'] ?? null,
                            'mobile'            => $consigneeData['mobile'] ?? null,
                        ]
                    );
                }

                $results[] = $consignee->toArray();

            }
        });

        return $results;
    }
}
