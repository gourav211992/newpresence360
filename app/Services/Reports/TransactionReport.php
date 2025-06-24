<?php

namespace App\Services\Reports;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\TransactionReportHelper;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Book;
use App\Models\BookDynamicField;
use App\Models\Category;
use App\Models\DynamicFieldDetail;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\Service;
use App\Models\SubType;
use Ramsey\Collection\Set;

class TransactionReport
{
    //Fallback if no service label is found
    private const DEFAULT_REPORT_NAME = '';
    private $serviceAlias;
    private $reportType = '';
    public $reportName;
    public $filterRoute;
    public $indexRoute;
    public $reportColumns;
    public $filters;
    private $dynamicFieldsSeperationIndex = null;
    public $routeName = null;
    public $parameters = [];

    public function __construct(string $serviceAlias, string $reportType = '')
    {
        TransactionReportHelper::initialize();
        $this -> serviceAlias = $serviceAlias;
        $this -> reportType = $reportType;
        //Set the report name
        $this -> reportName = isset(ConstantHelper::SERVICE_LABEL[$this -> serviceAlias]) ? 
        ConstantHelper::SERVICE_LABEL[$this -> serviceAlias] : self::DEFAULT_REPORT_NAME;
        //Set the report filter route
        $this -> filterRoute = isset(TransactionReportHelper::FILTER_ROUTES[$this -> serviceAlias . $this -> reportType]) ? 
        TransactionReportHelper::FILTER_ROUTES[$this -> serviceAlias . $this -> reportType] : '';
        //Split the route and parameters
        $filterRouteWithParams = explode(',', $this -> filterRoute, 2); // max 2 parts
        $this -> routeName = $filterRouteWithParams[0];
        $this -> parameters = isset($filterRouteWithParams[1]) ? json_decode($filterRouteWithParams[1], true) : [];
        //Set the report filter route
        $this -> indexRoute = isset(TransactionReportHelper::INDEX_ROUTES[$this -> serviceAlias]) ? 
        TransactionReportHelper::INDEX_ROUTES[$this -> serviceAlias] : '';
        //Get the dynamic fields seperation Index
        $this -> dynamicFieldsSeperationIndex = isset(TransactionReportHelper::DYNAMIC_FIELDS_SEPERATION_INDEX[$this -> serviceAlias]) ?
        TransactionReportHelper::DYNAMIC_FIELDS_SEPERATION_INDEX[$this -> serviceAlias] : null;
        //Set the report columns
        $this -> reportColumns = self::getReportColumns();
        //Get the filters
        $this -> filters = isset(TransactionReportHelper::FILTERS_MAPPING[$this -> serviceAlias]) ? 
        TransactionReportHelper::FILTERS_MAPPING[$this -> serviceAlias] : [];  
        
    }
    public function getBasicFilters()
    {
        //Get the common filters
        $user = Helper::getAuthenticatedUser();
        $categories = Category::select('id AS value', 'name AS label') -> withDefaultGroupCompanyOrg() 
        -> whereNull('parent_id') -> get();
        $subCategories = Category::select('id AS value', 'name AS label') -> withDefaultGroupCompanyOrg() 
        -> whereNotNull('parent_id') -> get();
        $items = Item::select('id AS value', 'item_name AS label') -> withDefaultGroupCompanyOrg()->get();
        $users = AuthUser::select('id AS value', 'name AS label') -> where('organization_id', $user -> organization_id)->get();
        $attributeGroups = AttributeGroup::select('id AS value', 'name AS label')->withDefaultGroupCompanyOrg()->get();

        //Custom filters (to be restr)

        return array(
            'itemCategories' => $categories,
            'itemSubCategories' => $subCategories,
            'items' => $items,
            'users' => $users,
            'attributeGroups' => $attributeGroups 
        );
    }

    public function getIndexPageData()
    {
        $user = Helper::getAuthenticatedUser();
        $filters = $this -> getBasicFilters(); // Get the Filters
        $reportName = $this -> reportName; // Report Name Label
        $filterRoute = $this -> filterRoute; // Filter Route (Query Function for each service/ table)
        $filterRoute = explode(',', $filterRoute, 2); // max 2 parts
        $routeName = $filterRoute[0];
        $params = isset($filterRoute[1]) ? json_decode($filterRoute[1], true) : [];

        $filterRoute = route($routeName, $this -> parameters);
        $indexRoute = $this -> indexRoute; // Index Route (For Breadcrumb)
        $tableHeadersColumn = $this -> reportColumns; //Columns or Headers for Table
        $autoCompleteFilters = $this -> filters;// Applicable Side filters
        $users = AuthUser::select('id', 'name', 'email') -> where('organization_id', $user -> organization_id) -> get();
        //Return the data in same format
        return 
        [
            'filters' => $filters,
            'reportName' => $reportName,
            'autoCompleteFilters' => $autoCompleteFilters,
            'filterRoute' => $filterRoute,
            'indexRoute' => $indexRoute,
            'tableHeaders' => $tableHeadersColumn,
            'users' => $users
        ];
    }

    private function getReportColumns()
    {
        $columns = isset(TransactionReportHelper::$TABLE_HEADERS[$this -> serviceAlias . $this -> reportType]) ?
        TransactionReportHelper::$TABLE_HEADERS[$this -> serviceAlias . $this -> reportType] : [];
        $model = isset(ConstantHelper::SERVICE_ALIAS_MODELS[$this -> serviceAlias]) ? ConstantHelper::SERVICE_ALIAS_MODELS[$this -> serviceAlias] : '';
        if (!isset($model)) {
            return $columns;
        }
        if (method_exists(resolve('App\\Models\\'.$model), 'dynamic_fields') && $this -> dynamicFieldsSeperationIndex) {
            $serviceId = Service::where('alias', $this -> serviceAlias) -> first() ?-> id;
            $bookIds = Book::withDefaultGroupCompanyOrg() -> where('service_id', $serviceId) -> get() -> pluck('id') -> toArray();
            $dynamicFieldIds = BookDynamicField::whereIn('book_id', $bookIds) -> get() -> pluck('dynamic_field_id') -> toArray();
            $dynamicFields = DynamicFieldDetail::whereIn('header_id', $dynamicFieldIds)  -> get();
            $dynamicFieldsCols = [];
            foreach ($dynamicFields as $dynamicFieldIndex => $dynamicField) {
                array_push($dynamicFieldsCols, [
                    'name' => $dynamicField -> name,
                    'field' => $dynamicField -> name,
                    'header_class' => '',
                    'column_class' => 'no-wrap',
                    'header_style' => '',
                    'column_style' => '',
                ]);
            }
            $additionalColumns = $this -> appendAdditionalColumns();
            // dd($additionalColumns['placement'] + $this -> dynamicFieldsSeperationIndex);
            
            array_splice($columns, $this -> dynamicFieldsSeperationIndex , 0, $dynamicFieldsCols);
            array_splice($columns, $additionalColumns['placement'] + count($dynamicFieldsCols) , 0, $additionalColumns['columns']);
        }
        return $columns;
    }

    private function appendAdditionalColumns() : array
    {
        $additionalColumns = [];
        if ($this -> serviceAlias === ConstantHelper::SO_SERVICE_ALIAS && $this -> reportType == "attributeGrouped") {
            //Shufab Report
            // $subTypeIds = SubType::whereIn('name', [ConstantHelper::FINISHED_GOODS, ConstantHelper::TRADED_ITEM, 
            // ConstantHelper::ASSET,ConstantHelper::WIP_SEMI_FINISHED]) -> get() -> pluck('id') -> toArray();
            // $itemAttributes = ItemAttribute::whereHas('erpItem', function ($erpItem) use($subTypeIds) {
            //     $erpItem -> withDefaultGroupCompanyOrg() -> where(function ($typeQuery) use($subTypeIds) {
            //         $typeQuery -> whereHas('subTypes', function ($subTypeQuery) use($subTypeIds) {
            //             $subTypeQuery -> whereIn('sub_type_id', $subTypeIds);
            //         });
            //     });
            // }) -> get() -> pluck('attribute_id') -> toArray();
            // $attributeIds = array_merge(...$itemAttributes);
            // $attributes = Attribute::with('attributeGroup') -> whereIn('id', $attributeIds) -> withWhereHas('attributeGroup', function ($attrQuery) {
            //     $attrQuery -> whereRaw('LOWER(name) = ?', ['size']);
            // })
            // -> orderByRaw('CAST(value AS UNSIGNED)') -> get();
            for ($i=1; $i <= 14; $i++) { 
                array_push($additionalColumns, [
                    'name' => "SIZE" . ":" . " " .$i,
                    'field' => "SIZE" . "_" .$i . "_CUSTOMATTRCODE",
                    'header_class' => '',
                    'column_class' => 'no-wrap',
                    'header_style' => '',
                    'column_style' => '',
                ]);
            }
        }
        if($this -> serviceAlias === ConstantHelper::BOM_SERVICE_ALIAS){
            
        }
        return ['columns' => $additionalColumns, 'placement' => 11];
    }
}
