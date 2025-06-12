<?php

namespace App\Http\Controllers\PurchaseOrder;

use Carbon\Carbon;
use App\Helpers\Helper;
use App\Models\Item;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Attribute;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\AttributeGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\PurchaseOrderExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PurchaseOrderScheduler;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class PurchaseOrderReportController extends Controller
{
    public function index()
    {
        $user = Helper::getAuthenticatedUser();
        $categories = Category::where('parent_id', null)->get();
        $sub_categories = Category::where('parent_id', '!=',null)->get();
        $items = Item::where('organization_id', $user->organization_id)->get();
        $vendors = Vendor::where('organization_id', $user->organization_id)->get();
        $employees = Employee::where('organization_id', $user->organization_id)->get();
        $users = User::where('organization_id', $user->organization_id)->get();
        $attribute_groups = AttributeGroup::where('organization_id',$user->organization_id)->get();
        // $attributes = Attribute::get();
        return view('procurement.po.report', compact('categories', 'sub_categories', 'items', 'vendors', 'employees', 'users', 'attribute_groups'));
    }

    public function getPurchaseOrdersFilter(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $period = $request->query('period');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $categoryId = $request->query('category');
        $subCategoryId = $request->query('subCategory');
        $vendorId = $request->query('vendor');
        $itemId = $request->query('item');
        $status = $request->query('status');
        $mCategoryId = $request->query('m_category');
        $mSubCategoryId = $request->query('m_subCategory');
        $mAttribute = $request->query('m_attribute');
        $mAttributeValue = $request->query('m_attributeValue');

        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
            $utype = 'user';
        } elseif (!empty(Auth::guard('web2')->user())) {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
            $utype = 'employee';
        } else {
            $organization_id = 1;
            $user_id = 1;
            $type = 1;
            $utype = 'user';
        }
        $query = PurchaseOrder::query()
        ->where('organization_id', $organization_id);
        
        $query->with([
            'po_items' => function($query) use ($itemId, $categoryId, $subCategoryId, $mCategoryId, $mSubCategoryId, $mAttribute, $mAttributeValue) {
            $query->whereHas('item', function($q) use ($itemId, $categoryId, $subCategoryId, $mCategoryId, $mSubCategoryId, $mAttribute, $mAttributeValue) {
                if ($itemId) {
                    $q->where('id', $itemId);
                }
                if ($categoryId) {
                    $q->where('category_id', $categoryId);
                }
                if ($subCategoryId) {
                    $q->where('subcategory_id', $subCategoryId);
                }
                if ($mCategoryId) {
                    $q->where('category_id', $mCategoryId);
                }
                if ($mSubCategoryId) {
                    $q->where('subcategory_id', $mSubCategoryId);
                }
            });
        },
        'po_items.item', 'po_items.item.category', 'po_items.item.subCategory', 'vendor'])
        ->where('organization_id', $user->organization_id);

        if ($mAttribute || $mAttributeValue) {
            $query->whereHas('po_items_attribute', function($subQuery) use ($mAttribute, $mAttributeValue) {
                // Filters for po_items_attribute
                $subQuery->whereHas('itemAttribute', function($q) use ($mAttribute, $mAttributeValue) {
                    if ($mAttribute) {
                        $q->where('attribute_group_id', $mAttribute);
                    }
                    if ($mAttributeValue) {
                        $jsonValue = json_encode([$mAttributeValue]);
                        // Filter on JSON_CONTAINS

                        $q->whereRaw('JSON_CONTAINS(attribute_id, ?)', [$jsonValue]);
                    }
                });
            });
        }

        // Date Filtering
        if (($startDate && $endDate) || $period) {
            if (!$startDate || !$endDate) {
                switch ($period) {
                    case 'this-month':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate = Carbon::now()->endOfMonth();
                        break;
                    case 'last-month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'this-year':
                        $startDate = Carbon::now()->startOfYear();
                        $endDate = Carbon::now()->endOfYear();
                        break;
                }
            }
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Vendor Filter
        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        // Status Filter
        if ($status) {
            $query->where('document_status', $status);
        }

        // Fetch Results
        $po_reports = $query->get();

        DB::enableQueryLog();
    
        return response()->json($po_reports);
    }
    
    public function getAttributeValues($id)
    {
        $attributes = Attribute::where('attribute_group_id', $id)->get();
    
        return response()->json($attributes->map(function ($attribute) {
            return [
                'id' => $attribute->id,
                'value' => $attribute->value,
                'attribute_group_name' => $attribute->attributeGroup->name,
            ];
        }));
    }

    public function addScheduler(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'to' => 'required|array',
            'type' => 'required|string',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);
        $toIds = $validatedData['to'];

        foreach($toIds as $toId) {
            PurchaseOrderScheduler::updateOrCreate(
                [
                    'toable_id' => $toId['id'],
                    'toable_type' => $toId['type']
                ],
                [
                    'type' => $validatedData['type'],
                    'date' => $validatedData['date'],
                    'remarks' => $validatedData['remarks']
                ]
            );
        }

        return Response::json(['success' =>'Scheduler Added Successfully!']);
    }

    public function sendReportMail(){
        $fileName = $this->getFileName();
        $user = Auth::user();

        $startDate = null;
        $endDate = null;
          // Create the export object with the date range
          $excelData = Excel::raw(new PurchaseOrderExport($startDate, $endDate), \Maatwebsite\Excel\Excel::XLSX);
  
          // Save the file locally for debugging
          $filePath = storage_path('app/public/purchase-order-report/' . $fileName);
          $directoryPath = storage_path('app/public/purchase-order-report');

            if (!file_exists($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }
          file_put_contents($filePath, $excelData);
  
          // Check if the file exists
          if (!file_exists($filePath)) {
              throw new \Exception('File does not exist at path: ' . $filePath);
          }

        Mail::send('emails.po_report', [], function ($message) use ($user, $filePath) {
            $message->to($user->email)
                    ->subject('Purchase Order Report')
                    ->attach($filePath);
        });

        return Response::json(['success' =>'Send Mail Successfully!']);
    }

    private function getFileName()
    {
        $now = carbon::now()->format('Y-m-d_H-i-s');
        return "purchase_order_report_{$now}.xlsx";
    }
}
