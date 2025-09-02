<?php

namespace App\Http\Requests;

use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Models\MfgOrder;
use App\Models\MoBomMapping;
use App\Models\PslipBomConsumption;
use App\Traits\ProcessesComponentJson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class PslipRequest extends FormRequest
{
    use ProcessesComponentJson;
    protected function prepareForValidation(): void
    {
        $this->processComponentJson('components_json');
    }
    public function rules(): array
    {
        // dd($this->request->all());
        $rules = [
            'book_id' => 'required',
            'expiry_date' => 'nullable|date',
            'lot_number' => 'nullable|string|max:40',
            'manufacturing_year' => 'nullable|integer|nullable|digits:4|min:2000|max:' . date('Y'),
            'cons.*.item_qty' => 'required|numeric|min:0.01',
            'item_qty.*' => 'required|numeric|min:1',
            'item_accepted_qty.*' => 'required|numeric|min:1',
        ];

        if(!$this->input('id')) {
            $rules['fg_sub_store_id'] = 'required';
        }

        // If Item is_batch_no == 1
        if($this->input('is_batch_no') ==1) {
            $rules['expiry_date'] = 'required|date';
            $rules['manufacturing_year'] = 'required|integer|nullable|digits:4|min:2000|max:' . date('Y');
            $rules['lot_number'] = 'required|string|max:40';
        }

        // Document date validation

        if(!$this->input('id') && ($this->input('document_status') == ConstantHelper::SUBMITTED || $this->input('document_status') == ConstantHelper::DRAFT))
        {
            $today = now()->toDateString();
            $isPast = false;
            $isFeature = false;
            $futureAllowed = isset($parameters['future_date_allowed']) && is_array($parameters['future_date_allowed']) && in_array('yes', array_map('strtolower', $parameters['future_date_allowed']));
            $backAllowed = isset($parameters['back_date_allowed']) && is_array($parameters['back_date_allowed']) && in_array('yes', array_map('strtolower', $parameters['back_date_allowed']));

            if (!$futureAllowed && !$backAllowed) {
                $rules['document_date'] = "required|date|in:$today";
            } else {
                if ($futureAllowed) {
                    $rules['document_date'] = "after_or_equal:$today";
                    $isFeature = true;
                } else {
                    $rules['document_date'] = "before_or_equal:$today";
                    $isFeature = false;
                }
                if ($backAllowed) {
                    $rules['document_date'] = "before_or_equal:$today";
                    $isPast = true;
                } else {
                    $rules['document_date'] = "after_or_equal:$today";
                    $isPast = false;
                }
            }
            if($isFeature && $isPast) {
                $rules['document_date'] = "required|date";
            }
        }

        $moId = $this->mo_id ?? null;
        $machines = collect();
        $mo = MfgOrder::where('id', $moId)->first();
        $productionBom = $mo?->productionRoute ?? null;
        if($productionBom) {
            $machines = $productionBom?->machines()
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        }
        if($machines->isNotEmpty()) {
            $rules['machine_id'] = 'array';
            $rules['machine_id.*'] = 'nullable|array';
            $rules['machine_id.*.*'] = 'required|integer|exists:erp_machines,id';
        }

        return $rules;
    }

    protected function withValidator($validator)
    {
        // $id = $this->input('id');
        $validator->after(function ($validator){

            foreach ($this->input('cons', []) as $index => $component) {
                $selectedAttributeIds = [];
                $moBomMappingId = $component['mo_bom_cons_id'] ?? null;
                // $pslipBomMappingId = $component['pslip_bom_cons_id'] ?? null;
                // if($pslipBomMappingId) {
                //     $moBomMapping = PslipBomConsumption::find($pslipBomMappingId);
                // } else {
                // }
                $moBomMapping = MoBomMapping::find($moBomMappingId);
                $rm_type = 'R';
                $itemWipStationId = null;
                if($moBomMapping?->rm_type =='sf') {
                    $rm_type = 'W';
                    $itemWipStationId = $moBomMapping->station_id;
                }

                $consumptionQty = floatval($component['consumption_qty']);
                // $requiredQty = floatval($component['item_qty']);
                // $itemAttributes = $moBomMapping->attributes ?? [];
                // foreach ($itemAttributes as $itemAttr) {
                //     $selectedAttributeIds[] = $itemAttr['attribute_value'];
                // }

                $itemAttributes = $component['attribute_value']
                    ?? $moBomMapping->attributes
                    ?? [];

                $itemAttributes = is_array($itemAttributes) ? $itemAttributes : [];

                foreach ($itemAttributes as $itemAttr) {
                    $selectedAttributeIds[] = $itemAttr['attribute_value'] ?? null;
                }

                $storeId = $moBomMapping?->mo_product?->mo?->store_id ?? null;
                $subStoreId = $moBomMapping?->mo_product?->mo?->sub_store_id ?? null;
                $stationId = $moBomMapping?->mo_product?->mo?->station_id ?? null;
                $stocks = InventoryHelper::totalInventoryAndStock(
                    $component['item_id'],
                    // $moBomMapping?->item_id,
                    $selectedAttributeIds,
                    $component['uom_id'],
                    $storeId,
                    $subStoreId,
                    null,
                    $stationId,
                    $rm_type,
                    $itemWipStationId
                );

                $stockBalanceQty = floatval($stocks['confirmedStocks'] ?? 0);

                if (
                    $this->input('document_status') !== ConstantHelper::APPROVED &&
                    !$this->input('id') &&
                    $consumptionQty > $stockBalanceQty
                ) {
                    $validator->errors()->add("cons.$index.item_qty", "Stock not available.");
                }
            }
        });
    }


    public function messages(): array
    {
        return [
            'book_id.required' => 'The series is required.',
            'fg_sub_store_id.required' => 'The sub store is required.',
            'cons.*.item_qty.required' => 'Stock not available.',
            'document_date.in' => 'The document date must be today.',
            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date for the document date.',
            'document_date.after_or_equal' => 'The document date cannot be in the past.',
            'document_date.before_or_equal' => 'The document date cannot be in the future.',
            'item_qty.*.required' => 'Produced quantity is required.',
            'item_qty.*.numeric'  => 'Produced quantity must be a number.',
            'item_qty.*.min'      => 'Produced quantity must be at least 1.',
            'item_accepted_qty.*.required' => 'Accepted quantity is required.',
            'item_accepted_qty.*.numeric'  => 'Accepted quantity must be a number.',
            'item_accepted_qty.*.min'      => 'Accepted quantity must be at least 1.',
        ];

    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
            'is_tab_exist' => true,
        ], 422));
    }
}
