<?php

namespace App\Http\Controllers\CRM\API;

use App\Exceptions\ApiGenericException;
use App\Helpers\ConstantHelper;
use App\Http\Controllers\Controller;
use App\Models\CRM\ErpCustomerTarget;
use App\Models\CRM\ErpLocationMaster;
use App\Models\CRM\ErpSaleOrderSummary;
use App\Models\CRM\TempErpCustomerTarget;
use App\Models\CRM\TempErpOrderHeader;
use App\Models\CRM\TempErpOrderItem;
use App\Models\CRM\TempErpSaleOrderSummary;
use App\Models\ErpCustomer;
use App\Models\ErpOrderHeader;
use App\Models\ErpOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function syncOrderSummmary(Request $request)
    {
        $token = ConstantHelper::CRM_TOKEN . base64_encode(date('Ymd'));
        $accessKey = $request->header('access_key');
        if($accessKey != $token){
            throw new ApiGenericException("Invalid access key.");
        }

        $validator = Validator::make($request->all(),[
            '*.order_number' => 'required|string',
            '*.location' => 'required|string',
            '*.item_code' => 'required|string',
            '*.order_item_id' => 'required|string'
        ],[
            '*.order_number.required' => 'Order number is required.',
            '*.location.required' => 'Location is required.',
            '*.item_code.required' => 'Item code is required.',
            '*.order_item_id.required' => 'Order item id is required.',
        ]);

        if($validator->fails()){
            throw new ApiGenericException($validator->errors()->first());
        }

        $ordernum = '';
        $data = $request->all();
        if(count($data) < 1){
            throw new ApiGenericException("Data should not empty.");
        }
        try {
            \DB::beginTransaction();
            collect($data)->chunk(1000)->each(function ($chunk) use($ordernum){
                foreach ($chunk as $value) {
                    $uniqueid = $value['order_number'].''.$value['location'];
                    $orderheader = ErpOrderHeader::where('order_number',$value['order_number'])
                                        ->where('location',$value['location'])
                                        ->first();

                    // only fetch and update order header when the order number changes
                    if($ordernum != $uniqueid){
                        // update order header summary
                        if(!$orderheader){
                            $orderheader = new ErpOrderHeader();
                        }
                        $customer = ErpCustomer::where('customer_code',$value['customer_id'])->first();

                        $orderheader->order_number = $value['order_number'];
                        $orderheader->order_date = $value['order_date'] ? date('y-m-d',strtotime($value['order_date'])) : NULL;
                        $orderheader->customer_id = $customer ? $customer->id : NULL;
                        $orderheader->customer_code = $value['customer_id'];
                        $orderheader->total_order_value = $value['total_order_value'];
                        $orderheader->location = $value['location'];
                        $orderheader->order_status = $value['order_status'];
                        $orderheader->delivery_address = $value['delivery_address'];
                        $orderheader->organization_id = 6;
                        $orderheader->save();
                    }

                    // update order item summary
                    $orderitem = ErpOrderItem::where('order_number',$value['order_number'])
                                    ->where('item_code',$value['item_code'])
                                    ->where('order_item_id',$value['order_item_id'])
                                    ->first();

                    if(!$orderitem){
                        $orderitem = new ErpOrderItem();
                    }

                    $orderitem->order_number = $value['order_number'];
                    $orderitem->order_header_id = $orderheader->id;
                    $orderitem->order_item_id = $value['order_item_id'];
                    $orderitem->item_code = $value['item_code'];
                    $orderitem->item_name = $value['item_name'];
                    $orderitem->rate = $value['rate'];
                    $orderitem->size = $value['dimensions'];
                    $orderitem->store_type = $value['store_type'];
                    $orderitem->uom = $value['uom'];
                    $orderitem->delivery_date = $value['delivery_date'] ? date('y-m-d',strtotime($value['delivery_date'])) : null;
                    $orderitem->total_order_value = $value['item_value'];
                    $orderitem->order_quantity = $value['order_quantity'];
                    $orderitem->delivered_quantity = $value['delivered_quantity'];
                    $orderitem->order_status = $value['order_status'];
                    $orderitem->save();

                    // update $ordernum after processing the current order number
                    $ordernum = $uniqueid;  
                }
            });

            \DB::commit();
            return [
                "message" => "Record saved Successfully."
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }

    }

    public function syncCustomerTarget(Request $request)
    {
        $token = ConstantHelper::CRM_TOKEN . base64_encode(date('Ymd'));
        $accessKey = $request->header('access_key');
        if($accessKey != $token){
            throw new ApiGenericException("Invalid access key.");
        }

        $validator = Validator::make($request->all(),[
            '*.customer_code' => 'required|string',
            '*.year' => 'required|string',
        ],[
            '*.customer_code.required' => 'Customer code is required.',
            '*.year.required' => 'Year is required.',
        ]);

        if($validator->fails()){
            throw new ApiGenericException($validator->errors()->first());
        }

        $data = $request->all();
        if(count($data) < 1){
            throw new ApiGenericException("Data should not empty.");
        }

        try {
            \DB::beginTransaction();
            collect($data)->chunk(1000)->each(function ($chunk) {
                foreach ($chunk as $value) {
                    $customer = ErpCustomer::where('customer_code',$value['customer_code'])->first();
                    $location = ErpLocationMaster::where('location_code',$value['location_code'])->first();
                    $customerTarget = ErpCustomerTarget::where('customer_code',$value['customer_code'])
                                        ->where('year',$value['year'])
                                        ->first();

                    // update order header summary
                    if(!$customerTarget){
                        $customerTarget = new ErpCustomerTarget();
                    }

                    $customerTarget->customer_code = $value['customer_code'];
                    $customerTarget->customer_id = $customer ? $customer->id : NULL;
                    $customerTarget->channel_partner_name = $value['channel_partner_name'];
                    $customerTarget->location_code = $value['location_code'];
                    $customerTarget->location = $location ? $location->unit_name : NULL;
                    $customerTarget->sales_rep_code = NULL;
                    $customerTarget->ly_sale = $value['ly_sale'];
                    $customerTarget->cy_sale = $value['cy_sale'];
                    $customerTarget->apr = $value['apr'];
                    $customerTarget->may = $value['may'];
                    $customerTarget->jun = $value['jun'];
                    $customerTarget->jul = $value['jul'];
                    $customerTarget->aug = $value['aug'];
                    $customerTarget->sep = $value['sep'];
                    $customerTarget->oct = $value['oct'];
                    $customerTarget->nov = $value['nov'];
                    $customerTarget->dec = $value['dec'];
                    $customerTarget->jan = $value['jan'];
                    $customerTarget->feb = $value['feb'];
                    $customerTarget->mar = $value['mar'];
                    $customerTarget->year = $value['year'];
                    $customerTarget->total_target = $value['total_target'];
                    $customerTarget->organization_id = 6;
                    $customerTarget->save();
                }
            });
            \DB::commit();

            return [
                "message" => "Record saved Successfully."
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }

    }

    public function syncSalesOrderSummary(Request $request)
    {
        $token = ConstantHelper::CRM_TOKEN . base64_encode(date('Ymd'));
        $accessKey = $request->header('access_key');
        if($accessKey != $token){
            throw new ApiGenericException("Invalid access key.");
        }

        $validator = Validator::make($request->all(),[
            '*.customer_id' => 'required',
            '*.date' => 'required',
            '*.total_sale_value' => 'required',
        ],[
            '*.customer_id.required' => 'Customer code is required.',
            '*.date.required' => 'Year is required.',
            '*.total_sale_value.required' => 'Total sale value is required.',
        ]);

        if($validator->fails()){
            throw new ApiGenericException($validator->errors()->first());
        }

        $data = $request->all();
        if(count($data) < 1){
            throw new ApiGenericException("Data should not empty.");
        }
        
        try {
            \DB::beginTransaction();
            collect($data)->chunk(1000)->each(function ($chunk) {
                foreach ($chunk as $value) {
                    $date = $value['date'] ? date('Y-m-d',strtotime($value['date'])) : NULL;
                    $customer = ErpCustomer::where('customer_code',$value['customer_id'])->first();
                
                    $saleOrderSummary = ErpSaleOrderSummary::where('customer_code',$value['customer_id'])
                                        ->where('date',$date)
                                        ->first();

                    // update order header summary
                    if(!$saleOrderSummary){
                        $saleOrderSummary = new ErpSaleOrderSummary();
                    }

                    $saleOrderSummary->customer_code = $value['customer_id'];
                    $saleOrderSummary->customer_id = $customer ? $customer->id : NULL;
                    $saleOrderSummary->total_sale_value = $value['total_sale_value'];
                    $saleOrderSummary->date = $date;
                    $saleOrderSummary->organization_id = 6;
                    $saleOrderSummary->save();
                }

            });
            \DB::commit();
            return [
                "message" => "Record saved Successfully."
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }


    }
}
