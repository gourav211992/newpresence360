<?php

namespace App\Imports;

use App\Helpers\Helper;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ConstantHelper;
use App\Models\Ledger;
use App\Models\UploadLedgerMaster;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Services\LedgerImportExportService;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Exception;
class LedgerImport implements ToModel, WithHeadingRow, WithChunkReading, WithStartRow
{
    protected $successfulItems = [];
    protected $failedItems = [];
    protected $service;
    protected $user;

    public function chunkSize(): int
    {
        return 500;
    }

    public function __construct(LedgerImportExportService $service, $user)
    {
        $this->service = $service;
        $this->user = $user;
    }
    public function startRow(): int
    {
        return 3;
    }
    public function headingRow(): int
    {
        return 1;
    }


    public function onSuccess($row)
    {
        $this->successfulItems[] = [
            'code' => $row->code,
            'name' => $row->name,
            'groups' =>  $row->ledger_groups,
            'status' => $row->status,
            'tds_section' => $row->tds_section,
            'tds_percentage' => $row->tds_percentage,
            'tcs_section' => $row->tcs_section,
            'tcs_percentage' => $row->tcs_percentage,
            'tax_type' => $row->tax_type,
            'tax_percentage' => $row->tax_percentage,
            'status_check' => 'success',
            'remarks' => '',
        ];
    }

    public function onFailure($uploadedItem)
    {
        $this->failedItems[] = [
            'code' => $uploadedItem->code ?? null,
            'name' => $uploadedItem->name ?? null,
            'groups' => $uploadedItem->ledger_groups ?? null,
            'status' => $uploadedItem->status,
            'tds_section' => $uploadedItem->tds_section ?? null,
            'tds_percentage' => $uploadedItem->tds_percentage ?? null,
            'tcs_section' => $uploadedItem->tcs_section ?? null,
            'tcs_percentage' => $uploadedItem->tcs_percentage ?? null,
            'tax_type' => $uploadedItem->tax_type ?? null,
            'tax_percentage' => $uploadedItem->tax_percentage ?? null,
            'status_check' => 'failed',
            'remarks' => $uploadedItem->import_remarks,
        ];
    }

    public function getSuccessfulItems()
    {
        return $this->successfulItems;
    }

    public function getFailedItems()
    {
        return $this->failedItems;
    }
    public function model(array $row)
    {
        $organization = $this->user->organization;
        if (
            strtolower(trim($row['code'])) === 'code of ledger' ||
            strtolower(trim($row['name'])) === 'name of ledger'
        ) {
            // Skip this row
            return null;
        }
        if (collect($row)->filter()->isEmpty()) {
            return null;
        }
        $uploadedItem = null;
        $validatedData = [];
        $errors = [];
        try {
            $parentUrl = ConstantHelper::LEDGERS_SERVICE_ALIAS;
            $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
            if ($services && isset($services['services']) && $services['services']->isNotEmpty()) {
                $firstService = $services['services']->first();
                $serviceId = $firstService->service_id;
                $policyData = Helper::getPolicyByServiceId($serviceId);
                if ($policyData && isset($policyData['policyLevelData'])) {
                    $policyLevelData = $policyData['policyLevelData'];
                    $validatedData['group_id'] = $policyLevelData['group_id'] ?? $organization->group_id;
                    $validatedData['company_id'] = $policyLevelData['company_id'] ?? null;
                    $validatedData['organization_id'] = $policyLevelData['organization_id'] ?? null;
                } else {
                    $validatedData['group_id'] = $organization->group_id;
                    $validatedData['company_id'] = null;
                    $validatedData['organization_id'] = null;
                }
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = null;
                $validatedData['organization_id'] = null;
            }


            // Validate mandatory fields
            $code = $row['code'] ?? null;
            $name = $row['name'] ?? null;
            $group = $row['group'] ?? null;
            $status = 'Success';

            try {
                $this->service->checkRequiredFields($code, $name, $group);
            } catch (Exception $e) {
                Log::error("Error Required fields missing: " . $e->getMessage());
                $status = 'Failed';
            }

            try {
                // Check uniqueness
                $this->service->checkLedgerUniqueness('code', $code,$this->user);
                // Validate unique name
                $this->service->checkLedgerUniqueness('name', $name,$this->user);
            } catch (Exception $e) {
                Log::error("Error Fields already Exists: " . $e->getMessage());
                $status = 'Failed';
            }

            // Process group IDs
            $groupData = $this->service->processGroupData($group);
            $groupIds = $groupData['groupIds'];
            $groupLower = $groupData['groupLower'];

            // Clean unnecessary fields
            if (!in_array('tds', $groupLower)) {
                $row['tds_section'] = null;
                $row['tds_percentage'] = null;
            } else {
                $row['tds_section'] = $this->service->getTdsSectionKeyFromLabel($row['tds_section'] ?? '') ?? null;
            }
            if (!in_array('tcs', $groupLower)) {
                $row['tcs_section'] = null;
                $row['tcs_percentage'] = null;
            } else {
                $row['tcs_section'] = $this->service->getTcsSectionKeyFromLabel($row['tcs_section'] ?? '') ?? null;
            }
            if (!in_array('gst', $groupLower)) {
                $row['tax_type'] = null;
                $row['tax_percentage'] = null;
            } else {
                $row['tax_type'] = $this->service->getTaxTypeSectionKeyFromLabel($row['tax_type'] ?? '') ?? null;
            }
            $uploadedItem = UploadLedgerMaster::create([
                'code' => $code,
                'name' => $name,
                'ledger_groups' => $group,
                'status' => $row['status'],
                'user_id' => $this->user->id,
                'tds_section' => $row['tds_section'] ?? null,
                'tds_percentage' => $row['tds_percentage'] ?? null,
                'tcs_section' => $row['tcs_section'] ?? null,
                'tcs_percentage' => $row['tcs_percentage'] ?? null,
                'tax_type' => $row['tax_type'] ?? null,
                'tax_percentage' => $row['tax_percentage'] ?? null,
                'group_id' => $validatedData['group_id'],
                'company_id' => $validatedData['company_id'],
                'organization_id' => $validatedData['organization_id'],
                'import_status' => $status,
            ]);

            if ($uploadedItem) {
                $this->processItemFromUpload($uploadedItem);
            } else {
                throw new Exception("Failed to create item in the database.");
            }
            return $uploadedItem;

        } catch (\Exception $e) {
            Log::error("Error importing item: " . $e->getMessage(), [
                'error' => $e,
                'row' => $row
            ]);
            if (isset($uploadedItem)) {
                $uploadedItem->update([
                    'import_status' => 'Failed',
                    'import_remarks' => "Error importing item: " . $e->getMessage(),
                ]);
            }

            $this->onFailure($uploadedItem);
            throw new Exception("Error importing item: " . $e->getMessage());
        }
    }



    private function processItemFromUpload(UploadLedgerMaster $uploadedItem)
    {
        $errors = [];
        $organizationId = $uploadedItem->organization_id;
        $groupId = $uploadedItem->group_id;
        $companyId = $uploadedItem->company_id;
        $code = $uploadedItem->code;
        $group = $uploadedItem->ledger_groups;
        $name = $uploadedItem->name;
        try {
            $this->service->checkRequiredFields($code, $name, $group);
        } catch (Exception $e) {
            $errors[] = "Error fetching sub-category: " . $e->getMessage();
        }

        try {
            // Check uniqueness
            $this->service->checkLedgerUniqueness('code', $code,$this->user);
            // Validate unique name
            $this->service->checkLedgerUniqueness('name', $name,$this->user);
        } catch (Exception $e) {
            $errors[] = "Error Fields already Exists: " . $e->getMessage();
        }
        try {

            $groupData = $this->service->processGroupData($group);
            $groupIds = $groupData['groupIds'];
            $item = new Ledger([
                'code' => $uploadedItem->code,
                'name' => $uploadedItem->name,
                'ledger_group_id' => json_encode($groupIds),
                'status' => $this->service->mapStatus($uploadedItem['status'] ?? 1),
                'tds_section' => $uploadedItem->tds_section,
                'tds_percentage' => $uploadedItem->tds_percentage ?? null,
                'tcs_section' => $uploadedItem->tcs_section  ?? null,
                'tcs_percentage' => $uploadedItem->tcs_percentage  ?? null,
                'tax_type' => $uploadedItem->tax_type  ?? null,
                'tax_percentage' => $uploadedItem->tax_percentage ?? null,
                'organization_id' => $uploadedItem->organization_id,
                'company_id' => $uploadedItem->company_id,
                'group_id' => $uploadedItem->group_id,
            ]);


            $rules = [
                'code' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('erp_ledgers', 'code')->where(function ($query) use ($organizationId, $companyId, $groupId) {
                        return $query->where('organization_id', $organizationId)
                            ->where('company_id', $companyId)
                            ->where('group_id', $groupId);
                    }),
                ],
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('erp_ledgers', 'name')->where(function ($query) use ($organizationId, $companyId, $groupId) {
                        return $query->where('organization_id', $organizationId)
                            ->where('company_id', $companyId)
                            ->where('group_id', $groupId);
                    }),
                ],
                'tax_type' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'tax_percentage' => [
                    'nullable',
                    'int',
                    'max:255',
                ],
                'tds_section' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'tds_percentage' => [
                    'nullable',
                    'numeric',
                    'max:255',
                ],
                'tcs_section' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'tcs_percentage' => [
                    'nullable',
                    'numeric',
                    'max:255',
                ],
            ];

            $validator = Validator::make($item->toArray(), $rules, []);

            if ($validator->fails()) {
                $errors[] = 'Validation errors: ' . implode(', ', $validator->errors()->all());

                $uploadedItem->update([
                    'import_status' => 'Failed',
                    'import_remarks' => implode(', ', $errors),
                ]);

                $this->onFailure($uploadedItem);
                return;
            }

            $item->save();

            $uploadedItem->update([
                'import_status' => 'Success',
                'import_remarks' => 'Successfully imported item.',
            ]);

            $this->onSuccess($uploadedItem);
        } catch (Exception $e) {
            Log::error("Error fetching category: " . $e->getMessage(), ['error' => $e]);
            $errors[] = "Error fetching: " . $e->getMessage();
            $uploadedItem->update([
                'import_status' => 'Failed',
                'import_remarks' => implode(', ', $errors),
            ]);
            Log::info("Updated uploaded item status to Failed. Remarks: " . $uploadedItem->import_remarks . ". Status: " . $uploadedItem->status); //Check the status here
            $this->onFailure($uploadedItem);
            Log::info("Called onFailure for item code: " . $uploadedItem->code);
            return;
        }
    }
}
