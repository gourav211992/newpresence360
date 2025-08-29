<?php

namespace App\Imports;

use App\Models\UploadInspectionChecklist;
use App\Models\InspectionChecklist;
use App\Models\InspectionChecklistDetail;
use App\Models\InspectionChecklistDetailValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Validation\Rule;
use Exception;
use stdClass;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;

class InspectionChecklistImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected $successful;
    protected $failed;

    public function __construct()
    {
        $this->successful = collect();
        $this->failed = collect();
    }

    public function chunkSize(): int
    {
        return 500;
    }

    protected function getServiceData($organization, $services)
    {
        $validatedData = [
            'group_id' => null,
            'company_id' => null,
            'organization_id' => null,
        ];

        if ($services && isset($services['services']) && count($services['services']) > 0) {
            $firstService = $services['services']->first();
            $policyData = Helper::getPolicyByServiceId($firstService->service_id);

            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validatedData['group_id'] = $policyLevelData['group_id'] ?? $organization->group_id;
                $validatedData['company_id'] = $policyLevelData['company_id'] ?? $organization->company_id;
                $validatedData['organization_id'] = $policyLevelData['organization_id'] ?? null;
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = $organization->company_id;
            $validatedData['organization_id'] = null;
        }

        return $validatedData;
    }

    public function collection(Collection $rows)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $batchNo = 'BATCH-' . now()->format('YmdHis');
        $parentUrl = ConstantHelper::INSPECTION_CHECKLIST_ALIAS;
        $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        $validatedData = $this->getServiceData($organization, $services);

        $uploadedRows = collect();
        $rowsToProcess = $rows->skip(1);

        foreach ($rowsToProcess as $row) {
            DB::beginTransaction();
            try {
                if (empty($row['checklist_name']) || empty($row['detail_name'])) {
                    $upload = UploadInspectionChecklist::create([
                        'name' => $row['checklist_name'] ?? null,
                        'detail_name' => $row['detail_name'] ?? null,
                        'status' => 'Failed',
                        'remarks' => 'Checklist Name and Detail Name are required.',
                        'batch_no' => $batchNo,
                        'user_id' => $user->id,
                        'group_id' => $validatedData['group_id'],
                        'company_id' => $validatedData['company_id'],
                        'organization_id' => $validatedData['organization_id'],
                    ]);
                    $this->failed->push($upload);
                    DB::commit();
                    continue;
                }

                $values = null;
                if (strtolower($row['data_type'] ?? '') === 'list' && !empty($row['values'])) {
                    $values = array_map('trim', explode(',', $row['values']));
                }

                $staging = UploadInspectionChecklist::create([
                    'group_id' => $validatedData['group_id'],
                    'company_id' => $validatedData['company_id'],
                    'organization_id' => $validatedData['organization_id'],
                    'name' => $row['checklist_name'] ?? null,
                    'description' => $row['description'] ?? null,
                    'detail_name' => $row['detail_name'] ?? null,
                    'data_type' => $row['data_type'] ?? null,
                    'mandatory' => (isset($row['mandatory']) && strtoupper($row['mandatory']) === 'Y') ? 1 : 0,
                    'detail_description' => $row['description'] ?? null,
                    'values' => $values,
                    'batch_no' => $batchNo,
                    'user_id' => $user->id,
                    'status' => 'Pending',
                    'remarks' => 'Processing row...',
                ]);

                DB::commit();
                $uploadedRows->push($staging);

            } catch (Exception $e) {
                DB::rollback();
                Log::error("Error inserting staging row: " . $e->getMessage(), ['row' => $row]);
                $staging = $staging ?? UploadInspectionChecklist::create([
                    'name' => $row['checklist_name'] ?? null,
                    'detail_name' => $row['detail_name'] ?? null,
                    'status' => 'Failed',
                    'remarks' => $e->getMessage(),
                    'batch_no' => $batchNo,
                    'user_id' => $user->id,
                    'group_id' => $validatedData['group_id'],
                    'company_id' => $validatedData['company_id'],
                    'organization_id' => $validatedData['organization_id'],
                ]);
                $this->failed->push($staging);
            }
        }

        if ($uploadedRows->isNotEmpty()) {
            $this->processInspectionFromUpload($uploadedRows);
        }
    }

  protected function processInspectionFromUpload(Collection $stagingRows)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        DB::transaction(function () use ($stagingRows, $organization) {

            $grouped = $stagingRows->groupBy('name');

            foreach ($grouped as $checklistName => $rows) {
                try {
                    $firstRow = $rows->first();

                    $allFailed = false;
                    $failedMessages = [];
                    $detailNames = [];

                    //Validate checklist header
                    $checklistValidator = validator(
                        ['name' => $firstRow->name],
                        [
                            'name' => [
                                'required',
                                'string',
                                'max:255',
                                Rule::unique('erp_inspection_checklists', 'name')
                                    ->where(function ($query) use ($firstRow, $organization) {
                                        if ($firstRow->group_id !== null) {
                                            $query->where('group_id', $firstRow->group_id);
                                        }
                                        if ($organization->company_id !== null) {
                                            $query->where(function ($q) use ($organization) {
                                                $q->where('company_id', $organization->company_id)
                                                ->orWhereNull('company_id');
                                            });
                                        }
                                        if ($organization->id !== null) {
                                            $query->where(function ($q) use ($organization) {
                                                $q->where('organization_id', $organization->id)
                                                ->orWhereNull('organization_id');
                                            });
                                        }
                                        $query->whereNull('deleted_at');
                                    }),
                            ],
                        ],
                        ['name.unique' => 'Checklist name has already been taken.']
                    );

                    if ($checklistValidator->fails()) {
                        $allFailed = true;
                        $failedMessages[] = implode(', ', $checklistValidator->errors()->all());
                    }

                    // Validate details
                    foreach ($rows as $row) {
                        if (!empty($row->values) && is_array($row->values)) {
                            $row->values = array_map('trim', $row->values);
                            if (count($row->values) !== count(array_unique($row->values))) {
                                $allFailed = true;
                                $failedMessages[] = "Duplicate values are not allowed for detail '{$row->detail_name}'.";
                            }
                        }

                        if (in_array($row->detail_name, $detailNames)) {
                            $allFailed = true;
                            $failedMessages[] = "Duplicate detail name '{$row->detail_name}' in checklist '{$checklistName}'.";
                        } else {
                            $detailNames[] = $row->detail_name;
                        }

                        $detailValidator = validator(
                            [
                                'name' => $row->detail_name,
                                'data_type' => $row->data_type,
                                'mandatory' => $row->mandatory,
                                'values' => $row->values,
                            ],
                            [
                                'name' => 'required|string|max:255',
                                'data_type' => 'nullable|string|max:255',
                                'values' => 'nullable|array',
                                'values.*' => 'string|max:255',
                            ]
                        );

                        if ($detailValidator->fails()) {
                            $allFailed = true;
                            $failedMessages[] = "Detail '{$row->detail_name}': " . implode(', ', $detailValidator->errors()->all());
                        }
                    }

                    // If validation failed → mark staging as Failed
                    if ($allFailed) {
                        foreach ($rows as $row) {
                            $row->update([
                                'status' => 'Failed',
                                'remarks' => implode(' | ', $failedMessages),
                            ]);
                        }
                        $failedHeader = new \stdClass();
                        $failedHeader->name = $checklistName;
                        $failedHeader->status = 'Failed';
                        $failedHeader->remarks = implode(' | ', $failedMessages);
                        $this->failed->push($failedHeader);
                        continue; 
                    }

                    //If valid → create checklist in main table
                    $checklist = InspectionChecklist::create([
                        'group_id' => $firstRow->group_id,
                        'company_id' => $organization->company_id,
                        'organization_id' => $organization->organization_id,
                        'name' => $firstRow->name,
                        'description' => $firstRow->description,
                        'type' => ConstantHelper::ITEM_INSPECTION_CHECKLIST_TYPE,
                        'status' => 'active',
                    ]);

                    //Create details & values
                    foreach ($rows as $row) {
                        $detail = $checklist->details()->create([
                            'name' => $row->detail_name,
                            'data_type' => $row->data_type,
                            'description' => $row->detail_description,
                            'mandatory' => $row->mandatory,
                        ]);

                        if (!empty($row->values) && is_array($row->values)) {
                            foreach ($row->values as $val) {
                                $detail->values()->create(['value' => $val]);
                            }
                        }

                        $row->update([
                            'status' => 'Success',
                            'remarks' => 'Successfully imported checklist detail.',
                        ]);
                    }

                    // Push success summary
                    $successfulHeader = new \stdClass();
                    $successfulHeader->name = $checklist->name;
                    $successfulHeader->status = 'Success';
                    $successfulHeader->remarks = 'Successfully imported checklist';
                    $this->successful->push($successfulHeader);

                } catch (Exception $e) {
                    Log::error("Error processing checklist group: " . $e->getMessage(), ['rows' => $rows]);

                    foreach ($rows as $row) {
                        $row->update([
                            'status' => 'Failed',
                            'remarks' => $e->getMessage(),
                        ]);
                    }

                    $failedHeader = new \stdClass();
                    $failedHeader->name = $checklistName;
                    $failedHeader->status = 'Failed';
                    $failedHeader->remarks = $e->getMessage();
                    $this->failed->push($failedHeader);
                }
            }
        });
    }


    public function getSuccessful()
    {
        return $this->successful;
    }

    public function getFailed()
    {
        return $this->failed;
    }
}
