<?php

namespace App\Services\Reports;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\TransactionReportHelper;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Book;
use App\Models\BookDynamicField;
use App\Models\Category;
use App\Models\DynamicFieldDetail;
use App\Models\Item;
use App\Models\Service;

class TransactionReport
{
    //Fallback if no service label is found
    private const DEFAULT_REPORT_NAME = '';
    private $serviceAlias;
    public $reportName;
    public $filterRoute;
    public $indexRoute;
    public $reportColumns;
    public $filters;

    public function __construct(string $serviceAlias)
    {
        $this -> serviceAlias = $serviceAlias;
        //Set the report name
        $this -> reportName = isset(ConstantHelper::SERVICE_LABEL[$this -> serviceAlias]) ? 
        ConstantHelper::SERVICE_LABEL[$this -> serviceAlias] : self::DEFAULT_REPORT_NAME;
        //Set the report filter route
        $this -> filterRoute = isset(TransactionReportHelper::FILTER_ROUTES[$this -> serviceAlias]) ? 
        TransactionReportHelper::FILTER_ROUTES[$this -> serviceAlias] : '';
        //Set the report filter route
        $this -> indexRoute = isset(TransactionReportHelper::INDEX_ROUTES[$this -> serviceAlias]) ? 
        TransactionReportHelper::INDEX_ROUTES[$this -> serviceAlias] : '';
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

        $filterRoute = route($routeName, $params);
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
        $columns = isset(TransactionReportHelper::TABLE_HEADERS[$this -> serviceAlias]) ?
        TransactionReportHelper::TABLE_HEADERS[$this -> serviceAlias] : [];
        if (in_array($this -> serviceAlias, [ConstantHelper::SO_SERVICE_ALIAS])) {
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
            array_splice($columns, 7, 0, $dynamicFieldsCols);    
        }
        return $columns;
    }
}
