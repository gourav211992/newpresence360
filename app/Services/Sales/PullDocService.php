<?php 
namespace App\Services\Sales;

use App\Helpers\ConstantHelper;
use App\Helpers\ServiceParametersHelper;
use App\Models\ErpSoItem;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\DataTables;

class PullDocService
{
    private $pullType = "";

    private $parameters = [];

    public function __construct(string $pullType, $parameters = [])
    {
        $this->pullType = $pullType;
        $this->parameters = $parameters;
    }

    public function getRecords() : array
    {
        $applicableBookIds = ServiceParametersHelper::getBookCodesForReferenceFromParam($this -> parameters['header_book_id']);
        $selectedIds = $this -> parameters['selected_ids'] ?? [];
        if ($this -> pullType == ConstantHelper::SO_SERVICE_ALIAS) {
            return $this -> getSoTypeItems($applicableBookIds, $selectedIds);
        } else {
            return ['status' => 'success', 'data' => []];
        }
    }

    public function getSoTypeItems(array $applicableBookIds, array $selectedIds) : array
    {
        $parameters = $this -> parameters;
        //Get the already referred Headers
        $referedHeaderId = ErpSoItem::whereIn('id', $selectedIds)->first()?->header?->id;
        //Retrieve the items
        $query = ErpSoItem::with(['attributes', 'uom', 'header.customer', 'header.shipping_address_details', 'header.billing_address_details'])
            ->whereHas('header', function ($subQuery) use ($parameters, $applicableBookIds, $referedHeaderId) {
                $subQuery->withDefaultGroupCompanyOrg()
                    ->when($referedHeaderId, fn($q) => $q->where('id', $referedHeaderId))
                    ->where('document_type', ConstantHelper::SO_SERVICE_ALIAS)
                    ->where('store_id', $parameters['store_id'])
                    ->whereIn('document_status', [ConstantHelper::APPROVED, ConstantHelper::APPROVAL_NOT_REQUIRED])
                    ->whereIn('book_id', $applicableBookIds)
                    ->when(isset($parameters['customer_id']) && $parameters['customer_id'], fn($q) => $q->where('customer_id', $parameters['customer_id']))
                    ->when(isset($parameters['book_id']) && $parameters['book_id'], fn($q) => $q->where('book_id', $parameters['book_id']))
                    ->when(isset($parameters['document_id']) && $parameters['document_id'], fn($q) => $q->where('id', $parameters['document_id']));
            })
            ->whereRaw('((order_qty - short_close_qty - GREATEST(picked_qty, plist_qty, dnote_qty)) + srn_qty) > 0')
            ->when(count($selectedIds) > 0, fn($q) => $q->whereNotIn('id', $selectedIds));
        // Return the datatable values
        return $this -> processSoTypeItems($query);
    }

    public function processSoTypeItems(QueryBuilder $query) : array
    {
        $parameters = $this -> parameters;
        $dataTables = DataTables::of($query)
            ->addColumn('book_code', fn($item) => $item?->header?->book_code ?? ($item->header->book?->book_code ?? ''))
            ->addColumn('document_number', fn($item) => $item?->header?->document_number)
            ->addColumn('document_date', fn($item) =>  method_exists($item?->header, 'getFormattedDate') ? $item->header->getFormattedDate("document_date") : '')
            ->addColumn('uom_name', function ($item) {
                return $item -> uom ?-> name;
            })
            ->addColumn('avl_stock', function ($item) use($parameters) {
                return number_format($item -> getAvailableStocks($parameters['store_id'], isset($parameters['sub_store_id']) ? $parameters['sub_store_id'] : null), 2);
            })
            ->addColumn('balance_qty', fn($item) => number_format($item->dnote_pull_balance_qty ?? 0, 6))
            ->editColumn('order_qty', fn($item) => number_format($item->order_qty ?? 0, 6))
            ->editColumn('rate', fn($item) => number_format($item->rate ?? 0, 2))
            ->make(true);
        return ['status' => 'success', 'data' => $dataTables];
    }

}
