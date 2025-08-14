<?php
namespace App\Services;

use App\Http\Requests\MrnRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\ErpAddress;
use Illuminate\Support\Facades\Auth;

class MrnService
{
    public function createMrnHeader(MrnRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $user = Auth::user();
            $calculatedData = $this->calculateOrderAmounts($validatedData);
            $billingAddressJson = $validatedData['billing_to'] ? json_encode(ErpAddress::find($validatedData['billing_to'])) : null;
            $shippingAddressJson = $validatedData['ship_to'] ? json_encode(ErpAddress::find($validatedData['ship_to'])) : null;

            $mrnHeader = MrnHeader::create([
                'series_id' => $validatedData['series_id'],
                'organization_id' => $user->organization_id,
                'group_id' => $user->group_id,
                'company_id' => $user->company_id,
                'mrn_no' => $validatedData['mrn_no'],
                'po_date' => $validatedData['po_date'],
                'vendor_id' => $validatedData['vendor_id'],
                'billing_to' => $validatedData['billing_to'],
                'ship_to' => $validatedData['ship_to'],
                'billing_address' => $billingAddressJson,
                'shipping_address' => $shippingAddressJson,
                'reference_number' => $validatedData['reference_number'] ?? null,
                'currency_id' => $validatedData['currency_id'],
                'sub_total' => $calculatedData['sub_total'],
                'discount' => $validatedData['discount'],
                'discount_amount' => $calculatedData['discount_amount'],
                'gst' => $calculatedData['gst'],
                'gst_details' => json_encode($calculatedData['gst_details']),
                'tax_value' => $calculatedData['tax_value'],
                'taxable_amount' => $calculatedData['taxable_amount'],
                'other_expenses' => $validatedData['other_expenses'],
                'total_amount' => $calculatedData['total_amount'],
                'item_remark' => $validatedData['item_remark'] ?? null,
                'final_remarks' => $validatedData['final_remarks'] ?? null,
            ]);

            if ($mrnHeader->id) {
                $this->saveItems($mrnHeader->id, $calculatedData['items_calculated_data']);
            }

            return $mrnHeader;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateMrnHeader($id, MrnRequest $request)
    {
        try {
            $mrnHeader = MrnHeader::find($id);
            if (!$mrnHeader) {
                throw new \Exception('Mrn Header not found.');
            }

            $validatedData = $request->validated();
            $user = Auth::user();

            $billingAddress = $validatedData['billing_to'] ? ErpAddress::find($validatedData['billing_to']) : null;
            $shippingAddress = $validatedData['ship_to'] ? ErpAddress::find($validatedData['ship_to']) : null;
    
            $billingAddressJson = $billingAddress ? json_encode($billingAddress) : null;
            $shippingAddressJson = $shippingAddress ? json_encode($shippingAddress) : null;
            $calculatedData = $this->calculateOrderAmounts($validatedData);
    
            $mrnHeader->update([
                'series_id' => $validatedData['series_id'],
                'organization_id' => $user->organization_id,
                'group_id' => $user->group_id,
                'company_id' => $user->company_id,
                'mrn_no' => $validatedData['mrn_no'],
                'po_date' => $validatedData['po_date'],
                'vendor_id' => $validatedData['vendor_id'],
                'billing_to' => $validatedData['billing_to'],
                'ship_to' => $validatedData['ship_to'],
                'billing_address' => $billingAddressJson,
                'shipping_address' => $shippingAddressJson,
                'reference_number' => $validatedData['reference_number'] ?? null,
                'currency_id' => $validatedData['currency_id'],
                'sub_total' => $calculatedData['sub_total'],
                'discount' => $validatedData['discount'],
                'discount_amount' => $calculatedData['discount_amount'],
                'gst' => $calculatedData['gst'],
                'gst_details' => json_encode($calculatedData['gst_details']),
                'tax_value' => $calculatedData['tax_value'],
                'taxable_amount' => $calculatedData['taxable_amount'],
                'other_expenses' => $validatedData['other_expenses'],
                'total_amount' => $calculatedData['total_amount'],
                'item_remark' => $validatedData['item_remark'] ?? null,
                'final_remarks' => $validatedData['final_remarks'] ?? null,
            ]);
    
            // Delete existing items and save the new ones
            $mrnHeader->items()->delete(); // Delete all items
    
            // Save new items
            $this->saveItems($mrnHeader->id, $calculatedData['items_calculated_data']);
    
            return $mrnHeader;
    
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    
    private function saveItems(int $mrnHeaderId, array $items)
    {
        foreach ($items as $itemData) {
            MrnDetail::create([
                'mrn_header_id' => $mrnHeaderId,
                'item_id' => $itemData['item_id'],
                'hsn_code' => $itemData['hsn_code'] ?? null,
                'uom_id' => $itemData['uom_id'],
                'expected_delivery_date' => $itemData['expected_delivery_date'] ?? null,
                'quantity' => $itemData['quantity'],
                'rate' => $itemData['rate'],
                'basic_value' => $itemData['basic_value'],
                'discount_percentage' => $itemData['discount_percentage'],
                'discount_amount' => $itemData['discount_amount'],
                'net_value' => $itemData['net_value'],
                'sgst_percentage' => $itemData['sgst_percentage'],
                'cgst_percentage' => $itemData['cgst_percentage'],
                'igst_percentage' => $itemData['igst_percentage'],
                'tax_value' => $itemData['tax_value'],
                'taxable_amount' => $itemData['taxable_amount'],
                'sub_total' => $itemData['sub_total'],
                'selected_item' => $itemData['selected_item'] ?? null,
            ]);
        }
    }

    private function calculateOrderAmounts(array $data): array
    {
        $subTotal = 0;
        $discountAmount = 0;
        $totalTaxableAmount = 0;
        $totalTaxValue = 0;
        $gstDetails = [
            'sgst_amount' => 0,
            'cgst_amount' => 0,
            'igst_amount' => 0,
        ];

        $itemsCalculatedData = [];

        foreach ($data['items'] as $item) {
            $basicValue = ($item['quantity'] ?? 0) * ($item['rate'] ?? 0);
            $discountAmountItem = ($basicValue * ($item['discount_percentage'] ?? 0)) / 100;
            $netValue = $basicValue - $discountAmountItem;

            $sgstAmount = ($netValue * ($item['sgst_percentage'] ?? 0)) / 100;
            $cgstAmount = ($netValue * ($item['cgst_percentage'] ?? 0)) / 100;
            $igstAmount = ($netValue * ($item['igst_percentage'] ?? 0)) / 100;

            $taxValue = $sgstAmount + $cgstAmount + $igstAmount;
            $taxableAmount = $netValue;
            $subTotalItem = $netValue + $taxValue;

            $itemsCalculatedData[] = [
                'item_id' => $item['item_id'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'uom_id' => $item['uom_id'],
                'alternate_uom_id' => $item['alternate_uom_id'] ?? null,
                'expected_delivery_date' => $item['expected_delivery_date'] ?? null,
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'basic_value' => $basicValue,
                'discount_percentage' => $item['discount_percentage'],
                'discount_amount' => $discountAmountItem,
                'net_value' => $netValue,
                'sgst_percentage' => $item['sgst_percentage'],
                'cgst_percentage' => $item['cgst_percentage'],
                'igst_percentage' => $item['igst_percentage'],
                'tax_value' => $taxValue,
                'taxable_amount' => $taxableAmount,
                'sub_total' => $subTotalItem,
                'selected_item' => $item['selected_item'] ?? null,
            ];

            $subTotal += $basicValue;
            $discountAmount += $discountAmountItem;
            $totalTaxableAmount += $taxableAmount;
            $totalTaxValue += $taxValue;

            $gstDetails['sgst_amount'] += $sgstAmount;
            $gstDetails['cgst_amount'] += $cgstAmount;
            $gstDetails['igst_amount'] += $igstAmount;
        }

        $totalAmount = $subTotal - $discountAmount + $totalTaxValue + ($data['other_expenses'] ?? 0);

        return [
            'sub_total' => $subTotal,
            'discount_amount' => $discountAmount,
            'gst' => $totalTaxValue,
            'gst_details' => $gstDetails,
            'tax_value' => $totalTaxValue,
            'taxable_amount' => $totalTaxableAmount,
            'total_amount' => $totalAmount,
            'items_calculated_data' => $itemsCalculatedData,
        ];
    }
}
