<?php

namespace App\Http\Requests;

use App\Helpers\InventoryHelper;
use App\Models\MoBomMapping;
use Illuminate\Foundation\Http\FormRequest;

class PslipRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'book_id' => 'required',
            'cons.*.item_qty' => 'required|numeric|min:0.01',
        ];

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
        // Check the condition only if book_id is present
        // if ($this->filled('book_id')) {
        //     $user = Helper::getAuthenticatedUser();
        //     $numPattern = NumberPattern::where('organization_id', $user->organization_id)
        //                 ->where('book_id', $this->book_id)
        //                 ->orderBy('id', 'DESC')
        //                 ->first();
        //     // Update document_number rule based on the condition
        //     if ($numPattern && $numPattern->series_numbering == 'Manually') {
        //         if($poId) {
        //             $rules['document_number'] = 'required|unique:erp_purchase_orders,document_number,' . $poId;
        //         } else {
        //             $rules['document_number'] = 'required|unique:erp_purchase_orders,document_number';
        //         }
        //     }
        // }
        return $rules;
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($this->input('cons', []) as $index => $component) {
                $selectedAttributeIds = [];
                $moBomMappingId = $component['mo_bom_cons_id'] ?? null;
                $moBomMapping = MoBomMapping::find($moBomMappingId);

                $rm_type = 'R';
                $itemWipStationId = null;
                if($moBomMapping->rm_type =='sf') {
                    $rm_type = 'W';
                    $itemWipStationId = $moBomMapping->station_id;
                }

                $requiredQty = floatval($component['item_qty']);
                $itemAttributes = $moBomMapping->attributes ?? [];
                foreach ($itemAttributes as $itemAttr) {
                    $selectedAttributeIds[] = $itemAttr['attribute_value'];
                }
                $storeId = $moBomMapping->mo_product->mo->store_id ?? null;
                $subStoreId = $moBomMapping->mo_product->mo->sub_store_id ?? null;
                $stationId = $moBomMapping->mo_product->mo->station_id ?? null;
                $stocks = InventoryHelper::totalInventoryAndStock(
                    $moBomMapping->item_id,
                    $selectedAttributeIds,
                    $moBomMapping->uom_id,
                    $storeId,
                    $subStoreId,
                    null,
                    $stationId,
                    $rm_type,
                    $itemWipStationId
                );
                $stockBalanceQty = $stocks['confirmedStocks'] ?? 0;
                if ($requiredQty > $stockBalanceQty) {
                    $validator->errors()->add("cons.$index.item_qty", "Stock not available.");
                }
            }
        });
    }
    

    public function messages(): array
    {
        return [
            'book_id.required' => 'The series is required.',
            'cons.*.item_qty.required' => 'Stock not available.',
            'document_date.in' => 'The document date must be today.',
            'document_date.required' => 'The document date is required.',
            'document_date.date' => 'Please enter a valid date for the document date.',
            'document_date.after_or_equal' => 'The document date cannot be in the past.',
            'document_date.before_or_equal' => 'The document date cannot be in the future.',
        ];
 
    }
}
