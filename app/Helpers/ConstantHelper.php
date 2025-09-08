<?php

namespace App\Helpers;

use App\Helpers\PackingList\Constants as PackingListConstants;
use App\Helpers\ASN\Constants as ASNConstant;
use App\Helpers\RGR\Constants as RGRConstant;
use App\Models\Legal;

class ConstantHelper
{
    // Vendor Status
    const IAM_VENDOR_USER = 'IAM-VENDOR';
    const IAM_ROOT_USER = 'IAM-ROOT';
    const IAM_SUPER_ADMIN = 'IAM-SUPER';
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const PENDING = 'pending';
    const GENERATED = 'generated';
    const WON = 'won';
    const LOST = 'lost';
    const CREDIT = 'CR';
    const DEBIT = 'DR';

    const CLIENT_ID = 'e_invoice_client_id';
    const CLIENT_SECRET = 'e_invoice_client_secret';
    const CLIENT_USERNAME = 'e_invoice_client_username';
    const CLIENT_PASSWORD = 'e_invoice_client_password';
    const CLIENT_ACCESS_TOKEN = 'e_invoice_acess_token';

    const ERP_CUSTOMER_STATUS = [
        self::ACTIVE,
        self::INACTIVE,
    ];


    const STATUS = [
        self::ACTIVE,
        self::INACTIVE,
    ];

    const USER_STATUS = [
        self::ACTIVE,
        self::INACTIVE,
        self::DRAFT,
    ];
    // Document Status
    const REVOKE = 'revoke';
    const CANCEL = 'cancel';
    const DRAFT = 'draft';
    const SUBMITTED = 'submitted';
    const DISBURSED = 'Disbursed';

    const COMPLETED = 'completed';
    const SHORTLISTED = 'shortlisted';
    const CANCELLED = 'cancelled';
    const CONFIRMED = 'confirmed';
    const CLOSED = 'closed';
    const APPROVAL_NOT_REQUIRED = 'approval_not_required';
    const APPROVAL = 'approval';
    const ACCEPTED = 'accepted';
    const ALL = 'all';
    const PARTIALLY_APPROVED = 'partially_approved';
    // const REVOKE = 'revoke';

    const ASSIGNED = 'assigned';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const APPRAISAL = 'appraisal';
    const ASSESSMENT = 'assessment';
    const LEASE = 'Lease Rent';
    const LEASE_SERVICE_TYPE_NAME = "Lease";
    const SECURITY_DEPOSIT = 'Security Deposit';
    const LEASE_SERVICE_TYPE = [self::LEASE_SERVICE_TYPE_NAME,"Land-Lease"];
    const ASSESSED = 'Assessed';
    const SANCTIONED = 'sanctioned';
    const POST = 'post';
    const PROCESSING_FEE = 'processingfee';
    const REQUEST = 'Requested';
    const PROCESSFEEINCOMEACC = 'Processing Fee Income Account';
    const POSTED = 'posted';
    const PAYABLE='Account Payable';
    const RECEIVABLE = 'Account Receivable';
    const DOCUMENT_STATUS = [self::DRAFT, self::SUBMITTED, self::APPROVAL_NOT_REQUIRED, self::PARTIALLY_APPROVED, self::APPROVED, self::REJECTED];

    const DOCUMENT_STATUS_CSS = [self::DRAFT => 'text-warning', self::SUBMITTED => 'text-primary', self::APPROVAL_NOT_REQUIRED => 'text-success', self::PARTIALLY_APPROVED => 'text-warning', self::APPROVED => 'text-success', self::REJECTED => 'text-danger', self::POSTED => 'text-primary-new',self::COMPLETED => 'text-warning'];

    const DOCUMENT_STATUS_CSS_WO_TEXT = [self::DRAFT => 'warning', self::SUBMITTED => 'primary', self::APPROVAL_NOT_REQUIRED => 'success', self::PARTIALLY_APPROVED => 'warning', self::APPROVED => 'success', self::REJECTED => 'danger', self::POSTED => 'info'];

    const DOCUMENT_STATUS_CSS_LIST = [self::DRAFT => 'badge-light-warning', self::SUBMITTED => 'badge-light-primary', self::APPROVAL_NOT_REQUIRED => 'badge-light-success', self::PARTIALLY_APPROVED => 'badge-light-warning', self::APPROVED => 'badge-light-success',self::CONFIRMED => 'badge-light-success', self::REJECTED => 'badge-light-danger',self::POSTED => 'badge-light-info',self::COMPLETED => 'badge-light-warning', self::CLOSED => 'badge-light-info',self::SHORTLISTED => 'badge-light-primary',self::ACTIVE => 'badge-light-success',self::INACTIVE => 'badge-light-danger'];
    // Error Message
    const DUPLICATE_DOCUMENT_NUMBER = "Document number already exists.";

    const DOCUMENT_STATUS_APPROVED = [self::APPROVED,self::APPROVAL_NOT_REQUIRED,self::POSTED];
    const DOCUMENT_STATUS_REJECTED = [self::CANCEL,self::REJECTED];
    const DOCUMENT_STATUS_SUBMITTED = [self::SUBMITTED, self::APPROVED, self::APPROVAL_NOT_REQUIRED, self::POSTED, self::PARTIALLY_APPROVED];

    # Job Order
    const TYPE_JOB_ORDER = 'Job Work';
    const TYPE_SUBCONTRACTING = 'Subcontracting';
    const JOB_ORDER_TYPES = [
        self::TYPE_JOB_ORDER,
        self::TYPE_SUBCONTRACTING,
    ];

    // Titles
    const MR = 'Mr.';
    const MRS = 'Mrs.';
    const MS = 'Ms.';
    const MISS = 'Miss.';
    const DR = 'Dr.';

    const TITLES = [
        self::MR,
        self::MRS,
        self::MS,
        self::MISS,
        self::DR,
    ];

    // Vendor Types
    const ORGANISATION = 'Organisation';
    const INDIVIDUAL = 'Individual';

    const TRANSPORTER = 'Transporter';
    const REGULAR = 'Regular';
    const CASH = 'Cash';


    const MASTERINDIA = 'MasterIndia';
    const GOV_EINVOICE = 'GovEInvoice';

    const BUNDLE = 'Bundle';

    const STORAGE_TYPES = [
        self::BUNDLE,
    ];


    const VENDOR_TYPES = [
        self::REGULAR,
        self::CASH,
    ];

    const CUSTOMER_TYPES = [
        self::REGULAR,
        self::CASH,
    ];


    const VENDOR_SUB_TYPES = [
        self::REGULAR,
        self::TRANSPORTER,
    ];

    const CRM_CUSTOMER_TYPES = [
        'New',
        'Existing',
    ];

    // Yes/No Options
    const YES = 'Yes';
    const NO = 'No';

    const STOP_OPTIONS = [
        self::YES,
        self::NO,
    ];

    // Category Types
    const PRODUCT = 'Product';
    const SERVICE = 'Service';
    const SUPPLY = 'Supply';
    const CUSTOMER = 'Customer';
    const VENDOR = 'Vendor';
    const EQUIPMENT = 'Equipment';

    const CATEGORY_TYPES = [
        self::PRODUCT,
        self::CUSTOMER,
        self::VENDOR,
    ];

    const SHIPPING = 'shipping';
    const BILLING = 'billing';
    const BOTH = 'both';
    const DEFAULT = 'default';

    const ADDRESS_TYPES = [
        self::SHIPPING,
        self::BILLING,
        self::BOTH,
    ];

    // MSME Types
    const MICRO = 'Micro';
    const SMALL = 'Small';
    const MEDIUM = 'Medium';

    const MSME_TYPES = [
        self::MICRO,
        self::SMALL,
        self::MEDIUM,
    ];

    const GST_REGISTERED = 'Registered';
    const GST_NON_REGISTERED = 'Non-Registered';

    const GST_APPLICABLE = [
        self::GST_REGISTERED,
        self::GST_NON_REGISTERED,
    ];

    const GOODS = 'Goods';
    const ITEM_TYPES = [
        self::GOODS,
        self::SERVICE,
    ];

    const OPEN = 'Open';
    const CLOSE = 'Close';

    const PURCHASE_ORDER_STATUS = [
        self::OPEN,
        self::CLOSE,
    ];

    const PERCENTAGE = 'percentage';
    const FIXED = 'fixed';
    const DYNAMIC = 'dynamic';

    const BOM_TYPES = [
        self::FIXED,
        self::DYNAMIC
    ];

    const DISCOUNT_TYPES = [
        self::PERCENTAGE,
        self::FIXED,
    ];

    public const DEFAULT_PURCHASE = 'Purchase';
    public const DEFAULT_SELLING = 'Selling';

    const DEDUCTION = 'deduction';
    const COLLECTION = 'collection';

    const TAX_APPLICATION_TYPE = [
        self::DEDUCTION,
        self::COLLECTION,
    ];

    const SGST = 'SGST';
    const CGST = 'CGST';
    const IGST = 'IGST';
    const TDS = 'TDS';
    const TCS = 'TCS';
    const VAT = 'VAT';

    const TAX_TYPES = [
        self::SGST,
        self::CGST,
        self::IGST,
        self::TDS,
        self::TCS,
        self::VAT,
    ];
    const TDS_SECTION_192A = '192A';
    const TDS_SECTION_193 = '193';
    const TDS_SECTION_194 = '194';
    const TDS_SECTION_194A = '194A';
    const TDS_SECTION_194B = '194B';
    const TDS_SECTION_194B_PROVISO = '194B_proviso';
    const TDS_SECTION_194BA_1 = '194BA_1';
    const TDS_SECTION_194BA_2 = '194BA_2';
    const TDS_SECTION_194BB = '194BB';
    const TDS_SECTION_194C = '194C';
    const TDS_SECTION_194D = '194D';
    const TDS_SECTION_194DA = '194DA';
    const TDS_SECTION_194EE = '194EE';
    const TDS_SECTION_194G = '194G';
    const TDS_SECTION_194H = '194H';
    const TDS_SECTION_194I_A = '194I_a';
    const TDS_SECTION_194I_B = '194I_b';
    const TDS_SECTION_194IC = '194IC';
    const TDS_SECTION_194J_CALLCENTRE = '194J_callcentre';
    const TDS_SECTION_194J_OTHERS = '194J_others';
    const TDS_SECTION_194J_TECHNICAL = '194J_technical';
    const TDS_SECTION_194K = '194K';
    const TDS_SECTION_194LA = '194LA';
    const TDS_SECTION_194LBA_DIV = '194LBA_div';
    const TDS_SECTION_194LBA_INT = '194LBA_int';
    const TDS_SECTION_194LBB = '194LBB';
    const TDS_SECTION_194LBC_1 = '194LBC_1';
    const TDS_SECTION_194N = '194N';
    const TDS_SECTION_194O = '194O';
    const TDS_SECTION_194Q = '194Q';
    const TDS_SECTION_194R = '194R';
    const TDS_SECTION_194R_PROVISO = '194R_proviso';
    const TDS_SECTION_194S = '194S';
    const TDS_SECTION_194S_PROVISO = '194S_proviso';

    // TCS Sections
    const TCS_SECTION_LIQUOR = 'Liquor';
    const TCS_SECTION_MINERALS = 'Minerals_coal_lignite_iron_ore';
    const TCS_SECTION_MINING_QUARRYING = 'Mining_Quarrying_Lease';
    const TCS_SECTION_MOTOR_VEHICLE = 'Motor_Vehicle';
    const TCS_SECTION_OTHER_FOREST_PRODUCT = 'Other_Forest_Product';
    const TCS_SECTION_OVERSEAS_TOUR_PACKAGE = 'Overseas_Tour_Package';
    const TCS_SECTION_PARKING_LOT_LEASE = 'Parking_Lot_Lease';
    const TCS_SECTION_REMITTANCE_LRS_EDUCATION_LOAN = 'Remittance_LRS_Education_Loan';
    const TCS_SECTION_REMITTANCE_LRS_MEDICAL_EDUCATION = 'Remittance_LRS_Medical_Education';
    const TCS_SECTION_REMITTANCE_LRS_OTHERS = 'Remittance_LRS_Others';
    const TCS_SECTION_SALE_OF_OTHER_GOODS = 'Sale_of_Other_Goods';
    const TCS_SECTION_SCRAP = 'Scrap';
    const TCS_SECTION_TENDU_LEAVES = 'Tendu_Leaves';
    const TCS_SECTION_TIMBER_FOREST_LEASE = 'Timber_Forest_Lease';
    const TCS_SECTION_TIMBER_OTHERS = 'Timber_Others';
    const TCS_SECTION_TOLL_PLAZA_LEASE = 'Toll_Plaza_Lease';
    const TAX_TYPE_IGST = 'igst';
    const TAX_TYPE_CGST = 'cgst';
    const TAX_TYPE_SGST = 'sgst';
    const TAX_TYPE_CESS = 'cess';
    public static function getTaxTypes(): array
    {
        return [
            self::TAX_TYPE_IGST => 'IGST',
            self::TAX_TYPE_CGST => 'CGST',
            self::TAX_TYPE_SGST => 'SGST/UTGST',
            self::TAX_TYPE_CESS => 'Cess',
        ];
    }

    /**
     * Get all TDS sections with their labels
     *
     * @return array
     */
    public static function getTdsSections(): array
    {
        return [
            self::TDS_SECTION_192A => '192A - Accumulated PF balance',
            self::TDS_SECTION_193 => '193 - Interest on Securities',
            self::TDS_SECTION_194 => '194 - Dividend',
            self::TDS_SECTION_194A => '194A - Any other Interest',
            self::TDS_SECTION_194B => '194B - Winnings',
            self::TDS_SECTION_194B_PROVISO => '194B proviso - Winnings fully / partly in kind',
            self::TDS_SECTION_194BA_1 => '194BA(1) - Online games winnings',
            self::TDS_SECTION_194BA_2 => '194BA(2) - Online games winnings fully / partly in kind',
            self::TDS_SECTION_194BB => '194BB - Horse races winnings',
            self::TDS_SECTION_194C => '194C - Works Contract',
            self::TDS_SECTION_194D => '194D - Insurance commission',
            self::TDS_SECTION_194DA => '194DA - Life Insurance policy sum',
            self::TDS_SECTION_194EE => '194EE - NSS payments',
            self::TDS_SECTION_194G => '194G - Lottery commission',
            self::TDS_SECTION_194H => '194H - Commission / Brokerage',
            self::TDS_SECTION_194I_A => '194I(a) - Plant / Machinery rent',
            self::TDS_SECTION_194I_B => '194I(b) - Land / Building rent',
            self::TDS_SECTION_194IC => '194IC - Payment under Joint Development agreement',
            self::TDS_SECTION_194J_CALLCENTRE => '194J - Fees / Royalty (Call Centre Business)',
            self::TDS_SECTION_194J_OTHERS => '194J - Fees / Royalty (Others)',
            self::TDS_SECTION_194J_TECHNICAL => '194J - Fees for Technical Services / Royalty (cinematographic films)',
            self::TDS_SECTION_194K => '194K - Income from units',
            self::TDS_SECTION_194LA => '194LA - Compensation',
            self::TDS_SECTION_194LBA_DIV => '194LBA(1) - Dividend u/s 10(23FC)(b)',
            self::TDS_SECTION_194LBA_INT => '194LBA(1) - Interest u/s 10(23FC)(a)',
            self::TDS_SECTION_194LBB => '194LBB - Investment fund units Income',
            self::TDS_SECTION_194LBC_1 => '194LBC(1) - Securitisation Trust Investment Income',
            self::TDS_SECTION_194N => '194N - Cash withdrawal exceeding limit',
            self::TDS_SECTION_194O => '194O - Payment to E-commerce participant',
            self::TDS_SECTION_194Q => '194Q - Purchase of goods',
            self::TDS_SECTION_194R => '194R - Benefit or Perquisite from Business / Profession',
            self::TDS_SECTION_194R_PROVISO => '194R proviso - Benefit or Perquisite fully / partly in kind',
            self::TDS_SECTION_194S => '194S - Purchase of Virtual Digital Asset',
            self::TDS_SECTION_194S_PROVISO => '194S proviso - Payment fully / partly in kind for Virtual Digital Asset',
        ];
    }

    /**
     * Get all TCS sections with their labels
     *
     * @return array
     */
    public static function getTcsSections(): array
    {
        return [
            self::TCS_SECTION_LIQUOR => 'Liquor',
            self::TCS_SECTION_MINERALS => 'Minerals - coal / lignite / iron ore',
            self::TCS_SECTION_MINING_QUARRYING => 'Mining & Quarrying Lease',
            self::TCS_SECTION_MOTOR_VEHICLE => 'Motor Vehicle',
            self::TCS_SECTION_OTHER_FOREST_PRODUCT => 'Other Forest Product',
            self::TCS_SECTION_OVERSEAS_TOUR_PACKAGE => 'Overseas Tour package',
            self::TCS_SECTION_PARKING_LOT_LEASE => 'Parking Lot Lease',
            self::TCS_SECTION_REMITTANCE_LRS_EDUCATION_LOAN => 'Remittance under LRS - Education Loan (206C(1G) - 3rd proviso)',
            self::TCS_SECTION_REMITTANCE_LRS_MEDICAL_EDUCATION => 'Remittance under LRS - Medical treatment / Education',
            self::TCS_SECTION_REMITTANCE_LRS_OTHERS => 'Remittance under LRS - Others',
            self::TCS_SECTION_SALE_OF_OTHER_GOODS => 'Sale of other goods',
            self::TCS_SECTION_SCRAP => 'Scrap',
            self::TCS_SECTION_TENDU_LEAVES => 'Tendu leaves',
            self::TCS_SECTION_TIMBER_FOREST_LEASE => 'Timber - Forest Lease',
            self::TCS_SECTION_TIMBER_OTHERS => 'Timber - Others',
            self::TCS_SECTION_TOLL_PLAZA_LEASE => 'Toll Plaza Lease',
        ];
    }

    const TDS_CATEGORY = 'TDS';
    const TCS_CATEGORY = 'TCS';

    const GST_TYPES = [
        self::SGST,
        self::CGST,
        self::IGST,
    ];

    const TDS_TYPES = [
        self::TDS,
    ];

    const TCS_TYPES = [
        self::TCS,
    ];

    const TAX_CATEGORIES = [
        self::GST => self::GST_TYPES,
        self::TDS_CATEGORY => self::TDS_TYPES,
        self::TCS_CATEGORY => self::TCS_TYPES,
    ];

    const TAX_CLASSIFICATIONS = [
        self::GST,
        self::TDS_CATEGORY,
        self::TCS_CATEGORY,
    ];
    // Place of Supply Types
    const INTRASTATE = 'Intrastate';
    const INTERSTATE = 'Interstate';
    const OVERSEAS = 'Overseas';

    const PLACE_OF_SUPPLY_TYPES = [
        self::INTRASTATE,
        self::INTERSTATE,
        self::OVERSEAS,
    ];

    const HSN = 'Hsn';
    const SAC = 'Sac';

    const HSN_CODE_TYPE = [
        self::HSN,
        self::SAC,
    ];

    public const TRIGGER_TYPES = [
        'advance',
        'on delivery',
        'post delivery',
    ];

    const SHARING_POLICY_GLOBAL = 'global';
    public const SHARING_POLICY_COMPANY = 'company';
    public const SHARING_POLICY_LOCAL = 'local';
    public const SHARING_POLICY_HYBRID = 'hybrid';

    public const SHARING_POLICY = [
        self::SHARING_POLICY_GLOBAL => 'Global',
        self::SHARING_POLICY_COMPANY => 'Company',
        self::SHARING_POLICY_LOCAL => 'Local',
        self::SHARING_POLICY_HYBRID => 'Hybrid',
    ];
    //Service Labels
    const SERVICE_LABEL = [
        self::TI_SERVICE_ALIAS => "Transporter Invoice",
        self::LR_SERVICE_ALIAS => "Lorry Receipt",
        self::SO_SERVICE_ALIAS => "Sales Order",
        self::SI_SERVICE_ALIAS => "Tax Invoice",
        self::SERVICE_INV_SERVICE_ALIAS => "Service Invoice",
        self::SQ_SERVICE_ALIAS => "Sales Quotation",
        self::SR_SERVICE_ALIAS => "Sales Return",
        self::DELIVERY_CHALLAN_SERVICE_ALIAS => "Delivery Note",
        self::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS => "Delivery Note CUM Invoice",
        self::BOM_SERVICE_ALIAS => "Bill Of Material",
        self::PO_SERVICE_ALIAS => "Purchase Order" ,
        self::SUPPLIER_INVOICE_SERVICE_ALIAS => "Supplier Invoice" ,
        self::SCRAP_SERVICE_ALIAS => "Scrap" ,
        self::PI_SERVICE_ALIAS => "Purchase Indent" ,
        self:: MRN_SERVICE_ALIAS => "MRN" ,
        self:: GATE_ENTRY_SERVICE_ALIAS => "Gate Entry" ,
        self::EXPENSE_SERVICE_ALIAS => "Expense" ,
        self::EXPENSE_ADVISE_SERVICE_ALIAS => 'Expense Advise',
        self::PURCHASE_RETURN_SERVICE_ALIAS => "Purchase Return",
        self::PB_SERVICE_ALIAS => "Purchase Bill",
        self::MATERIAL_ISSUE_SERVICE_ALIAS_NAME => "Material Issue",
        self::MATERIAL_RETURN_SERVICE_ALIAS_NAME => "Material Return",
        self::JO_SERVICE_ALIAS => "Job Order",
        self::VOUCHERS => "Vouchers",
        self::PAYMENTS_SERVICE_ALIAS => 'Payment Voucher',
        self::RECEIPTS_SERVICE_ALIAS => 'Receipt Voucher',
        self::PL_SERVICE_ALIAS => "Pick List",
        self::PSV_SERVICE_ALIAS => "Physical Stock Verification",
        self::TR_SERVICE_ALIAS => "Transporter Request",
        self::PWO_SERVICE_ALIAS => "Production Work Order",
        self::MO_SERVICE_ALIAS => "Manufacturing Order",
        self::INSPECTION_SERVICE_ALIAS => "Inspection",
        self::MATERIAL_ISSUE_SERVICE_NAME => "Material Issue",
        self::MATERIAL_RETURN_SERVICE_NAME => "Material Return",
        self::LEASE_INVOICE_SERVICE_ALIAS=>"Lease Invoice",
        self::JOURNAL_VOUCHER => "Journal Voucher",
        self::OPENING_BALANCE => "Opening Balance",
        self::PRODUCTION_SLIP_SERVICE_ALIAS => "Production Slip",
        self::RFQ_SERVICE_ALIAS => "Request For Quotation",
        self::PQ_SERVICE_ALIAS => "Purchase Quotation",
        self::PQC_SERVICE_ALIAS => "Purchase Quotation Comparison",
        self::PDS_SERVICE_ALIAS => "Pickup Dropoff Schedule",
        self::TRIP_SERVICE_ALIAS => "Trip PLanning",
        PackingListConstants::SERVICE_ALIAS => "Packing List",
        RgrConstant::SERVICE_ALIAS => "Return Goods Receipt"];

    //Service Alias
    const MO_SERVICE_ALIAS = 'mo'; # Manufacturing Order
    const BOM_SERVICE_ALIAS = 'bom';
    const PO_SERVICE_ALIAS = 'po';
    const JO_SERVICE_ALIAS = 'jo';
    const OPENING_BALANCE = 'ob';
    const RFQ_SERVICE_ALIAS = 'rfq';
    const PQ_SERVICE_ALIAS = 'pq';
    const PQC_SERVICE_ALIAS = 'pqc';
    const PDS_SERVICE_ALIAS = 'pds';
    const TRIP_SERVICE_ALIAS = 'trip';
    const SUPPLIER_INVOICE_SERVICE_ALIAS = 'supplier-invoice';
    const SCRAP_SERVICE_ALIAS = 'scrap';
    const PI_SERVICE_ALIAS = 'purchase-indent';
    const MRN_SERVICE_ALIAS = 'mrn';
    const GATE_ENTRY_SERVICE_ALIAS = 'ge';
    const EXPENSE_SERVICE_ALIAS = 'expense';
    const PURCHASE_RETURN_SERVICE_ALIAS = 'purchase-return';
    const INSPECTION_SERVICE_ALIAS = 'insp';
    const PUTAWAY_SERVICE_ALIAS = 'ptw';
    const MATERIAL_REQUEST_SERVICE_ALIAS = 'material-request';
    const MATERIAL_ISSUE_SERVICE_ALIAS = 'material-issue';
    const MATERIAL_ISSUE_SERVICE_ALIAS_NAME = 'mi';
    const MI_MRN_SERVICE_ALIAS_NAME = 'mi-mrn';

    const MATERIAL_ISSUE_SERVICE_NAME = 'Material Issue';
    const MATERIAL_RETURN_SERVICE_ALIAS = 'material-return';
    const MATERIAL_RETURN_SERVICE_ALIAS_NAME = 'mr';
    const MATERIAL_RETURN_SERVICE_NAME = 'Material Return';
    const STOCK_ADJUSTMENT_SERVICE_ALIAS = 'stock-adjustment';
    const PHYSICAL_STOCK_TAKE_SERVICE_ALIAS = 'physical-stock-take';
    const COMMERCIAL_BOM_SERVICE_ALIAS = 'qbom';
    const PRODUCTION_SLIP_SERVICE_ALIAS = 'pslip';
    const PB_SERVICE_ALIAS = 'pb';
    const SO_SERVICE_ALIAS = 'so';
    const LR_SERVICE_ALIAS = 'lr';
    const TI_SERVICE_ALIAS = 'ti';
    const SQ_SERVICE_ALIAS = 'sq';
    const SI_SERVICE_ALIAS = 'si';
    const SERVICE_INV_SERVICE_ALIAS = 'sinv';
    const SR_SERVICE_ALIAS = 'sr';
    const PWO_SERVICE_ALIAS = 'pwo';
    const TR_SERVICE_ALIAS = 'tr';
    const LEASE_INVOICE_SERVICE_ALIAS = 'lease-invoice';
    const DELIVERY_CHALLAN_SERVICE_ALIAS = "dnote";
    const DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS = "si-dnote";
    const EXPENSE_ADVISE_SERVICE_ALIAS = 'expense-advice';
    const PURCHASE_VOUCHER = 'pv';
    const SALES_VOUCHER = 'sv';
    const VOUCHERS = 'voucher';
    const RECEIPT_VOUCHER = 'receipt-voucher';
    const PAYMENT_VOUCHER = 'payment-voucher';
    const DEBIT_Note = 'dn';
    const CREDIT_Note = 'cn';
    const JOURNAL_VOUCHER = 'jv';
    const CONTRA_VOUCHER = 'cv';
    const PAYMENT_VOUCHER_RECEIPT = 'receipt-payment-voucher';
    const PAYMENTS_SERVICE_ALIAS = 'payments';
    const RECEIPTS_SERVICE_ALIAS = 'receipts';

    const RC_SERVICE_ALIAS = "rc";
    const PSV_SERVICE_ALIAS = "psv";
    const DR_SERVICE_ALIAS = "dr";
    const PL_SERVICE_ALIAS = "pl";
    const PL_SERVICE_NAME = "Pick List";
    const LAND_PARCEL = 'land-parcel';
    const LEGAL_FILE = 'legal-file';

    const FILE_TRACKING = 'file-tracking';
    const LOAN_GRANT_FILE = 'loan-grant-file';
    const PROJECT_FILES = 'project-files';
    const POLICY_FILES = 'policy-files';
    const AUDIT_COMPLIANCE_FILES = 'audit-compliance-files';
    const TECHNICAL_FILES = 'technical-files';
    const RESEARCH_FILES = 'research-files';

    const STORE_MAPPING_SERVICE_ALIAS = 'store-mapping';
    const ITEM_BUNDLE_SERVICE_ALIAS = 'item-bundles';
    const ITEM_SERVICE_ALIAS = 'items';
    const LEDGERS_SERVICE_ALIAS = 'ledgers';
    const LEDGER_GROUP_SERVICE_ALIAS = 'ledger-groups';
    const COST_CENTER_SERVICE_ALIAS = 'cost-center';
    const HSN_SERVICE_ALIAS = 'hsn';
    const CATEGORY_SERVICE_ALIAS = 'categories';
    const ATTRIBUTE_SERVICE_ALIAS = 'attributes';
    const PRODUCT_SPECIFICATION_ALIAS = 'product-specifications';
    const DYNAMIC_FIELD_ALIAS = 'dynamic-fields';
    const INSPECTION_CHECKLIST_ALIAS = 'inspection-checklists';
    const PAYMENT_TERM_SERVICE_ALIAS = 'payment-terms';
    const UNIT_SERVICE_ALIAS = 'units';
    const STOCK_ACCOUNT_SERVICE_ALIAS = 'stock-accounts';
    const COGS_ACCOUNT_SERVICE_ALIAS = 'cogs-accounts';
    const GR_ACCOUNT_SERVICE_ALIAS = 'gr-accounts';
    const SALES_ACCOUNT_SERVICE_ALIAS = 'sales-accounts';
    const TAX_SERVICE_ALIAS = 'taxes';
    const PRODUCT_SECTION_SERVICE_ALIAS = 'product-sections';
    const STATION_SERVICE_ALIAS = 'stations';
    const STATION_GROUP_SERVICE_ALIAS = 'station-groups';
    const TERMS_CONDITION_SERVICE_ALIAS = 'terms-conditions';
    const EXCHANGE_RATE_SERVICE_ALIAS = 'exchange-rates';
    const DISCOUNT_MASTER_SERVICE_ALIAS = 'discount-masters';

    const OVERHEAD_MASTER_SERVICE_ALIAS = 'overhead-masters';
    const EXPENSE_MASTER_SERVICE_ALIAS = 'expense-masters';
    const STORE_SERVICE_ALIAS = 'stores';
    const BANK_SERVICE_ALIAS = 'banks';
    const CUSTOMER_SERVICE_ALIAS = 'customers';
    const VENDOR_SERVICE_ALIAS = 'vendors';



    // const HOME_LOAN  = 'home-loan';
    const HOMELOAN = 'home-loan';
    const TERMLOAN = 'term-loan';
    const VEHICLELOAN = 'vehicle-loan';

    const LAND_PLOT = 'land-plot';
    const LAND_LEASE = 'land-lease';

    const LOAN_RECOVERY = 'loan-recovery';

    const LOAN_SETTLEMENT = 'loan-settlement';
    const LOAN_DISBURSEMENT = 'loan-disbursement';
    const LEGAL = 'legal';

    const FIXEDASSET = 'fixed-asset';
    const FIXED_ASSET_DEPRECIATION = 'depreciation';
    const EQPT = 'equipment';
    const MAINT = 'maintenance';
    const FIXED_ASSET_SPLIT = 'fixed-asset-split';
    const FIXED_ASSET_MERGER = 'fixed-asset-merger';
    const FIXED_ASSET_REV_IMP = 'fixed-asset-rev';
    const MAINT_WO = 'maint-wo';
    const DEFECT_NOTIFICATION = 'defect-notification';
    const MAINT_BOM = 'maint-bom';
    const STAKEHOLDER_INTERACTION = 'stakeholder-interaction';
    const COMPLAINT_MANAGEMENT = 'complaint';
    const FEEDBACK_PROCESS = 'feedback-process';
    const PUBLIC_OUTREACH = 'public-outreach';
    const ENGAGEMENT_TRACKING = 'engagement-tracking';
    const RELATION_MANAGEMENT = 'relation-management';
    const GOV_RELATION_MANAGEMENT = 'gov-relation-management';
    const CURRENT_LIABILITIES = "Current Liabilities";
    const DUTIES_TAXES = "Duties & Taxes";
    const GST = "GST";
    const OTHER_STATUTORY_DUES = "Other Statutory Dues";
    const PROVISIONS = "Provisions";
    const ACCOUNT_PAYABLE = "Account Payable";
    const DIRECT_EXPENSES = "Direct Expenses";
    const DIRECT_INCOMES = "Direct Incomes";
    const FIXED_ASSETS = "Fixed Assets";
    const INDIRECT_EXPENSES = "Indirect Expenses";
    const RESERVE_SURPLUS ="Reserves & Surplus";
    const INDIRECT_INCOMES = "Indirect Incomes";
    const INVESTMENTS = "Investments";
    const LOANS_LIABILITY = "Loans (Liability)";
    const BANK_OD = "Bank OD A/c";
    const SECURED_LOANS = "Secured Loans";
    const UNSECURED_LOANS = "Unsecured Loans";
    const MISC_EXPENSES_ASSET = "Misc. Expenses (ASSET)";
    const PURCHASE_ACCOUNTS = "Purchase Accounts";
    const SALES_ACCOUNTS = "Sales Accounts";
    const RETAINED_EARNINGS = "Retained Earnings";
    const SUSPENSE_ACCOUNT = "Suspense A/c";
    const DEPRECIATION = "Depreciation";
    const ASSETS = "Assets";
    const LIABILITIES = "Liabilities";
    const EXPENSES = "Expenses";
    const INCOMES = "Incomes";


    //Operation and Financial Services mapping
    const OPERATION_FINANCIAL_SERVICES_MAPPING = [
        self::HOMELOAN => self::HOMELOAN,
        self::VEHICLELOAN => self::VEHICLELOAN,
        self::TERMLOAN => self::TERMLOAN,
        self::LOAN_RECOVERY => self::LOAN_RECOVERY,
        self::LOAN_SETTLEMENT => self::LOAN_SETTLEMENT,
        self::LOAN_DISBURSEMENT => self::LOAN_DISBURSEMENT,
        self::SI_SERVICE_ALIAS => self::SALES_VOUCHER,
        self::SERVICE_INV_SERVICE_ALIAS => self::SALES_VOUCHER,
        // self::DELIVERY_CHALLAN_SERVICE_ALIAS => self::SALES_VOUCHER,
        self::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS => self::SALES_VOUCHER,
        self::MRN_SERVICE_ALIAS => self::PURCHASE_VOUCHER,
        self::MO_SERVICE_ALIAS => self::JOURNAL_VOUCHER,
        self::PB_SERVICE_ALIAS => self::PURCHASE_VOUCHER,
        self::EXPENSE_ADVISE_SERVICE_ALIAS => self::PURCHASE_VOUCHER,
        self::PURCHASE_RETURN_SERVICE_ALIAS => self::PURCHASE_VOUCHER,
        self::RECEIPT_VOUCHER=>self::RECEIPT_VOUCHER,
        self::PAYMENT_VOUCHER_RECEIPT=>self::PAYMENT_VOUCHER_RECEIPT,
        self::LEASE_INVOICE_SERVICE_ALIAS=>self::SALES_VOUCHER,
        self::PAYMENTS_SERVICE_ALIAS=>self::PAYMENTS_SERVICE_ALIAS,
        self::RECEIPTS_SERVICE_ALIAS=>self::RECEIPTS_SERVICE_ALIAS,
        self::FIXED_ASSET_DEPRECIATION=>self::FIXED_ASSET_DEPRECIATION,
        self::MAINT=>self::MAINT,
        self::FIXED_ASSET_SPLIT=>self::FIXED_ASSET_SPLIT,
        self::FIXED_ASSET_MERGER=>self::FIXED_ASSET_MERGER,
        self::FIXED_ASSET_REV_IMP=>self::FIXED_ASSET_REV_IMP,
        self::FIXEDASSET=>self::FIXEDASSET,
        self::SR_SERVICE_ALIAS=>self::CREDIT_Note,
        self::PSV_SERVICE_ALIAS => self::JOURNAL_VOUCHER,
        self::PRODUCTION_SLIP_SERVICE_ALIAS => self::JOURNAL_VOUCHER,
        self::TI_SERVICE_ALIAS => self::TI_SERVICE_ALIAS,
    ];

    //Service Alias Models Mapping
    const SERVICE_ALIAS_MODELS = [
        self::TI_SERVICE_ALIAS => 'ErpTransportInvoice',
        self::LR_SERVICE_ALIAS => 'ErpLorryReceipt',
        self::MO_SERVICE_ALIAS => 'MfgOrder',
        self::BOM_SERVICE_ALIAS => 'Bom',
        self::PO_SERVICE_ALIAS => 'PurchaseOrder',
        self::JO_SERVICE_ALIAS => 'JobOrder\JobOrder', // If model inside sub folder
        self::SUPPLIER_INVOICE_SERVICE_ALIAS => 'PurchaseOrder',
        self::PI_SERVICE_ALIAS => 'PurchaseIndent',

        self::SCRAP_SERVICE_ALIAS => 'Scrap\ErpScrap',
        self::MRN_SERVICE_ALIAS => 'MrnHeader',
        self::INSPECTION_SERVICE_ALIAS => 'InspectionHeader',
        self::PUTAWAY_SERVICE_ALIAS => 'PutAwayHeader',
        self::GATE_ENTRY_SERVICE_ALIAS => 'GateEntryHeader',
        self::EXPENSE_SERVICE_ALIAS => 'ExpenseHeader',
        self::EXPENSE_ADVISE_SERVICE_ALIAS => 'ExpenseHeader',
        self::PURCHASE_RETURN_SERVICE_ALIAS => 'PRHeader',
        self::MATERIAL_REQUEST_SERVICE_ALIAS => 'PurchaseIndent',
        // self::MATERIAL_ISSUE_SERVICE_ALIAS => 'MrnHeader',
        self::STOCK_ADJUSTMENT_SERVICE_ALIAS => 'MrnHeader',
        self::MATERIAL_ISSUE_SERVICE_ALIAS_NAME => 'ErpMaterialIssueHeader',
        self::MATERIAL_RETURN_SERVICE_ALIAS_NAME => 'ErpMaterialReturnHeader',
        self::PHYSICAL_STOCK_TAKE_SERVICE_ALIAS => 'MrnHeader',
        self::COMMERCIAL_BOM_SERVICE_ALIAS => 'Bom',
        self::PRODUCTION_SLIP_SERVICE_ALIAS => 'ErpProductionSlip',
        self::PB_SERVICE_ALIAS => 'PbHeader',
        self::SO_SERVICE_ALIAS => 'ErpSaleOrder',
        self::SQ_SERVICE_ALIAS => 'ErpSaleOrder',
        self::SI_SERVICE_ALIAS => 'ErpSaleInvoice',
        self::SERVICE_INV_SERVICE_ALIAS => 'ErpSaleInvoice',
        self::SR_SERVICE_ALIAS => 'ErpSaleReturn',
        self::PWO_SERVICE_ALIAS => 'ErpProductionWorkOrder',
        self::TR_SERVICE_ALIAS => 'ErpTransporterRequest',
        self::LEASE_INVOICE_SERVICE_ALIAS => 'ErpSaleInvoice',
        self::DELIVERY_CHALLAN_SERVICE_ALIAS => 'ErpSaleInvoice',
        self::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS => 'ErpSaleInvoice',
        self::PURCHASE_VOUCHER => 'Voucher',
        self::SALES_VOUCHER => 'Voucher',
        self::RECEIPT_VOUCHER => 'Voucher',
        self::PAYMENT_VOUCHER => 'Voucher',
        self::CREDIT_Note => 'Voucher',
        self::DEBIT_Note => 'Voucher',
        self::JOURNAL_VOUCHER => 'Voucher',
        self::CONTRA_VOUCHER => 'Voucher',
        self::PAYMENT_VOUCHER_RECEIPT => 'PaymentVoucher',
        self::OPENING_BALANCE=>'Voucher',
        self::PAYMENTS_SERVICE_ALIAS => 'PaymentVoucher',
        self::RECEIPTS_SERVICE_ALIAS => 'PaymentVoucher',
        self::LEGAL_FILE => 'FileTracking',
        self::LOAN_GRANT_FILE => 'FileTracking',
        self::PROJECT_FILES => 'FileTracking',
        self::POLICY_FILES => 'FileTracking',
        self::AUDIT_COMPLIANCE_FILES => 'FileTracking',
        self::TECHNICAL_FILES => 'FileTracking',
        self::RESEARCH_FILES => 'FileTracking',
        self::FILE_TRACKING => 'FileTracking',

        self::HOMELOAN => 'HomeLoan',
        self::TERMLOAN => 'HomeLoan',
        self::VEHICLELOAN => 'HomeLoan',

        self::LAND_PARCEL => 'LandParcel',
        self::LAND_PLOT => 'LandPlot',
        self::LAND_LEASE => 'LandLease',
        self::LOAN_RECOVERY => 'RecoveryLoan',
        self::LOAN_SETTLEMENT => 'LoanSettlement',
        self::LOAN_DISBURSEMENT => 'LoanDisbursement',
        self::LEGAL => 'Legal',
        self::FIXEDASSET => 'FixedAssetRegistration',
        self::FIXED_ASSET_DEPRECIATION => 'FixedAssetDepreciation',
        self::EQPT=> 'ErpEquipment',
        self::MAINT => 'ErpMaintenance',
        self::FIXED_ASSET_SPLIT => 'FixedAssetSplit',
        self::FIXED_ASSET_MERGER => 'FixedAssetMerger',
        self::FIXED_ASSET_REV_IMP => 'FixedAssetRevImp',
        self::MAINT_BOM=>'PlantMaintBom',

        self::MAINT_WO=>'PlantMaintWo',
        self::DEFECT_NOTIFICATION=>'DefectNotification',

        self::STORE_MAPPING_SERVICE_ALIAS => 'ErpStoreMapping',
        self::ITEM_BUNDLE_SERVICE_ALIAS => 'ErpItemBundle',
        self::ITEM_SERVICE_ALIAS => 'Item',
        self::LEDGERS_SERVICE_ALIAS => 'Ledger',
        self::HSN_SERVICE_ALIAS => 'Hsn',
        self::CATEGORY_SERVICE_ALIAS =>'Category',
        self::ATTRIBUTE_SERVICE_ALIAS =>'Attribute',
        self::PRODUCT_SPECIFICATION_ALIAS =>'ProductSpecification',
        self::PAYMENT_TERM_SERVICE_ALIAS => 'PaymentTerm',
        self::UNIT_SERVICE_ALIAS => 'Unit',
        self::STOCK_ACCOUNT_SERVICE_ALIAS => 'StockAccount',
        self::COGS_ACCOUNT_SERVICE_ALIAS => 'CogsAccount',
        self::GR_ACCOUNT_SERVICE_ALIAS => 'GrAccount',
        self::SALES_ACCOUNT_SERVICE_ALIAS => 'SalesAccount',
        self::TAX_SERVICE_ALIAS => 'Tax',
        self::PRODUCT_SECTION_SERVICE_ALIAS => 'ProductSection',
        self::STATION_SERVICE_ALIAS => 'Station',
        self::STATION_GROUP_SERVICE_ALIAS => 'StationGroup',
        self::TERMS_CONDITION_SERVICE_ALIAS => 'TermsAndCondition',
        self::EXCHANGE_RATE_SERVICE_ALIAS => 'CurrencyExchange',
        self::DISCOUNT_MASTER_SERVICE_ALIAS => 'DiscountMaster',
        self::OVERHEAD_MASTER_SERVICE_ALIAS => 'Overhead',
        self::EXPENSE_MASTER_SERVICE_ALIAS => 'ExpenseMaster',
        self::STORE_SERVICE_ALIAS => 'ErpStore',
        self::BANK_SERVICE_ALIAS => 'Bank',
        self::CUSTOMER_SERVICE_ALIAS => 'Customer',
        self::VENDOR_SERVICE_ALIAS => 'Vendor',

        self::STAKEHOLDER_INTERACTION => 'StakeholderInteraction',
        self::COMPLAINT_MANAGEMENT => 'ComplaintManagement',
        self::FEEDBACK_PROCESS => 'FeedbackProcess',
        self::PUBLIC_OUTREACH => 'ErpPublicOutreachAndCommunication',
        self::ENGAGEMENT_TRACKING => 'ErpEngagementTracking',
        self::RELATION_MANAGEMENT => 'ErpInvestorRelationManagement',
        self::GOV_RELATION_MANAGEMENT => 'ErpGovRelationManagement',
        self::RC_SERVICE_ALIAS => 'ErpRateContract',
        self::PSV_SERVICE_ALIAS => 'ErpPsvHeader',
        self::PL_SERVICE_ALIAS => 'ErpPlHeader',
        self::RFQ_SERVICE_ALIAS => 'ErpRfqHeader',
        self::PQ_SERVICE_ALIAS => 'ErpPqHeader',
        self::PQC_SERVICE_ALIAS => 'ErpPqcHeader',
        self::PDS_SERVICE_ALIAS => 'ErpPickupSchedule',
        self::TRIP_SERVICE_ALIAS => 'ErpTripPlanHeader',
        PackingListConstants::SERVICE_ALIAS => 'PackingList',
        ASNConstant::SERVICE_ALIAS => 'VendorAsn',
        RgrConstant::SERVICE_ALIAS => 'ErpRgr',
    ];
    const CV_ALLOWED_GROUPS=['Cash-in-Hand', 'Bank Accounts', 'Bank OD A/c', 'Bank OCC A/c'];
    const JV_EXCLUDE_GROUPS=[
        'Sales Accounts',
        'Purchase Accounts',
        'Cash-in-Hand',
        'Bank Accounts',
        'Bank OD A/c',
        'Bank OCC A/c',
    ];
    const NON_CARRY_FORWARD_BALANCE_GROUPS =['Expenses','Incomes'];
    const SERVICE_ALIAS_VIEW_ROUTE = [
        self::PAYMENTS_SERVICE_ALIAS => 'payments.edit',
        self::RECEIPTS_SERVICE_ALIAS => 'receipts.edit',
        self::HOMELOAN => 'loan.view_all_detail',
        self::TERMLOAN => 'loan.view_term_detail',
        self::VEHICLELOAN => 'loan.view_vehicle_detail',
        self::LAND_PARCEL => 'land-parcel.view',
        self::LAND_PLOT => 'land-plot.index',
        self::LOAN_RECOVERY => 'loan.recovery_view',
        self::LOAN_SETTLEMENT => 'loan.settlement.view',
        self::LOAN_DISBURSEMENT => 'loan.view-disbursement',
        self::FIXED_ASSET_DEPRECIATION => 'finance.fixed-asset.depreciation.show',
        self::SCRAP_SERVICE_ALIAS => 'remanufacturing.scrap.edit',
        self::PI_SERVICE_ALIAS =>'pi.edit',
        self::PO_SERVICE_ALIAS =>'po.edit',
        self::JO_SERVICE_ALIAS => 'jo.edit',
        self::GATE_ENTRY_SERVICE_ALIAS => 'gate-entry.edit',
        self::MRN_SERVICE_ALIAS =>'material-receipt.edit',
        self::PURCHASE_RETURN_SERVICE_ALIAS => 'purchase-return.edit',
        self::PB_SERVICE_ALIAS => 'purchase-bill.edit',
        self::EXPENSE_ADVISE_SERVICE_ALIAS => 'expense-adv.edit',
        self::MATERIAL_ISSUE_SERVICE_ALIAS_NAME => 'material.issue.edit',
        self::MATERIAL_RETURN_SERVICE_ALIAS_NAME => 'material.return.edit',
        self::BOM_SERVICE_ALIAS => 'bill.of.material.edit',
        self::COMMERCIAL_BOM_SERVICE_ALIAS => 'quotation-bom.edit',
        self::PWO_SERVICE_ALIAS => 'pwo.edit',
        self::MO_SERVICE_ALIAS => 'mo.edit',
        self::PRODUCTION_SLIP_SERVICE_ALIAS => 'production.slip.edit',
        self::SQ_SERVICE_ALIAS => 'sale.quotation.edit',
        self::SO_SERVICE_ALIAS => 'sale.order.edit',
        self::DELIVERY_CHALLAN_SERVICE_ALIAS => 'sale.invoice.edit',
        self::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS => 'sale.invoice.edit',
        self::SI_SERVICE_ALIAS => 'sale.invoice.edit',
        self::SERVICE_INV_SERVICE_ALIAS => 'sale.serviceInvoice.edit',
        self::SR_SERVICE_ALIAS => 'sale.return.edit',
        self::RC_SERVICE_ALIAS => 'rate.contract.edit',
        self::PSV_SERVICE_ALIAS => 'psv.edit',
        self::PL_SERVICE_ALIAS => 'PL.layout',
        self::FIXED_ASSET_SPLIT=>'finance.fixed-asset.split.show',
        self::FIXED_ASSET_MERGER=>'finance.fixed-asset.merger.show',
        self::FIXED_ASSET_REV_IMP=>'finance.fixed-asset.revaluation-impairement.show',
        self::RECEIPT_VOUCHER => 'receipts.edit',
        self::PAYMENT_VOUCHER => 'payments.edit',
        self::FIXEDASSET => 'finance.fixed-asset.registration.show',
        self::SALES_VOUCHER => 'vouchers.edit',
        self::CONTRA_VOUCHER => 'vouchers.edit',
        self::JOURNAL_VOUCHER => 'vouchers.edit',
        self::PURCHASE_VOUCHER => 'vouchers.edit',
        self::VOUCHERS => 'vouchers.edit',
    ];
    const PWO_DOC_TYPES = [self::PWO_SERVICE_ALIAS];
    const SALE_INVOICE_DOC_TYPES = [self::SI_SERVICE_ALIAS, self::LEASE_INVOICE_SERVICE_ALIAS, self::DELIVERY_CHALLAN_SERVICE_ALIAS, self::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS, self::SERVICE_INV_SERVICE_ALIAS];
    const SALE_RETURN_DOC_TYPES = [self::SR_SERVICE_ALIAS,self::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS, self::DELIVERY_CHALLAN_SERVICE_ALIAS];
    const SALE_INVOICE_DOC_TYPES_FOR_DB = [self::SI_SERVICE_ALIAS, 'dn', 'sidn', self::SERVICE_INV_SERVICE_ALIAS];
    const SALE_RETURN_DOC_TYPES_FOR_DB = [self::SR_SERVICE_ALIAS, 'dn', 'srdn'];
    const DOC_NO_TYPE_AUTO = "Auto";
    const DOC_NO_TYPE_MANUAL = "Manually";
    const DOC_NO_TYPES = [self::DOC_NO_TYPE_AUTO, self::DOC_NO_TYPE_MANUAL];

    const DOC_RESET_PATTERN_NEVER = "Never";
    const DOC_RESET_PATTERN_YEARLY = "Yearly";
    const DOC_RESET_PATTERN_QUARTERLY = "Quarterly";
    const DOC_RESET_PATTERN_MONTHLY = "Monthly";
    const DOC_RESET_PATTERNS = [self::DOC_RESET_PATTERN_NEVER, self::DOC_RESET_PATTERN_YEARLY, self::DOC_RESET_PATTERN_QUARTERLY, self::DOC_RESET_PATTERN_MONTHLY];
    const NON_STOCK = 'non-stock';
    const STOCK = 'stock';
    const IS_SERVICE = [self::STOCK, self::NON_STOCK];

    const PAGE_LENGTH_10 = 10;
    const PAGE_LENGTH_20 = 20;
    const PAGE_LENGTH_50 = 50;
    const PAGE_LENGTH_100 = 100;
    const PAGE_LENGTH_2000 = 2000;
    const PAGE_LENGTH_1000 = 1000;
    const PAGE_LENGTH_10000 = 10000;

    const PAGE_LENGTHS = [
        self::PAGE_LENGTH_10,
        self::PAGE_LENGTH_20,
        self::PAGE_LENGTH_50,
        self::PAGE_LENGTH_100,
    ];

    const SCRAP = 'Scrap';
    const STOCKK = 'Stock';
    const STOCKK_LABEL = 'Self';
    const SHOP_FLOOR = 'Shop floor';
    const ADMINISTRATION = 'Administration';
    const OTHER = 'Other';
    const VENDOR_STORE = 'Vendor';
    const VENDOR_STORE_LABEL = 'Vendor';

    const ERP_STORE_LOCATION_TYPES = [
        self::STOCKK,
        // self::SHOP_FLOOR,
        // self::ADMINISTRATION,
        // self::OTHER,
        self::VENDOR_STORE
    ];

    const ERP_STORE_LOCATION_TYPES_LABEL_VAL = [
        [
            'label' => self::STOCKK_LABEL,
            'value' => self::STOCKK
        ],
        [
            'label' => self::VENDOR_STORE,
            'value' => self::VENDOR_STORE_LABEL
        ]
    ];

    const ERP_SUB_STORE_LOCATION_TYPES = [
        self::STOCKK,
        self::SHOP_FLOOR,
        self::VENDOR,
        self::OTHER,
    ];

    const ERP_QTY_TYPES = [
        self::REJECTED,
        self::ACCEPTED,
        self::ALL
    ];

    public const RAW_MATERIAL = 'Raw Material';
    public const WIP_SEMI_FINISHED = 'WIP/Semi Finished';
    public const FINISHED_GOODS = 'Finished Goods';
    public const TRADED_ITEM = 'Traded Item';
    public const ASSET = 'Asset';
    public const EXPENSE = 'Expense';

    const ITEM_SUB_TYPES = [
        ['name' => 'Raw Material', 'status' => ConstantHelper::ACTIVE],
        ['name' => 'WIP/Semi Finished', 'status' => ConstantHelper::ACTIVE],
        ['name' => 'Finished Goods', 'status' => ConstantHelper::ACTIVE],
        ['name' => 'Traded Item', 'status' => ConstantHelper::ACTIVE],
        ['name' => 'Asset', 'status' => ConstantHelper::ACTIVE],
        ['name' => 'Expense', 'status' => ConstantHelper::ACTIVE],
    ];

    const CRM_TOKEN = '$2y$12$t.wJEsgL6We96B9LK28ujuJ78xnhDRYynUYHu6DlUJ13m7D5lWv8y';

    const INPUT_TYPE_TEXT = "text";
    const INPUT_TYPE_SELECT = "select";
    const INPUT_TYPE_MULTI_SELECT = "multi-select";
    const INPUT_TYPE_NUMBER = "number";
    const INPUT_TYPE_DATE = "date";
    const INPUT_TYPE_TIME = "time";
    const INPUT_TYPE_DATETIME_LOCAL = "datetime-local";
    const INPUT_TYPE_MONTH = "month";
    const INPUT_TYPE_WEEK = "week";
    const INPUT_TYPE_COLOR = "color";
    const INPUT_TYPE_CHECKBOX = "checkbox";
    const INPUT_TYPE_RADIO = "radio";
    const INPUT_TYPE_FILE = "file";
    const INPUT_TYPE_RANGE = "range";
    const INPUT_TYPE_IMAGE = "image";
    const ERP_MASTER_SERVICE_TYPE = "master";
    const ERP_TRANSACTION_SERVICE_TYPE = "transaction";
    const ERP_SERVICE_TYPES = [
        self::ERP_MASTER_SERVICE_TYPE,
        self::ERP_TRANSACTION_SERVICE_TYPE
    ];
    const ERP_SERVICE_ALIAS_TYPE = [
        self::ITEM_SERVICE_ALIAS => ConstantHelper::ERP_MASTER_SERVICE_TYPE,
        self::CUSTOMER_SERVICE_ALIAS => ConstantHelper::ERP_MASTER_SERVICE_TYPE,
        self::VENDOR_SERVICE_ALIAS => ConstantHelper::ERP_MASTER_SERVICE_TYPE,
        self::LEDGERS_SERVICE_ALIAS => ConstantHelper::ERP_MASTER_SERVICE_TYPE,
        self::EQPT=>ConstantHelper::ERP_MASTER_SERVICE_TYPE

    ];
    const DUE_DATE_ALIAS = [self::MRN_SERVICE_ALIAS,self::PB_SERVICE_ALIAS,self::SI_SERVICE_ALIAS,self::SERVICE_INV_SERVICE_ALIAS,self::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS];
    const DOC_APPROVAL_STATUS_MAPPING = [
        ConstantHelper::SUBMITTED => 'submit',
        ConstantHelper::APPROVED => 'approve',
        ConstantHelper::REJECTED => 'reject',
        ConstantHelper::POSTED => 'posted',
        ConstantHelper::REVOKE => 'revoke',
        ConstantHelper::CLOSED => 'closed',
    ];
    public const LEDGER_ACCOUNT_NON_EDITABLE = [
        self::CURRENT_LIABILITIES,
        self::DUTIES_TAXES,
        self::GST,
        self::TDS,
        self::OTHER_STATUTORY_DUES,
        self::PROVISIONS,
        self::ACCOUNT_PAYABLE,
        self::DIRECT_EXPENSES,
        self::DIRECT_INCOMES,
        self::FIXED_ASSETS,
        self::INDIRECT_EXPENSES,
        self::INDIRECT_INCOMES,
        self::INVESTMENTS,
        self::LOANS_LIABILITY,
        self::BANK_OD,
        self::SECURED_LOANS,
        self::UNSECURED_LOANS,
        self::MISC_EXPENSES_ASSET,
        self::PURCHASE_ACCOUNTS,
        self::SALES_ACCOUNTS,
        self::RETAINED_EARNINGS,
        self::SUSPENSE_ACCOUNT,
        self::DEPRECIATION,
        self::ASSETS,
        self::LIABILITIES,
        self::EXPENSES,
        self::INCOMES
    ];

    const VOUCHER_TYPES = ['Sales','Purchase','Return','Direct'];
    const NATURE_OF_DOCUMENT = [
        'clttx' => 'Liable to collect tax u/s 52(TCS)',
        'paytx' => 'Liable to pay tax u/s 9(5)',
        'clttxa' => 'Liable to collect tax u/s 52(TCS)',
        'paytxa' => 'Liable to pay tax u/s 9(5)'

    ];

    const NIL_RATED_DESCRIPTION = [
        'INTRB2B' => 'Inter - State supplies to registered persons',
        'INTRAB2B' => 'Intra - State supplies to registered persons',
        'INTRB2C' => 'Inter - State supplies to unregistered persons',
        'INTRAB2C' => 'Intra - State supplies to unregistered persons'
    ];


    const FY_CURRENT_STATUS = "current";
    const FY_NEXT_STATUS = "next";
    const FY_PREVIOUS_STATUS = "prev";

    const FY_NOT_CLOSED_STATUS = false;
    const FY_NOT_LOCK_STATUS = false;


    const DATA_TYPE_TEXT = 'text';
    const DATA_TYPE_NUMBER = 'number';
    const DATA_TYPE_DATE = 'date';
    const DATA_TYPE_LIST = 'list';
    const DATA_TYPE_BOOLEAN = 'boolean';

    const DATA_TYPES = [
        ['label' => 'Text', 'value' => self::DATA_TYPE_TEXT],
        ['label' => 'Number','value' => self::DATA_TYPE_NUMBER],
        ['label' => 'Date', 'value' => self::DATA_TYPE_DATE],
        ['label' => 'List', 'value' => self::DATA_TYPE_LIST],
        ['label' => 'Boolean (Yes/No)', 'value' => self::DATA_TYPE_BOOLEAN],
    ];

      public const LORRY_CHARGES = [
        '5'   => '5',
        '10'   => '10',
    ];

     public const FUEL_TYPES = [
        'Diesel'   => 'Diesel',
        'Petrol'   => 'Petrol',
        'CNG'      => 'CNG',
        'Electric' => 'Electric',
    ];

      public const OWNERSHIP = [
        'self'   => 'Self',
        'contract'   => 'Contract',
        'hp'      => 'HP',
    ];

    const MAINTENANCE_INSPECTION_CHECKLIST_TYPE = 'maintenance';
    const ITEM_INSPECTION_CHECKLIST_TYPE = 'item';

    const INSPECTION_CHECKLIST_TYPES = [
        self::MAINTENANCE_INSPECTION_CHECKLIST_TYPE,
        self::ITEM_INSPECTION_CHECKLIST_TYPE,
    ];
     // Excel Export Styling Constants
    const EXCEL_FONT_COLOR_BLACK = 'FF000000';
    const EXCEL_FONT_BOLD = true;

    const EXCEL_FILL_TYPE_SOLID = 'solid';
    const EXCEL_FILL_YELLOW = 'FFFF00';
    const EXCEL_FILL_GREY = 'D3D3D3';

    const EXCEL_ALIGNMENT_WRAP = true;
    const EXCEL_ALIGNMENT_WRAP_FALSE = false;
    const EXCEL_ALIGNMENT_VERTICAL_CENTER = 'center';
    const EXCEL_ALIGNMENT_HORIZONTAL_CENTER = 'center';

    const EXCEL_BORDER_STYLE_THIN = 'thin';
    const EXCEL_BORDER_COLOR_BLACK = 'FF000000';

    const EXCEL_COLUMN_WIDTH_DEFAULT = 15;
    const POST_DELIVERY = 'post delivery';

    const DEFECT_SEVERITY_MINOR = 'minor';
    const DEFECT_SEVERITY_MAJOR = 'major';
    const DEFECT_SEVERITY_SCRAP = 'scrap';

    const DEFECT_SEVERITY_LEVELS = [
        ['label' => 'Minor', 'value' => self::DEFECT_SEVERITY_MINOR],
        ['label' => 'Major', 'value' => self::DEFECT_SEVERITY_MAJOR],
        ['label' => 'Scrap', 'value' => self::DEFECT_SEVERITY_SCRAP],
    ];


    const DAMAGE_NATURE_NO_DAMAGE = 'no_damage';
    const DAMAGE_NATURE_CUSTOMER_DAMAGE = 'customer_damage';
    const DAMAGE_NATURE_TRANSIT_HANDLE_DAMAGE = 'transit_handling_damage';
    const DAMAGE_NATURE_WEAR_AND_TEAR = 'wear_tear_damage';

    const DAMAGE_NATURES = [
        ['label' => 'No Damage', 'value' => self::DAMAGE_NATURE_NO_DAMAGE],
        ['label' => 'Customer Damage', 'value' => self::DAMAGE_NATURE_CUSTOMER_DAMAGE],
        ['label' => 'Transit / Handling Damage', 'value' => self::DAMAGE_NATURE_TRANSIT_HANDLE_DAMAGE],
        ['label' => 'Wear and Tear', 'value' => self::DAMAGE_NATURE_WEAR_AND_TEAR],
    ];

    const DAMAGE_TYPE = [
        ['label' => 'Transit damage', 'value' => 'transit_damage'],
        ['label' => 'Wrong Product', 'value' => 'wrong_product'],
        ['label' => 'Missing Product', 'value' => 'missing_product'],
        ['label' => 'Extra Asset', 'value' => 'extra_asset'],
    ];

    const REPAIR_ACTION  = [
        ['label' => 'Change Defect Severity', 'value' => 'change_defect_severity'],
        ['label' => 'Send to Vendor', 'value' => 'send_to_vendor'],
        ['label' => 'Scrap', 'value' => 'scrap'],
        ['label' => 'Repair', 'value' => 'repair'],
    ];
}
