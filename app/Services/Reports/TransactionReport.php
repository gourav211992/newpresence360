<?php

namespace App\Services\Reports;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\TransactionReportHelper;
use App\Models\AttributeGroup;
use App\Models\AuthUser;
use App\Models\Category;
use App\Models\Item;

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
        $this -> reportColumns = isset(TransactionReportHelper::TABLE_HEADERS[$this -> serviceAlias]) ?
        TransactionReportHelper::TABLE_HEADERS[$this -> serviceAlias] : [];
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
}
