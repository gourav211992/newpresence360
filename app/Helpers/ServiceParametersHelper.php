<?php

namespace App\Helpers;

use App\Models\Book;
use App\Models\NumberPattern;
use App\Models\Organization;
use App\Models\OrganizationBookParameter;
use App\Models\OrganizationService;
use App\Models\OrganizationServiceParameter;
use App\Models\Service;
use App\Models\ServiceParameter;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Helpers\PackingList\Constants as PackingListConstants;
use App\Helpers\RGR\Constants as RGRConstants;
use App\Helpers\Inventory\MaterialIssue\Constants as MIConstants;
use App\Helpers\ASN\Constants as ASNConstants;

/**
 * Helper Class containing all logics related to Parameters Functionality in the project.
 */
class ServiceParametersHelper
{
    /**
     * CONSTANTS - DO NOT REMOVE OR MODIFY ANY KEY OR VALUE
     * Parameters with all Applicable/ Possible Values -
     */
    const REFERENCE_FROM_SERVICE_PARAM = 'reference_from_service';
    const PO_PROCUREMENT_TYPE = 'po_procurement_type';
    const PO_PROCUREMENT_TYPE_VALUES = ['Buy', 'Lease', 'All'];

    const SERVICE_ITEM_PARAM = 'service_item';
    const REFERENCE_FROM_SERIES_PARAM = 'reference_from_series';
    const BACK_DATE_ALLOW_PARAM = 'back_date_allowed';
    const BACK_DATE_ALLOW_PARAM_VALUES = ['yes', 'no'];
    const ON_ACCOUNT_REQUIRED_PARAM = 'on_account_required';
    const ON_ACCOUNT_REQUIRED_PARAM_VALUES = ['yes', 'no'];

    const FUTURE_DATE_ALLOW_PARAM = 'future_date_allowed';
    const FUTURE_DATE_ALLOW_PARAM_VALUES = ['yes', 'no'];
    const GOODS_SERVICES_PARAM = 'goods_or_services';
    const GOODS_SERVICES_PARAM_VALUES = ['Goods', 'Service'];
    const ITEM_SUB_TYPE_PARAM = 'item_sub_type';
    const ITEM_SUB_TYPE_PARAM_VALUES = [ConstantHelper::ITEM_SUB_TYPES];
    const GL_POSTING_REQUIRED_PARAM = 'gl_posting_required';
    const GL_POSTING_REQUIRED_PARAM_VALUES = ['yes', 'no'];
    const GL_POSTING_SERIES_PARAM = 'gl_posting_series';
    const CONTRA_POSTING_SERIES_PARAM = 'contra_posting_series';
    const GL_SEPERATE_DISCOUNT_PARAM = 'gl_seperate_discount_posting';
    const GL_SEPERATE_DISCOUNT_PARAM_VALUE = ['yes', 'no'];
    const POST_ON_ARROVE_PARAM = 'post_on_approval';
    const POST_ON_ARROVE_PARAM_VALUES = ['yes', 'no'];
    const TAX_REQUIRED_PARAM = 'tax_required';
    const TAX_REQUIRED_PARAM_VALUES = ['yes', 'no'];
    const BILL_TO_FOLLOW_PARAM = 'bill_to_follow';
    const BILL_TO_FOLLOW_PARAM_VALUES = ['yes', 'no'];
    const INSPECTION_REQUIRED_PARAM = 'inspection_required';
    const INSPECTION_REQUIRED_PARAM_VALUES = ['yes', 'no'];
    const INVOICE_TO_FOLLOW_PARAM = 'invoice_to_follow';
    const INVOICE_TO_FOLLOW_PARAM_VALUES = ['yes', 'no'];
    const BOM_CONSUMPTION_METHOD = 'consumption_method';
    const BOM_CONSUMPTION_METHOD_VALUES = ['manual', 'norms'];
    const BOM_SECTION_REQUIRED = 'section_required';
    const BOM_SECTION_REQUIRED_VALUES = ['yes', 'no'];
    const BOM_SUB_SECTION_REQUIRED = 'sub_section_required';
    const BOM_SUB_SECTION_REQUIRED_VALUES = ['yes', 'no'];
    const BOM_COMPONENT_OVERHEAD_REQUIRED = 'component_overhead_required';
    const BOM_COMPONENT_OVERHEAD_REQUIRED_VALUES = ['yes', 'no'];
    const BOM_BATCH_INHERIT_REQUIRED = 'bacth_inherit_requird';
    const BOM_BATCH_INHERIT_REQUIRED_VALUES = ['yes', 'no'];
    const PR_QTY_TYPE_PARAM = 'pr_qty_type';
    const PR_QTY_TYPE_VALUES = ['rejected', 'accepted', 'all'];
    const ISSUE_TYPE_PARAM = "issue_type";
    const REQUESTER_TYPE_PARAM = "requester_type";
    const ITEM_CODE_TYPE_PARAM = "item_code_type";
    const LEDGER_CODE_TYPE_PARAM = "ledger_code_type";
    const ITEM_CODE_TYPE_PARAM_VALUES = ['Auto', 'Manual'];
    const LEDGER_CODE_TYPE_PARAM_VALUES = ['Auto', 'Manual'];
    const PROCUREMENT_TYPE_PARAM = "procurement_type";
    const PROCUREMENT_TYPE_VALUES = ['Make to order', 'Buy to order', 'All'];
    const ITEM_SERVICE_PARAMETERS = [
        [
            "name" => self::ITEM_CODE_TYPE_PARAM,
            "applicable_values" => self::ITEM_CODE_TYPE_PARAM_VALUES,
            "default_value" => ['Auto'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::COMMON_PARAMETERS
        ],
    ];
    const LEDGER_SERVICE_PARAMETERS = [
        [
            "name" => self::LEDGER_CODE_TYPE_PARAM,
            "applicable_values" => self::LEDGER_CODE_TYPE_PARAM_VALUES,
            "default_value" => ['Auto'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::COMMON_PARAMETERS
        ],
    ];

    const VENDOR_CODE_TYPE_PARAM = "vendor_code_type";
    const VENDOR_CODE_TYPE_PARAM_VALUES = ['Auto', 'Manual'];
    const VENDOR_SERVICE_PARAMETERS = [
        [
            "name" => self::VENDOR_CODE_TYPE_PARAM,
            "applicable_values" => self::VENDOR_CODE_TYPE_PARAM_VALUES,
            "default_value" => ['Auto'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::COMMON_PARAMETERS
        ],
    ];

    const CUSTOMER_CODE_TYPE_PARAM = "customer_code_type";
    const CUSTOMER_CODE_TYPE_PARAM_VALUES = ['Auto', 'Manual'];
    const CUSTOMER_SERVICE_PARAMETERS = [
        [
            "name" => self::CUSTOMER_CODE_TYPE_PARAM,
            "applicable_values" => self::CUSTOMER_CODE_TYPE_PARAM_VALUES,
            "default_value" => ['Auto'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::COMMON_PARAMETERS
        ],
    ];
    const GATE_ENTRY_REQUIRED = "gate_entry_required";
    const GATE_ENTRY_REQUIRED_VALUES = ['yes', 'no'];
    const PARTIAL_DELIVERY_ALLOWED = "partial_delivery_allowed";
    const PARTIAL_DELIVERY_ALLOWED_VALUES = ['yes', 'no'];
    const ISSUE_TYPE_VALUES = ["Location Transfer", "Sub Location Transfer", "Job Work", "Sub Contracting", "Consumption"];
    const REQUESTER_TYPE_VALUES = ["Department", "User"];
    const STATION_WISE_CONSUMPTION = 'station_wise_consumption';
    const STATION_WISE_CONSUMPTION_VALUES = ["yes", "no"];
    const SO_TRACKING_REQUIRED = "so_tracking_required";
    const SO_TRACKING_REQUIRED_VALUES = ['yes', 'no'];
    //Repair Order (RMFG)
    const OK_TO_RECIEVE_BOOK_PARAM = "ok_to_recieve_series";
    /**
     * Constant Array for all Service Parameters
     */
    const SERVICE_PARAMETERS = [
        self::REFERENCE_FROM_SERVICE_PARAM => 'Reference From', //Applied
        self::SERVICE_ITEM_PARAM => 'Service Item', //Applied
        self::REFERENCE_FROM_SERIES_PARAM => 'Reference Series', //Applied
        self::ON_ACCOUNT_REQUIRED_PARAM => 'On Account Required?', //Applied
        self::BACK_DATE_ALLOW_PARAM => 'Back Date Allowed?', //Applied
        self::FUTURE_DATE_ALLOW_PARAM => 'Future Date Allowed?', //Applied
        self::GOODS_SERVICES_PARAM => 'Goods/ Services', //Applied
        self::GL_POSTING_REQUIRED_PARAM => 'Financial Posting Required?', //Applied
        self::GL_SEPERATE_DISCOUNT_PARAM => 'Seperate Discount Posting?', //Applied
        self::GL_POSTING_SERIES_PARAM => 'Voucher Series',
        self::CONTRA_POSTING_SERIES_PARAM => 'Contra Voucher Series', //Applied
        self::POST_ON_ARROVE_PARAM => 'Post on Approval?', //Applied
        self::TAX_REQUIRED_PARAM => 'Tax Required?', //Applied
        self::BILL_TO_FOLLOW_PARAM => 'Bill To Follow', //Applied
        self::INSPECTION_REQUIRED_PARAM => 'Inspection Required', //Applied
        self::INVOICE_TO_FOLLOW_PARAM => 'Invoice To Follow?', //Applied
        self::BOM_CONSUMPTION_METHOD => 'Consumption Calculation Method',
        self::BOM_SECTION_REQUIRED => 'Product Section Required?',
        self::BOM_SUB_SECTION_REQUIRED => 'Product Sub Section Required?',
        self::BOM_COMPONENT_OVERHEAD_REQUIRED => 'Overheads at component level Required?',
        self::PR_QTY_TYPE_PARAM => 'Return Type?',
        self::ISSUE_TYPE_PARAM => 'Issue Type',
        self::GATE_ENTRY_REQUIRED => 'Gate Entry Required?',
        self::PARTIAL_DELIVERY_ALLOWED => 'Partial Delivery Allowed?',
        self::STATION_WISE_CONSUMPTION => 'Station Wise Production Required?',
        self::ITEM_CODE_TYPE_PARAM => 'Item Code',
        self::VENDOR_CODE_TYPE_PARAM => 'Vendor Code',
        self::CUSTOMER_CODE_TYPE_PARAM => 'Customer Code',
        self::LEDGER_CODE_TYPE_PARAM => 'Ledger Code',
        self::REQUESTER_TYPE_PARAM => 'Requester Type',
        self::SO_TRACKING_REQUIRED => 'SO Tracking Required?',
        self::PROCUREMENT_TYPE_PARAM => 'Procurement Type',
        self::BOM_BATCH_INHERIT_REQUIRED => 'Batch Inheritance Required',
        self::PO_PROCUREMENT_TYPE => 'Procurement Type',
        self::OK_TO_RECIEVE_BOOK_PARAM => 'Ok To Receive Series'
    ];

    // Service Parameters Mapping
    const SERVICE_PARAMETERS_VALUES = [
        self::REFERENCE_FROM_SERVICE_PARAM => [], //Applied
        self::SERVICE_ITEM_PARAM => [], //Applied
        self::REFERENCE_FROM_SERIES_PARAM => [], //Applied
        self::BACK_DATE_ALLOW_PARAM => self::BACK_DATE_ALLOW_PARAM_VALUES, //Applied
        self::FUTURE_DATE_ALLOW_PARAM => self::FUTURE_DATE_ALLOW_PARAM_VALUES, //Applied
        self::GOODS_SERVICES_PARAM => self::GOODS_SERVICES_PARAM_VALUES, //Applied
        self::GL_POSTING_REQUIRED_PARAM => self::GL_POSTING_REQUIRED_PARAM_VALUES, //Applied
        self::GL_SEPERATE_DISCOUNT_PARAM => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE, //Applied
        self::GL_POSTING_SERIES_PARAM => [], //Applied
        self::CONTRA_POSTING_SERIES_PARAM => [], //Applied
        self::POST_ON_ARROVE_PARAM => self::POST_ON_ARROVE_PARAM_VALUES, //Applied
        self::TAX_REQUIRED_PARAM => self::TAX_REQUIRED_PARAM_VALUES, //Applied
        self::BILL_TO_FOLLOW_PARAM => self::BILL_TO_FOLLOW_PARAM_VALUES, //Applied
        self::INVOICE_TO_FOLLOW_PARAM => self::INVOICE_TO_FOLLOW_PARAM_VALUES, //Applied
        self::ON_ACCOUNT_REQUIRED_PARAM => self::ON_ACCOUNT_REQUIRED_PARAM_VALUES,
        self::BOM_CONSUMPTION_METHOD => self::BOM_CONSUMPTION_METHOD_VALUES,
        self::BOM_SECTION_REQUIRED => self::BOM_SECTION_REQUIRED_VALUES,
        self::BOM_SUB_SECTION_REQUIRED => self::BOM_SUB_SECTION_REQUIRED_VALUES,
        self::BOM_COMPONENT_OVERHEAD_REQUIRED => self::BOM_COMPONENT_OVERHEAD_REQUIRED_VALUES,
        self::BOM_BATCH_INHERIT_REQUIRED => self::BOM_BATCH_INHERIT_REQUIRED_VALUES,
        self::PR_QTY_TYPE_PARAM => self::PR_QTY_TYPE_VALUES,
        self::ISSUE_TYPE_PARAM => self::ISSUE_TYPE_VALUES,
        self::ITEM_CODE_TYPE_PARAM => self::ITEM_CODE_TYPE_PARAM_VALUES,
        self::LEDGER_CODE_TYPE_PARAM => self::LEDGER_CODE_TYPE_PARAM_VALUES,
        self::VENDOR_CODE_TYPE_PARAM => self::VENDOR_CODE_TYPE_PARAM_VALUES,
        self::CUSTOMER_CODE_TYPE_PARAM => self::CUSTOMER_CODE_TYPE_PARAM_VALUES,
        self::STATION_WISE_CONSUMPTION => self::STATION_WISE_CONSUMPTION_VALUES,
        self::REQUESTER_TYPE_PARAM => self::REQUESTER_TYPE_VALUES,
        self::SO_TRACKING_REQUIRED => self::SO_TRACKING_REQUIRED_VALUES,
        self::PROCUREMENT_TYPE_PARAM => self::PROCUREMENT_TYPE_VALUES,
        self::GATE_ENTRY_REQUIRED => self::GATE_ENTRY_REQUIRED_VALUES,
        self::PARTIAL_DELIVERY_ALLOWED => self::PARTIAL_DELIVERY_ALLOWED_VALUES,
        self::BOM_BATCH_INHERIT_REQUIRED => self::BOM_BATCH_INHERIT_REQUIRED_VALUES,
        self::INSPECTION_REQUIRED_PARAM => self::INSPECTION_REQUIRED_PARAM_VALUES,
        self::OK_TO_RECIEVE_BOOK_PARAM => []
    ];
    const SO_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::SQ_SERVICE_ALIAS, ConstantHelper::PO_SERVICE_ALIAS, ConstantHelper::JO_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::SQ_SERVICE_ALIAS, ConstantHelper::PO_SERVICE_ALIAS, ConstantHelper::JO_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        // [
        //     "name" => self::TAX_REQUIRED_PARAM,
        //     "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
        //     "default_value" => ['yes'],
        //     'is_multiple' => false,
        //     'service_level_visibility' => true
        // ]
    ];


    const TI_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::LR_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::LR_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::SERVICE_ITEM_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => false,
            'service_level_visibility' => false,
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Service'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const MAINT_WO_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => [ConstantHelper::DEFECT_NOTIFICATION, ConstantHelper::EQPT], //All possible values
            "default_value" => [ConstantHelper::DEFECT_NOTIFICATION, ConstantHelper::EQPT], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
          [
            "name" => self::SERVICE_ITEM_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => false,
            'service_level_visibility' => false,
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];

    const FIXED_ASSET_SERVICE_PARAMETERS = [
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],

    ];
    const RC_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0"], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],

        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],

    ];
    const RFQ_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::PI_SERVICE_ALIAS], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],

        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],

    ];
    const PDS_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0"], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],

        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],

    ];
    const TRIP_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::SO_SERVICE_ALIAS], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],

        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
    ];
    const PQ_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::RFQ_SERVICE_ALIAS], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],

        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],

    ];
    const PQC_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::RFQ_SERVICE_ALIAS], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],

        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],

    ];
    const PSV_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0"], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],

        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]

    ];
    const SR_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::SI_SERVICE_ALIAS, ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::SI_SERVICE_ALIAS, ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const PL_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::SO_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::SO_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        // [
        //     "name" => self::GL_POSTING_REQUIRED_PARAM,
        //     "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
        //     "default_value" => ['no'],
        //     'is_multiple' => false,
        //     'service_level_visibility' => true,
        //     'type' => self::GL_PARAMETERS
        // ],
        // [
        //     "name" => self::GL_POSTING_SERIES_PARAM,
        //     "applicable_values" => [],
        //     "default_value" => [],
        //     'is_multiple' => true,
        //     'service_level_visibility' => false,
        //     'type' => self::GL_PARAMETERS
        // ],
        // [
        //     "name" => self::POST_ON_ARROVE_PARAM,
        //     "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
        //     "default_value" => ['no'],
        //     'is_multiple' => false,
        //     'service_level_visibility' => true,
        //     'type' => self::GL_PARAMETERS
        // ]
    ];

    const SQ_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0"], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    const DN_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::SO_SERVICE_ALIAS, PackingListConstants::SERVICE_ALIAS, ConstantHelper::PL_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::SO_SERVICE_ALIAS, PackingListConstants::SERVICE_ALIAS, ConstantHelper::PL_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'is_visible' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        // [
        //     "name" => self::GL_POSTING_REQUIRED_PARAM,
        //     "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
        //     "default_value" => ['no'],
        //     'is_multiple' => false,
        //     'service_level_visibility' => true,
        //     'type' => self::GL_PARAMETERS
        // ],
        // [
        //     "name" => self::GL_POSTING_SERIES_PARAM,
        //     "applicable_values" => [],
        //     "default_value" => [],
        //     'is_multiple' => true,
        //     'service_level_visibility' => false,
        //     'type' => self::GL_PARAMETERS
        // ],
        // [
        //     "name" => self::POST_ON_ARROVE_PARAM,
        //     "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
        //     "default_value" => ['no'],
        //     'is_multiple' => false,
        //     'service_level_visibility' => true,
        //     'type' => self::GL_PARAMETERS
        // ],
        // // [
        // //     "name" => self::TAX_REQUIRED_PARAM,
        // //     "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
        // //     "default_value" => ['yes'],
        // //     'is_multiple' => false,
        // //     'service_level_visibility' => true
        // // ]
    ];
    const SINV_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        // [
        //     "name" => self::GOODS_SERVICES_PARAM,
        //     "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
        //     "default_value" => ['Goods'],
        //     'is_multiple' => false,
        //     'service_level_visibility' => true
        // ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        // [
        //     "name" => self::TAX_REQUIRED_PARAM,
        //     "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
        //     "default_value" => ['yes'],
        //     'is_multiple' => false,
        //     'service_level_visibility' => true
        // ]
    ];
    const SERVICE_INV_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::SO_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::SO_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        // [
        //     "name" => self::GOODS_SERVICES_PARAM,
        //     "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
        //     "default_value" => ['Goods'],
        //     'is_multiple' => false,
        //     'service_level_visibility' => true
        // ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        // [
        //     "name" => self::TAX_REQUIRED_PARAM,
        //     "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
        //     "default_value" => ['yes'],
        //     'is_multiple' => false,
        //     'service_level_visibility' => true
        // ]
    ];

    const LEASE_INV_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => [ConstantHelper::LAND_LEASE], //All possible values
            "default_value" => [ConstantHelper::LAND_LEASE], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Service'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    const LOAN_SERVICE_PARAMETERS = [
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const LOAN_RECOVERY_SERVICE_PARAMETERS = [
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const LOAN_SETTLEMENT_SERVICE_PARAMETERS = [
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const DIS_SERVICE_PARAMETERS = [
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const DN_CUM_INVOICE_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::SO_SERVICE_ALIAS, PackingListConstants::SERVICE_ALIAS, ConstantHelper::PL_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::SO_SERVICE_ALIAS, ConstantHelper::PL_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        // [
        //     "name" => self::TAX_REQUIRED_PARAM,
        //     "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
        //     "default_value" => ['yes'],
        //     'is_multiple' => false,
        //     'service_level_visibility' => true
        // ]
    ];
    /*BOM PO PI*/

    const SCRAP_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
    ];

    /*BOM PO PI*/
    const PI_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::SO_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::SO_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::REQUESTER_TYPE_PARAM,
            "applicable_values" => self::REQUESTER_TYPE_VALUES,
            "default_value" => ['Department'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::SO_TRACKING_REQUIRED,
            "applicable_values" => self::SO_TRACKING_REQUIRED_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::PROCUREMENT_TYPE_PARAM,
            "applicable_values" => self::PROCUREMENT_TYPE_VALUES,
            "default_value" => ['Make to Order'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
    ];
    const BOM_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM,
            "applicable_values" => ["0", ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS],
            "default_value" => ["0", ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS],
            'is_multiple' => true,
            'service_level_visibility' => true,
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::BOM_CONSUMPTION_METHOD,
            "applicable_values" => self::BOM_CONSUMPTION_METHOD_VALUES,
            "default_value" => ['manual'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::BOM_SECTION_REQUIRED,
            "applicable_values" => self::BOM_SECTION_REQUIRED_VALUES,
            "default_value" => ['manual'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::BOM_SUB_SECTION_REQUIRED,
            "applicable_values" => self::BOM_SUB_SECTION_REQUIRED_VALUES,
            "default_value" => ['manual'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::BOM_COMPONENT_OVERHEAD_REQUIRED,
            "applicable_values" => self::BOM_COMPONENT_OVERHEAD_REQUIRED_VALUES,
            "default_value" => ['manual'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::BOM_BATCH_INHERIT_REQUIRED,
            "applicable_values" => self::BOM_BATCH_INHERIT_REQUIRED_VALUES,
            "default_value" => ['manual'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
    ];
    const MO_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM,
            "applicable_values" => [ConstantHelper::PWO_SERVICE_ALIAS],
            "default_value" => [ConstantHelper::PWO_SERVICE_ALIAS],
            'is_multiple' => true,
            'service_level_visibility' => true,
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const COMMON_SERVICE_PARAMETERS = [
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    const PV_SERVICE_PARAMETERS = [
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::ON_ACCOUNT_REQUIRED_PARAM,
            "applicable_values" => self::ON_ACCOUNT_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],

        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::CONTRA_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const RV_SERVICE_PARAMETERS = [
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::ON_ACCOUNT_REQUIRED_PARAM,
            "applicable_values" => self::ON_ACCOUNT_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],

        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const MAINT_BOM_SERVICE_PARAMETERS = [
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
    ];

    const ASSET_POSTING_SERVICE_PARAMETERS = [
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const ASSET_SERVICE_PARAMETERS = [
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ]
    ];
    const SPLIT_SERVICE_PARAMETERS = [
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]

    ];
    const PO_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::PI_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::PI_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GATE_ENTRY_REQUIRED,
            "applicable_values" => self::GATE_ENTRY_REQUIRED_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::PARTIAL_DELIVERY_ALLOWED,
            "applicable_values" => self::PARTIAL_DELIVERY_ALLOWED_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GOODS_SERVICES_PARAM,
            "applicable_values" => self::GOODS_SERVICES_PARAM_VALUES,
            "default_value" => ['Goods'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::PO_PROCUREMENT_TYPE,
            "applicable_values" => self::PO_PROCUREMENT_TYPE_VALUES,
            "default_value" => ['Buy'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        // ,
        // [
        //     "name" => self::INDENT_TOLERANCE_LIMIT_PARAM,
        //     "applicable_values" => self::INDENT_TOLERANCE_LIMIT_VALUES,
        //     "default_value" => [0],
        //     'is_multiple' => false,
        //     'service_level_visibility' => false
        // ]
    ];
    # Job Order
    const JO_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM,
            "applicable_values" => ["0", ConstantHelper::PWO_SERVICE_ALIAS],
            "default_value" => ["0", ConstantHelper::PWO_SERVICE_ALIAS],
            'is_multiple' => true,
            'service_level_visibility' => true,
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GATE_ENTRY_REQUIRED,
            "applicable_values" => self::GATE_ENTRY_REQUIRED_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::PARTIAL_DELIVERY_ALLOWED,
            "applicable_values" => self::PARTIAL_DELIVERY_ALLOWED_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];

    const SUPPLIER_INVOICE_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::PO_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::PO_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    /*BOM PO PI*/

    const GATE_ENTRY_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::PO_SERVICE_ALIAS, ConstantHelper::JO_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::PO_SERVICE_ALIAS, ConstantHelper::JO_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
    ];

    const MRN_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::PO_SERVICE_ALIAS, ConstantHelper::JO_SERVICE_ALIAS, ConstantHelper::SO_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::PO_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::BILL_TO_FOLLOW_PARAM,
            "applicable_values" => self::BILL_TO_FOLLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::INSPECTION_REQUIRED_PARAM,
            "applicable_values" => self::INSPECTION_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    const PB_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => [ConstantHelper::MRN_SERVICE_ALIAS], //All possible values
            "default_value" => [ConstantHelper::MRN_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    const EXPENSE_ADVISE_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::PO_SERVICE_ALIAS, ConstantHelper::JO_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::PO_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    const PURCHASE_RETURN_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::MRN_SERVICE_ALIAS], //All possible values
            "default_value" => ["0", ConstantHelper::MRN_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_SEPERATE_DISCOUNT_PARAM,
            "applicable_values" => self::GL_SEPERATE_DISCOUNT_PARAM_VALUE,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::PR_QTY_TYPE_PARAM,
            "applicable_values" => self::PR_QTY_TYPE_VALUES,
            "default_value" => ['rejected'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];

    const INSPECTION_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => [ConstantHelper::MRN_SERVICE_ALIAS], //All possible values
            "default_value" => [ConstantHelper::MRN_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];

    const PUTAWAY_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => [ConstantHelper::MRN_SERVICE_ALIAS], //All possible values
            "default_value" => [ConstantHelper::MRN_SERVICE_ALIAS], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];

    const MATERIAL_ISSUE_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0"], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    const STOCK_ADJUSTMENT_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0"], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    const PHYSICAL_STOCK_TAKE_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0"], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    const COMMERCIAL_BOM_SERVICE_PARAMETERS = [
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::BOM_CONSUMPTION_METHOD,
            "applicable_values" => self::BOM_CONSUMPTION_METHOD_VALUES,
            "default_value" => ['manual'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::BOM_SECTION_REQUIRED,
            "applicable_values" => self::BOM_SECTION_REQUIRED_VALUES,
            "default_value" => ['manual'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::BOM_SUB_SECTION_REQUIRED,
            "applicable_values" => self::BOM_SUB_SECTION_REQUIRED_VALUES,
            "default_value" => ['manual'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::BOM_COMPONENT_OVERHEAD_REQUIRED,
            "applicable_values" => self::BOM_COMPONENT_OVERHEAD_REQUIRED_VALUES,
            "default_value" => ['manual'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];

    const MATERIAL_REQUEST_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0"], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];

    const PWO_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM,
            "applicable_values" => ["0", ConstantHelper::SO_SERVICE_ALIAS],
            "default_value" => ["0"],
            'is_multiple' => true,
            'service_level_visibility' => true,
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    const TR_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0"], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::TAX_REQUIRED_PARAM,
            "applicable_values" => self::TAX_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ]
    ];
    /*mrn PB EXPENSE_ADVISE*/
    const MR_SERVICE_PARAMETERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME], //All possible values
            "default_value" => ["0", ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::REQUESTER_TYPE_PARAM,
            "applicable_values" => self::REQUESTER_TYPE_VALUES,
            "default_value" => ['Department'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
    ];
    const PSLIP_SERVICE_PARAMTERS = [
        [
            "name" => self::REFERENCE_FROM_SERVICE_PARAM, //Name of the parameter
            "applicable_values" => ["0", ConstantHelper::MO_SERVICE_ALIAS], //All possible values
            "default_value" => ["0"], //Default selected value(s)
            'is_multiple' => true, // Whether or not to allow multiple selection
            'service_level_visibility' => true, // Whether or not to show this parameter in UI
        ],
        [
            "name" => self::REFERENCE_FROM_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false
        ],
        [
            "name" => self::BACK_DATE_ALLOW_PARAM,
            "applicable_values" => self::BACK_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::FUTURE_DATE_ALLOW_PARAM,
            "applicable_values" => self::FUTURE_DATE_ALLOW_PARAM_VALUES,
            "default_value" => ['yes'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
        [
            "name" => self::GL_POSTING_REQUIRED_PARAM,
            "applicable_values" => self::GL_POSTING_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::GL_POSTING_SERIES_PARAM,
            "applicable_values" => [],
            "default_value" => [],
            'is_multiple' => true,
            'service_level_visibility' => false,
            'type' => self::GL_PARAMETERS
        ],
        [
            "name" => self::POST_ON_ARROVE_PARAM,
            "applicable_values" => self::POST_ON_ARROVE_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true,
            'type' => self::GL_PARAMETERS
        ],

        //Deepak
        [
            "name" => self::INSPECTION_REQUIRED_PARAM,
            "applicable_values" => self::INSPECTION_REQUIRED_PARAM_VALUES,
            "default_value" => ['no'],
            'is_multiple' => false,
            'service_level_visibility' => true
        ],
    ];
    const APPLICABLE_SERVICE_PARAMETERS = [
        ConstantHelper::MAINT_WO=>self::MAINT_WO_SERVICE_PARAMETERS,
        ConstantHelper::TI_SERVICE_ALIAS => self::TI_SERVICE_PARAMETERS,
        ConstantHelper::SO_SERVICE_ALIAS => self::SO_SERVICE_PARAMETERS,
        ConstantHelper::SQ_SERVICE_ALIAS => self::SQ_SERVICE_PARAMETERS,
        ConstantHelper::SR_SERVICE_ALIAS => self::SR_SERVICE_PARAMETERS,
        ConstantHelper::PWO_SERVICE_ALIAS => self::PWO_SERVICE_PARAMETERS,
        ConstantHelper::TR_SERVICE_ALIAS => self::TR_SERVICE_PARAMETERS,
        ConstantHelper::RC_SERVICE_ALIAS => self::RC_SERVICE_PARAMETERS,
        ConstantHelper::RFQ_SERVICE_ALIAS => self::RFQ_SERVICE_PARAMETERS,
        ConstantHelper::PDS_SERVICE_ALIAS => self::PDS_SERVICE_PARAMETERS,
        ConstantHelper::TRIP_SERVICE_ALIAS => self::TRIP_SERVICE_PARAMETERS,
        ConstantHelper::PQ_SERVICE_ALIAS => self::PQ_SERVICE_PARAMETERS,
        ConstantHelper::PQC_SERVICE_ALIAS => self::PQC_SERVICE_PARAMETERS,
        ConstantHelper::PSV_SERVICE_ALIAS => self::PSV_SERVICE_PARAMETERS,
        ConstantHelper::PL_SERVICE_ALIAS => self::PL_SERVICE_PARAMETERS,

        ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS => self::DN_SERVICE_PARAMETERS,
        ConstantHelper::SI_SERVICE_ALIAS => self::SINV_SERVICE_PARAMETERS,
        ConstantHelper::SERVICE_INV_SERVICE_ALIAS => self::SERVICE_INV_SERVICE_PARAMETERS,
        ConstantHelper::LEASE_INVOICE_SERVICE_ALIAS => self::LEASE_INV_SERVICE_PARAMETERS,
        ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS => self::DN_CUM_INVOICE_SERVICE_PARAMETERS,
        ConstantHelper::PI_SERVICE_ALIAS => self::PI_SERVICE_PARAMETERS,
        ConstantHelper::SCRAP_SERVICE_ALIAS => self::SCRAP_SERVICE_PARAMETERS,
        ConstantHelper::BOM_SERVICE_ALIAS => self::BOM_SERVICE_PARAMETERS,
        ConstantHelper::MO_SERVICE_ALIAS => self::MO_SERVICE_PARAMETERS,
        ConstantHelper::PAYMENT_VOUCHER_RECEIPT => self::PV_SERVICE_PARAMETERS,
        ConstantHelper::PAYMENTS_SERVICE_ALIAS => self::PV_SERVICE_PARAMETERS,
        ConstantHelper::RECEIPTS_SERVICE_ALIAS => self::RV_SERVICE_PARAMETERS,
        ConstantHelper::FIXED_ASSET_DEPRECIATION => self::ASSET_SERVICE_PARAMETERS,
        ConstantHelper::FIXED_ASSET_SPLIT => self::ASSET_POSTING_SERVICE_PARAMETERS,
        ConstantHelper::FIXED_ASSET_MERGER => self::ASSET_POSTING_SERVICE_PARAMETERS,
        ConstantHelper::FIXED_ASSET_REV_IMP => self::ASSET_POSTING_SERVICE_PARAMETERS,
        ConstantHelper::MAINT_BOM => self::MAINT_BOM_SERVICE_PARAMETERS,
        ConstantHelper::PO_SERVICE_ALIAS => self::PO_SERVICE_PARAMETERS,
        ConstantHelper::FIXEDASSET => self::ASSET_POSTING_SERVICE_PARAMETERS,
        ConstantHelper::GATE_ENTRY_SERVICE_ALIAS => self::GATE_ENTRY_SERVICE_PARAMETERS,
        ConstantHelper::SUPPLIER_INVOICE_SERVICE_ALIAS => self::SUPPLIER_INVOICE_SERVICE_PARAMETERS,
        ConstantHelper::MRN_SERVICE_ALIAS => self::MRN_SERVICE_PARAMETERS,
        ConstantHelper::PB_SERVICE_ALIAS => self::PB_SERVICE_PARAMETERS,
        ConstantHelper::INSPECTION_SERVICE_ALIAS => self::INSPECTION_SERVICE_PARAMETERS,
        ConstantHelper::PUTAWAY_SERVICE_ALIAS => self::PUTAWAY_SERVICE_PARAMETERS,
        ConstantHelper::EXPENSE_ADVISE_SERVICE_ALIAS => self::EXPENSE_ADVISE_SERVICE_PARAMETERS,
        ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS => self::PURCHASE_RETURN_SERVICE_PARAMETERS,
        ConstantHelper::MATERIAL_REQUEST_SERVICE_ALIAS => self::MATERIAL_REQUEST_SERVICE_PARAMETERS,
        ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS => self::MATERIAL_ISSUE_SERVICE_PARAMETERS,
        ConstantHelper::STOCK_ADJUSTMENT_SERVICE_ALIAS => self::STOCK_ADJUSTMENT_SERVICE_PARAMETERS,
        ConstantHelper::PHYSICAL_STOCK_TAKE_SERVICE_ALIAS => self::PHYSICAL_STOCK_TAKE_SERVICE_PARAMETERS,
        ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS => self::COMMERCIAL_BOM_SERVICE_PARAMETERS,
        ConstantHelper::JO_SERVICE_ALIAS => self::JO_SERVICE_PARAMETERS,
        ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS => self::PSLIP_SERVICE_PARAMTERS,
        ConstantHelper::HOMELOAN => self::LOAN_SERVICE_PARAMETERS,
        ConstantHelper::TERMLOAN => self::LOAN_SERVICE_PARAMETERS,
        ConstantHelper::VEHICLELOAN => self::LOAN_SERVICE_PARAMETERS,
        ConstantHelper::LOAN_DISBURSEMENT => self::DIS_SERVICE_PARAMETERS,
        ConstantHelper::LOAN_RECOVERY => self::LOAN_RECOVERY_SERVICE_PARAMETERS,
        ConstantHelper::LOAN_SETTLEMENT => self::LOAN_SETTLEMENT_SERVICE_PARAMETERS,
        ConstantHelper::LAND_LEASE => self::COMMON_SERVICE_PARAMETERS,
        ConstantHelper::PURCHASE_VOUCHER => self::COMMON_SERVICE_PARAMETERS,
        ConstantHelper::SALES_VOUCHER => self::COMMON_SERVICE_PARAMETERS,
        ConstantHelper::RECEIPT_VOUCHER => self::COMMON_SERVICE_PARAMETERS,
        ConstantHelper::PAYMENT_VOUCHER => self::COMMON_SERVICE_PARAMETERS,
        ConstantHelper::DEBIT_Note => self::COMMON_SERVICE_PARAMETERS,
        ConstantHelper::CREDIT_Note => self::COMMON_SERVICE_PARAMETERS,
        ConstantHelper::JOURNAL_VOUCHER => self::COMMON_SERVICE_PARAMETERS,
        ConstantHelper::CONTRA_VOUCHER => self::COMMON_SERVICE_PARAMETERS,
        ConstantHelper::OPENING_BALANCE => self::COMMON_SERVICE_PARAMETERS,
        MIConstants::SERVICE_ALIAS => MIConstants::SERVICE_PARAMETERS,
        ConstantHelper::ITEM_SERVICE_ALIAS => self::ITEM_SERVICE_PARAMETERS,
        ConstantHelper::LEDGERS_SERVICE_ALIAS => self::LEDGER_SERVICE_PARAMETERS,
        ConstantHelper::VENDOR_SERVICE_ALIAS => self::VENDOR_SERVICE_PARAMETERS,
        ConstantHelper::CUSTOMER_SERVICE_ALIAS => self::CUSTOMER_SERVICE_PARAMETERS,
        ConstantHelper::MATERIAL_RETURN_SERVICE_ALIAS_NAME => self::MR_SERVICE_PARAMETERS,
        PackingListConstants::SERVICE_ALIAS => PackingListConstants::PARAMETERS,
        ASNConstants::SERVICE_ALIAS => ASNConstants::PARAMETERS,
        RGRConstants::SERVICE_ALIAS => RGRConstants::PARAMETERS,
    ];
    /* Parameter Types*/
    const COMMON_PARAMETERS = 'co';
    const GL_PARAMETERS = 'gl';
    const PARAMETER_TYPES = [self::COMMON_PARAMETERS, self::GL_PARAMETERS];

    /* Function to get book level parameters for configuration*/
    public static function getBookLevelParameterValue(string $parameterName, int $bookId): array
    {
        //Get raw book parameters from database
        $bookParameter = OrganizationBookParameter::where('book_id', $bookId)->where('parameter_name', $parameterName)
            ->where('status', ConstantHelper::ACTIVE)->first();
        $parameters = [];
        if (isset($bookParameter)) {
            //REFERENCE FROM CASE
            if ($parameterName === self::REFERENCE_FROM_SERVICE_PARAM) {
                $services = Service::whereIn('id', $bookParameter->parameter_value)->get();
                foreach ($services as $service) {
                    array_push($parameters, $service->alias);
                }
                //Assign a Default D in values for Direct Doc creation
                if (in_array(0, $bookParameter->parameter_value)) {
                    array_push($parameters, 'd');
                }
                return [
                    'status' => true,
                    'message' => 'Parameter found',
                    'data' => $parameters
                ];
            } else if ($parameterName === self::REFERENCE_FROM_SERIES_PARAM) {
                $books = Book::withDefaultGroupCompanyOrg()->whereIn('id', $bookParameter->parameter_value)->get();
                foreach ($books as $service) {
                    array_push($parameters, $service->book_code);
                }
                return [
                    'status' => true,
                    'message' => 'Parameter found',
                    'data' => $parameters
                ];
            } else if ($parameterName === self::SERVICE_ITEM_PARAM) {
                $books = Book::withDefaultGroupCompanyOrg()->whereIn('id', $bookParameter->parameter_value)->get();
                foreach ($books as $service) {
                    array_push($parameters, $service->book_code);
                }
                return [
                    'status' => true,
                    'message' => 'Parameter found',
                    'data' => $parameters
                ];
            } else {
                return [
                    'status' => true,
                    'message' => 'Parameter found',
                    'data' => $bookParameter->parameter_value
                ];
            }
            //Parameters not Found
        } else {
            return [
                'status' => false,
                'message' => 'Parameter not found',
                'data' => []
            ];
        }
    }

    /* Function to get book ids applicable for pulling in */
    public static function getBookCodesForReferenceFromParam(int $bookId): array
    {
        $bookParameter = OrganizationBookParameter::where('book_id', $bookId)->where('parameter_name', self::REFERENCE_FROM_SERIES_PARAM)->where('status', ConstantHelper::ACTIVE)->first();
        if (isset($bookParameter)) {
            $books = Book::select('id')->whereIn('id', $bookParameter->parameter_value)->get()->pluck('id')->toArray();
            return $books;
        } else {
            return [];
        }
    }

    /* Function to get service level parameters with their default and appliacable values converted in a useable format */
    public static function getDefinedServiceLevelParameters(string $serviceAlias): array
    {
        $applicableParameters = isset(self::APPLICABLE_SERVICE_PARAMETERS[$serviceAlias]) ? self::APPLICABLE_SERVICE_PARAMETERS[$serviceAlias] : [];
        $service = Service::where('alias', $serviceAlias)->first();
        //Loop through parameters and modify values
        foreach ($applicableParameters as &$parameter) {
            $parameter['type'] = isset($parameter['type']) ? $parameter['type'] : self::COMMON_PARAMETERS; //Assign type (GL or common)
            if ($parameter['name'] === self::REFERENCE_FROM_SERVICE_PARAM) {
                //REFERENCE PARAMETER CASE (Get other services)
                $serviceAliases = $parameter['applicable_values'];
                $services = Service::select('id', 'alias', 'name')->whereIn('alias', $serviceAliases)->get();
                $formattedValues = [];
                //Assign a Direct Option
                if (in_array("0", $serviceAliases)) {
                    array_push($formattedValues, [
                        'label' => 'Direct',
                        'value' => "0"
                    ]);
                }
                foreach ($services as $serviceVal) {
                    array_push($formattedValues, [
                        'label' => $serviceVal->name,
                        'value' => $serviceVal->id
                    ]);
                }
                $parameter['applicable_values'] = $formattedValues;
            } else {
                $formattedValues = [];
                foreach ($parameter['applicable_values'] as $applicableVal) {
                    array_push($formattedValues, [
                        'label' => ucfirst($applicableVal),
                        'value' => $applicableVal
                    ]);
                }
                $parameter['applicable_values'] = $formattedValues;
            }
            //Assign a key for storing in Database
            $parameter['applicable_values_database'] = count($formattedValues) > 0 ? array_column($formattedValues, 'value') : [];
            //Assign Default parameter from database only if exists
            if (isset($service)) {
                $serviceLevelParam = ServiceParameter::where('service_id', $service->id)->where('name', $parameter['name'])->first();
                if (isset($serviceLevelParam)) {
                    //Modify the default value for REFERENCE FROM Param only
                    if ($parameter['name'] === self::REFERENCE_FROM_SERVICE_PARAM) {
                        $formattedDefaultValue = [];
                        $dFServices = Service::select('id', 'alias', 'name')->whereIn('id', $serviceLevelParam->default_value)->get();
                        if (in_array(0, $serviceLevelParam->default_value)) {
                            array_push($formattedDefaultValue, "0");
                        }
                        foreach ($dFServices as $dFService) {
                            array_push($formattedDefaultValue, $dFService->id);
                        }
                        $parameter['default_value'] = $formattedDefaultValue;
                    } else {
                        $parameter['default_value'] = $serviceLevelParam->default_value;
                    }
                } else {
                    //Modify the default value for REFERENCE FROM Param only
                    if ($parameter['name'] === self::REFERENCE_FROM_SERVICE_PARAM) {
                        $formattedDefaultValue = [];
                        $dFServices = Service::select('id', 'alias', 'name')->whereIn('alias', $parameter['default_value'])->get();
                        if (in_array(0, $parameter['default_value'])) {
                            array_push($formattedDefaultValue, "0");
                        }
                        foreach ($dFServices as $dFService) {
                            array_push($formattedDefaultValue, $dFService->id);
                        }
                        $parameter['default_value'] = $formattedDefaultValue;
                    }
                }
            }
        }
        return $applicableParameters;
    }

    /*
    Script Function to sync the service parameters at Organization level
    NOTE - Use within a Transaction
    */
    public static function enableServiceParametersForOrganization(int $serviceId, int $organizationId): array
    {
        $service = Service::find($serviceId);
        if (!isset($service)) {
            return [
                'status' => false,
                'message' => 'Service Not Found'
            ];
        }
        //Get Service Parameters from Database
        $serviceParameters = $service->parameters;
        $organization = Organization::find($organizationId);
        if (!isset($organization)) {
            return [
                'status' => false,
                'message' => 'Organization Not Found'
            ];
        }
        //Array to keep track of newly created or updated parameters
        $insertedOrgServiceParamIds = [];
        //Create or Update Organization Service Parameter
        foreach ($serviceParameters as $serviceParam) {
            $orgServiceParameter = OrganizationServiceParameter::where([
                ['group_id', $organization->group_id],
                ['service_id', $service->id],
                ['service_param_id', $serviceParam->id],
                ['parameter_name', $serviceParam->name]
            ])->first();
            //Create
            if (!isset($orgServiceParameter)) {
                $orgServiceParameter = OrganizationServiceParameter::create([
                    'group_id' => $organization->group_id,
                    'company_id' => null, // Need to change later
                    'organization_id' => null, // Need to change later
                    'service_id' => $service->id,
                    'service_param_id' => $serviceParam->id,
                    'parameter_name' => $serviceParam->name,
                    'parameter_value' => $serviceParam->default_value,
                    'type' => $serviceParam->type,
                    'status' => ConstantHelper::ACTIVE,
                ]);
            } else { // Update only parameter value and type
                $orgServiceParameter->parameter_value = $serviceParam->default_value;
                $orgServiceParameter->type = $serviceParam->type;
                $orgServiceParameter->service_param_id = $serviceParam->id;
                $orgServiceParameter->save();
            }
            array_push($insertedOrgServiceParamIds, $orgServiceParameter->id);
        }
        //Delete the records which are not required
        OrganizationServiceParameter::where([
            ['group_id', $organization->group_id],
            ['service_id', $service->id],
        ])->whereNotIn('id', $insertedOrgServiceParamIds)->delete();
        //Retrieve organization service if exists else create it
        $orgService = OrganizationService::where('group_id', $organization->group_id)->where('service_id', $serviceId)->first();
        if (!isset($orgService)) {
            $orgService = OrganizationService::create([
                'organization_id' => null,
                'company_id' => null,
                'group_id' => $organization->group_id,
                'service_id' => $serviceId,
                'name' => $service->name,
                'alias' => $service->alias
            ]);
        } else {
            $orgService->name = $service->name;
            $orgService->alias = $service->alias;
            $orgService->save();
        }
        //Check for any existing book in Group/ Organization
        $existingBook = Book::where([
            ['group_id', $organization->group_id],
            ['org_service_id', $orgService->id]
        ])->first();
        if (!isset($existingBook)) {
            //Assign a default Book with parameters and auto doc creation
            $book = Book::create([
                'org_service_id' => $orgService->id,
                'service_id' => $orgService?->service?->id,
                'book_code' => strtoupper($service->alias), // CHECK AGAIN
                'book_name' => $service->name, // CHECK AGAIN
                'status' => ConstantHelper::ACTIVE,
                'group_id' => $organization->group_id,
                'company_id' => null,
                'organization_id' => null
            ]);
            if ($service->type === ConstantHelper::ERP_TRANSACTION_SERVICE_TYPE) {
                NumberPattern::create([
                    'book_id' => $book->id,
                    'company_id' => $organization->company_id,
                    'organization_id' => $organization->id,
                    'series_numbering' => ConstantHelper::DOC_NO_TYPE_AUTO,
                    'reset_pattern' => ConstantHelper::DOC_RESET_PATTERN_NEVER,
                    'prefix' => null,
                    'starting_no' => 1,
                    'suffix' => null,
                    'current_no' => 0
                ]);
            }
            //Create Book Level Parmeters also
            $orgServiceParams = OrganizationServiceParameter::where('group_id', $organization->group_id)->where('service_id', $service->id)->get();
            foreach ($orgServiceParams as $orgServiceParam) {
                if ($orgServiceParam->parameter_name === self::REFERENCE_FROM_SERVICE_PARAM) {
                    $serviceIds = Service::select('id', 'alias', 'name')->whereIn('id', $orgServiceParam->parameter_value)->get()->pluck('id')->toArray();
                    $defaultVal = $serviceIds;
                    if (in_array("0", $orgServiceParam->parameter_value)) {
                        array_push($defaultVal, "0");
                    }
                } else if ($orgServiceParam->parameter_name === self::REFERENCE_FROM_SERIES_PARAM) {
                    //Get Service Ids for getting referenced books
                    $serviceIds = $orgServiceParams->firstWhere('parameter_name', self::REFERENCE_FROM_SERVICE_PARAM);
                    if (isset($serviceIds)) {
                        $serviceIds = $serviceIds->parameter_value;
                    } else {
                        $serviceIds = [];
                    }
                    //Special Conditions for INVOICE, DELIVERY NOTE AND INVOICE CUM DELIVERY NOTE (More can be added here)
                    $defaultVal = self::getAvailableReferenceSeries($orgServiceParam->service_id, $serviceIds, 0, true);
                } else {
                    $defaultVal = $orgServiceParam->parameter_value;
                }
                OrganizationBookParameter::create([
                    'book_id' => $book->id,
                    'group_id' => $organization->group_id,
                    'company_id' => null,
                    'organization_id' => null,
                    'org_service_id' => $orgService->id,
                    'service_param_id' => $orgServiceParam->service_param_id,
                    'parameter_name' =>  $orgServiceParam->parameter_name,
                    'parameter_value' => $defaultVal,
                    'type' => $orgServiceParam->type,
                    'status' => ConstantHelper::ACTIVE,
                ]);
            }
            //Financial Service Book Setup (If Required)
            if ($service->financial_service_alias) {
                //Check if the Financial Service Alias is setup or not
                $financialService = Service::where('alias', $service->financial_service_alias)->first();
                if (!isset($financialService)) {
                    return [
                        'status' => false,
                        'message' => 'Financial Service not setup'
                    ];
                }
                //Check if the financial service is assigned to the organization
                $orgFinancialService = OrganizationService::where('alias', $service->financial_service_alias)
                    ->where('group_id', $organization->group_id)->first();
                if (!isset($orgFinancialService)) {
                    return [
                        'status' => false,
                        'message' => 'Financial Service not setup for this Group'
                    ];
                }
                //Create Financial Book
                Book::create([
                    'org_service_id' => $orgFinancialService->id,
                    'service_id' => $financialService->id,
                    'book_code' => strtoupper($service->alias), // CHECK AGAIN
                    'book_name' => $service->name, // CHECK AGAIN
                    'status' => ConstantHelper::ACTIVE,
                    'group_id' => $organization->group_id,
                    'company_id' => null,
                    'organization_id' => null,
                    'manual_entry' => 0
                ]);
            }
        } else {
            //Update all existing books with new parameters (if addded)
            $books = Book::withDefaultGroupCompanyOrg()->where('org_service_id', $orgService->id)->get();
            foreach ($books as $book) {
                $referenceFrom = $serviceParameters->firstWhere('name', self::REFERENCE_FROM_SERVICE_PARAM)?->default_value;
                $insertedBookParameterIds = [];
                foreach ($serviceParameters as $serviceParam) {
                    $bookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', $serviceParam->name)->first();
                    if (!isset($bookParam)) {
                        $defaultValue = $serviceParam->default_value;
                        if (isset($referenceFrom)) {
                            if ($serviceParam->name === self::REFERENCE_FROM_SERIES_PARAM) {
                                foreach ($referenceFrom as $ref) {
                                    if ($ref != 0) {
                                        $service = Service::find($ref);
                                        $referencedBook = Book::where('group_id', $organization->group_id)->where(DB::raw('UPPER(book_code)'), strtoupper($service?->alias))->first();
                                        if (isset($referencedBook)) {
                                            array_push($defaultValue, $referencedBook->id);
                                        }
                                    }
                                }
                            }
                        }
                        $bookParam = OrganizationBookParameter::create([
                            'group_id' => $organization->group_id,
                            'company_id' => null,
                            'organization_id' => null,
                            'book_id' => $book->id,
                            'org_service_id' => $orgService->id,
                            'service_param_id' => $serviceParam->id,
                            'parameter_name' => $serviceParam->name,
                            'parameter_value' => $defaultValue,
                            'type' => $serviceParam->type,
                            'status' => ConstantHelper::ACTIVE,
                        ]);
                    } else {
                        // Update only parameter value and type
                        $bookParam->type = $serviceParam->type;
                        $bookParam->parameter_name = $serviceParam->name;
                        $bookParam->save();
                    }
                    //Push the inserted or updated book param id
                    array_push($insertedBookParameterIds, $bookParam->id);
                }
                //Delete the records which are not required now
                OrganizationBookParameter::where('book_id', $book->id)->whereNotIn('id', $insertedBookParameterIds)->delete();
            }
        }
        return [
            'status' => true,
            'message' => 'Organization Service Parameters Synced'
        ];
    }

    /*Return the series/ book available for pulling -> Only those series which have not been referenced in any book parameter will come*/
    public static function getAvailableReferenceSeries(int $sourceServiceId, array $serviceIds, int $editBookId = 0, bool $pluck = false): EloquentCollection|array
    {

        //Get all bookIds according to service
        $bookIds =  Book::withDefaultGroupCompanyOrg()->whereHas('org_service', function ($serviceQuery) use ($serviceIds) {
            $serviceQuery->whereIn('service_id', $serviceIds);
        })->get()->pluck('id')->toArray();

        $sourceService = Service::find($sourceServiceId);
        $nonReferencedBookIds = [];
        $invoiceServices = [
            // ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS,
            // ConstantHelper::SI_SERVICE_ALIAS,
            // ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS
        ];
        //Check each book id for it's reference
        foreach ($bookIds as $bookId) {
            $sourceServiceIds = [$sourceServiceId];
            if (isset($sourceService)) {
                //Condition for invoice services
                // if (in_array($sourceService -> alias, $invoiceServices))
                // {
                //     $serviceIds = Service::whereIn('alias', $invoiceServices) -> get() -> pluck('id') -> toArray();
                //     foreach ($serviceIds as $serviceId) {
                //         array_push($sourceServiceIds, $serviceId);
                //     }
                // }
                $isReferenced = OrganizationBookParameter::whereHas('org_service', function ($orgServiceQuery) use ($sourceServiceIds) {
                    $orgServiceQuery->whereIn('service_id', $sourceServiceIds);
                })->where('parameter_name', ServiceParametersHelper::REFERENCE_FROM_SERIES_PARAM)
                    ->when($editBookId, function ($editQuery) use ($editBookId) {
                        $editQuery->where('book_id', '!=', $editBookId);
                    })->where('org_service_id', $sourceService->id)->whereJsonContains('parameter_value', (string)$bookId)->first();
                if (!isset($isReferenced)) {
                    array_push($nonReferencedBookIds, $bookId);
                    //Check for sales invoice
                    // if ($sourceService -> alias === ConstantHelper::SI_SERVICE_ALIAS) {
                    //     $invoiceToFollowParam = OrganizationBookParameter::where('book_id', $bookId)
                    //     -> where('parameter_name', ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM)-> first();
                    //     if (isset($invoiceToFollowParam) && ($invoiceToFollowParam ?-> parameter_value[0] == 'yes')) {
                    //         array_push($nonReferencedBookIds, $bookId);
                    //     }
                    // } else if ($sourceService -> alias === ConstantHelper::PB_SERVICE_ALIAS) {
                    //     $billToFollowParam = OrganizationBookParameter::where('book_id', $bookId)
                    //     -> where('parameter_name', ServiceParametersHelper::BILL_TO_FOLLOW_PARAM)-> first();
                    //     if (isset($billToFollowParam) && ($billToFollowParam ?-> parameter_value[0] == 'yes')) {
                    //         array_push($nonReferencedBookIds, $bookId);
                    //     }
                    // } else {
                    //     array_push($nonReferencedBookIds, $bookId);
                    // }
                }
            }
        }
        //return all the non referenced books
        $books =  Book::withDefaultGroupCompanyOrg()->whereIn('id', $nonReferencedBookIds);
        if ($pluck) {
            $books = $books->get()->pluck('id')->toArray();
        } else {
            $books = $books->get();
        }
        return $books;
    }

    public static function getFinancialServiceAlias(string $serviceAlias): string|null
    {
        if (isset(ConstantHelper::OPERATION_FINANCIAL_SERVICES_MAPPING[$serviceAlias])) {
            return ConstantHelper::OPERATION_FINANCIAL_SERVICES_MAPPING[$serviceAlias];
        } else {
            return null;
        }
    }
    public static function getFinancialService(string $serviceAlias): string|null
    {
        $financialServiceAlias = self::getFinancialServiceAlias($serviceAlias);
        if (isset($financialServiceAlias)) {
            $financialService = Service::where('alias', $financialServiceAlias)->first();
            if (isset($financialService)) {
                return $financialService->name . " - " . $financialService->alias;
            } else {
                return $financialServiceAlias;
            }
        } else {
            return null;
        }
    }
}
