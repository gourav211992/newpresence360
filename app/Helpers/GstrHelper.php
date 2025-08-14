<?php

namespace App\Helpers;
use App\Models\ErpGstInvoiceType;
use App\Models\ErpSaleInvoice;
use App\Models\ErpSaleReturn;
use App\Models\Finance\GstrCompiledData;
use App\Models\GstInvoiceType;
use App\Models\Organization;
use App\Models\PRHeader;
use App\Models\State;
use App\Models\Voucher;
use Carbon\Carbon;

class GstrHelper
{

    public static function prepareData($data,$invoiceTypeName){
        switch ($invoiceTypeName) {
            case 'b2b':
                return self::prepareB2bData($data);
                break;
            case 'b2ba':
                return self::prepareB2baData($data);
                break;
            case 'b2cl':
                return self::prepareB2clData($data);
                break;
            case 'b2cla':
                return self::prepareB2claData($data);
                break;
            case 'b2cs':
                return self::prepareB2csData($data);
                break;
            case 'b2csa':
                return self::prepareB2csaData($data);
                break;
            case 'cdnr':
                return self::prepareCdnrData($data);
                break;
            case 'cdnra':
                return self::prepareCdnraData($data);
                break;
            case 'cdnur':
                return self::prepareCdnurData($data);
                break;
            case 'cdnura':
                return self::prepareCdnuraData($data);
                break;
            case 'supeco':
                return self::prepareSupecoData($data);
                break;
            case 'supecoa':
                return self::prepareSupecoaData($data);
                break;
            case 'ecob2b':
                return self::prepareEcomData($data,$invoiceTypeName);
                break;
            case 'ecob2c':
                return self::prepareEcomData($data,$invoiceTypeName);
                break;
            case 'ecourp2b':
                return self::prepareEcomData($data,$invoiceTypeName);
                break;
            case 'ecourp2c':
                return self::prepareEcomData($data,$invoiceTypeName);
                break;
            case 'ecoab2b':
                return self::prepareEcomaData($data,$invoiceTypeName);
                break;
            case 'ecoab2c':
                return self::prepareEcomaData($data,$invoiceTypeName);
                break;
            case 'ecoaurp2b':
                return self::prepareEcomaData($data,$invoiceTypeName);
                break;
            case 'ecoaurp2c':
                return self::prepareEcomaData($data,$invoiceTypeName);
                break;
            case 'doc_issue':
                return self::prepareDocIssueData($data);
                break;
            case 'at':
                return self::prepareAtData($data);
                break;
            case 'ata':
                return self::prepareAtaData($data);
                break;
            case 'txpd':
                return self::prepareTxpdData($data);
                break;
            case 'txpda':
                return self::prepareTxpdaData($data);
                break;
            case 'nil':
                return self::prepareNilData($data);
                break;
            case 'exp':
                return self::prepareExpData($data);
                break;
            case 'expa':
                return self::prepareExpaData($data);
                break;
            case 'hsn':
                return self::prepareHsnData($data);
                break;
            default:
                return [];
        }
    }


    public static function prepareB2bData($data){
        $invoicesArray = [];
        $data = $data->groupBy('party_gstin');

        foreach ($data as $partyGstin => $invoices) {
            $invArray = [];

            foreach ($invoices as $invoice) {
                 $items = [
                    [
                        "num" => rand(1000, 9999),
                        "itm_det" => [
                            "txval" => $invoice->taxable_amt,
                            "rt" => $invoice->rate,
                            "iamt" => $invoice->igst,
                            "csamt" => $invoice->cess
                        ]
                    ]
                ];

                $invoiceData = [
                    "inum" => $invoice->invoice_no,
                    "idt" => date('d-m-Y', strtotime($invoice->invoice_date)),
                    "val" => $invoice->invoice_amt,
                    "pos" => (string)$invoice->pos,
                    "rchrg" => $invoice->reverse_charge,
                    "inv_typ" => $invoice->invoice_type,
                    "itms" => $items
                ];

                if (!empty($invoice->applicable_tax_rate)) {
                    $invoiceData["diff_percent"] = $invoice->applicable_tax_rate;
                }

                $invArray[] = $invoiceData;
            }

            $invoicesArray[] = [
                "ctin" => $partyGstin,
                "inv" => $invArray
            ];
        }
        return $invoicesArray;
    }

    public static function prepareB2baData($data)
    {
        $data = $data->groupBy('party_gstin');
        $invoicesArray = [];

        foreach ($data as $partyGstin => $invoices) {
            foreach ($invoices as $invoice) {
                $items = [
                    [
                        "num" => rand(100, 999),
                        "itm_det" => [
                            "txval" => $invoice->taxable_amt,
                            "rt" => $invoice->rate,
                            "iamt" => $invoice->igst,
                            "csamt" => $invoice->cess,
                        ]
                    ]
                ];

                $invoiceData = [
                    "oinum" => $invoice->invoice_no,
                    "oidt" => date('d-m-Y', strtotime($invoice->invoice_date)),
                    "inum" => $invoice->revised_invoice_no,
                    "idt" => date('d-m-Y', strtotime($invoice->revised_invoice_date)),
                    "val" => $invoice->invoice_amt,
                    "pos" => $invoice->pos,
                    "rchrg" => $invoice->reverse_charge,
                    "diff_percent" => $invoice->applicable_tax_rate,
                    "inv_typ" => $invoice->invoice_type,
                    "itms" => $items
                ];

                $invArray[] = $invoiceData;
            }

            $invoicesArray[] = [
                "ctin" => $partyGstin,
                "inv" => $invArray
            ];
        }

        return $invoicesArray;
    }

    public static function prepareB2clData($data)
    {
        $data = $data->groupBy('pos');
        $invoicesArray = [];

        foreach ($data as $pos => $invoices) {
            $invArray = [];

            foreach ($invoices as $invoice) {
                $items = [
                        [
                            "num" => rand(100, 999),
                            "itm_det" => [
                                "txval" => $invoice->taxable_amt,
                                "rt" => $invoice->rate,
                                "iamt" => $invoice->igst,
                                "csamt" => $invoice->cess,
                            ]
                        ]
                    ];

                $invoiceData = [
                    "inum" => $invoice->invoice_no,
                    "idt" => date('d-m-Y', strtotime($invoice->invoice_date)),
                    "val" => $invoice->invoice_amt,
                    "itms" => $items
                ];

                $invArray[] = $invoiceData;
            }

            $invoicesArray[] = [
                "pos" => $pos,
                "inv" => $invArray
            ];
        }

        return $invoicesArray;
    }

    public static function prepareB2claData($data)
    {
        $data = $data->groupBy('pos');
        $invoicesArray = [];

        foreach ($data as $pos => $invoices) {
            $invArray = [];

            foreach ($invoices as $invoice) {
                $items = [
                    [
                        "num" => rand(100, 999),
                        "itm_det" => [
                            "txval" => $invoice->taxable_amt,
                            "rt" => $invoice->rate,
                            "iamt" => $invoice->igst,
                            "csamt" => $invoice->cess,
                        ]
                    ]
                ];

                $invoiceData = [
                    "oinum" => $invoice->invoice_no,
                    "oidt" => date('d-m-Y', strtotime($invoice->invoice_date)),
                    "inum" => $invoice->revised_invoice_no,
                    "idt" => date('d-m-Y', strtotime($invoice->revised_invoice_date)),
                    "val" => $invoice->invoice_amt,
                    "diff_percent" => $invoice->applicable_tax_rate,
                    "itms" => $items
                ];

                $invArray[] = $invoiceData;
            }

            $invoicesArray[] = [
                "pos" => $pos,
                "inv" => $invArray
            ];
        }

        return $invoicesArray;
    }

    public static function prepareB2csData($data)
    {
        $b2csArray = [];

        foreach ($data as $invoice) {
            $b2csArray[] = [
                "sply_ty" => $invoice->supply_type,
                "rt" => $invoice->rate,
                "typ" => $invoice->invoice_type,
                "pos" => $invoice->pos,
                "txval" => $invoice->taxable_amt,
                "iamt" => $invoice->igst,
                "csamt" => $invoice->cess,
            ];
        }

        return $b2csArray;
    }


    public static function prepareB2csaData($data)
    {
        $b2csaArray = [];

        foreach ($data as $invoice) {
            $items = [
                [
                    "rt" => $invoice->rate,
                    "txval" => $invoice->taxable_amt,
                    "iamt" => $invoice->igst,
                    "csamt" => $invoice->cess,
                ]
            ];

            $b2csaArray[] = [
                "sply_ty" => $invoice->supply_type,
                "pos" => $invoice->pos,
                "typ" => $invoice->invoice_type,
                "diff_percent" => $invoice->applicable_tax_rate,
                "itms" => $items,
                "omon" => $invoice->month.''.$invoice->year,

            ];
        }

        return $b2csaArray;
    }

    public static function prepareCdnrData($data)
    {
        $invoicesArray = [];
        $data = $data->groupBy('party_gstin');

        foreach ($data as $partyGstin => $invoices) {
            $invArray = [];

            foreach ($invoices as $invoice) {
                 $items = [
                    [
                        "itm_det" => [
                            "txval" => $invoice->taxable_amt,
                            "rt" => $invoice->rate,
                            "csamt" => $invoice->cess,
                            "iamt" => $invoice->integrated_tax_amt
                        ],
                        "num" => rand(1000, 9999),
                    ]
                ];

                $invoiceData = [
                    "nt_num" => $invoice->note_number,
                    "nt_dt" => date('d-m-Y', strtotime($invoice->note_date)),
                    "ntty" => $invoice->note_type,
                    "val" => $invoice->note_value,
                    "pos" => (string)$invoice->pos,
                    "rchrg" => $invoice->reverse_charge
                ];

                if (!empty($invoice->applicable_tax_rate)) {
                    $invoiceData["diff_percent"] = $invoice->applicable_tax_rate;
                }

                $invoiceData['itms'] = $items;
                $invoiceData['inv_typ'] = $invoice->invoice_type;

                $invArray[] = $invoiceData;
            }

            $invoicesArray[] = [
                "ctin" => $partyGstin,
                "nt" => $invArray
            ];
        }
        return $invoicesArray;
    }

    public static function prepareCdnraData($data)
    {
        $invoicesArray = [];
        $data = $data->groupBy('party_gstin');

        foreach ($data as $partyGstin => $invoices) {
            $invArray = [];

            foreach ($invoices as $invoice) {
                 $items = [
                    [
                        "itm_det" => [
                            "txval" => $invoice->taxable_amt,
                            "rt" => $invoice->rate,
                            "csamt" => $invoice->cess,
                            "iamt" => $invoice->integrated_tax_amt
                        ],
                        "num" => rand(1000, 9999),
                    ]
                ];

                $invoiceData = [
                    "nt_num" => $invoice->revised_note_no,
                    "nt_dt" => date('d-m-Y', strtotime($invoice->revised_note_date)),
                    "ont_num" => $invoice->note_number,
                    "ont_dt" => date('d-m-Y', strtotime($invoice->note_date)),
                    "ntty" => $invoice->note_type,
                    "val" => $invoice->note_value,
                    "pos" => (string)$invoice->pos,
                    "rchrg" => $invoice->reverse_charge
                ];

                if (!empty($invoice->applicable_tax_rate)) {
                    $invoiceData["diff_percent"] = $invoice->applicable_tax_rate;
                }

                $invoiceData['itms'] = $items;
                $invoiceData['inv_typ'] = $invoice->invoice_type;

                $invArray[] = $invoiceData;
            }

            $invoicesArray[] = [
                "ctin" => $partyGstin,
                "nt" => $invArray
            ];
        }
        return $invoicesArray;
    }


    public static function prepareCdnurData($data)
    {
        $invoiceData = [];

        foreach ($data as $invoice) {

            $items = [
                [
                    "itm_det" => [
                        "txval" => $invoice->taxable_amt,
                        "rt" => $invoice->rate,
                        "csamt" => $invoice->cess,
                        "iamt" => $invoice->integrated_tax_amt
                    ],
                    "num" => rand(1000, 9999),
                ]
            ];

            $invoiceData[] = [
                "nt_num" => $invoice->note_number,
                "nt_dt" => date('d-m-Y', strtotime($invoice->note_date)),
                "ntty" => $invoice->note_type,
                "val" => $invoice->note_value,
                "typ" => $invoice->ur_type,
                "itms" => $items,
                "pos" => (string)$invoice->pos,
                "diff_percent" => $invoice->applicable_tax_rate,
            ];
        }
        return $invoiceData;
    }

    public static function prepareCdnuraData($data)
    {
        $invoiceData = [];

        foreach ($data as $invoice) {

            $items = [
                [
                    "itm_det" => [
                        "txval" => $invoice->taxable_amt,
                        "rt" => $invoice->rate,
                        "csamt" => $invoice->cess,
                        "iamt" => $invoice->integrated_tax_amt
                    ],
                    "num" => rand(1000, 9999),
                ]
            ];

            $invarr = [
                "nt_num" => $invoice->revised_note_n0,
                "nt_dt" => date('d-m-Y', strtotime($invoice->revised_note_date)),
                "ont_num" => $invoice->note_number,
                "ont_dt" => date('d-m-Y', strtotime($invoice->note_date)),
                "ntty" => $invoice->note_type,
                "val" => $invoice->note_value,
            ];

            if($invoice->pos){
                $invarr["pos"] = (string)$invoice->pos;
            }

            $invarr["itms"] = $items;

            if (!empty($invoice->applicable_tax_rate)) {
                $invarr["diff_percent"] = $invoice->applicable_tax_rate;
            }

            $invarr["typ"] = $invoice->ur_type;

            $invoiceData[] = $invarr;
        }
        return $invoiceData;
    }

    public static function prepareSupecoData($data)
    {
        $supecoData = [];

        foreach ($data as $invoice) {
            $invoiceData = [
                "etin" => $invoice->e_commerce_gstin,
                "supval" => $invoice->net_value_of_supplies,
                "igst" => $invoice->igst,
                "cgst" => $invoice->cgst,
                "sgst" => $invoice->sgst,
                "cess" => $invoice->cess,
            ];

            // Classify invoices into `clttx` or `paytx`
            if ($invoice->nature_of_document === 'clttx') {
                $supecoData["clttx"][] = $invoiceData;
            } elseif ($invoice->nature_of_document === 'paytx') {
                $supecoData["paytx"][] = $invoiceData;
            }
        }
        return $supecoData;
    }

    public static function prepareSupecoaData($data){
        $supecoaData = [];

        foreach ($data as $invoice) {
            $invoiceData = [
                "oetin" => $invoice->e_commerce_gstin,
                "etin" => $invoice->revised_ecom_gstin,
                "supval" => $invoice->net_value_of_supplies,
                "igst" => $invoice->igst,
                "cgst" => $invoice->cgst,
                "sgst" => $invoice->sgst,
                "cess" => $invoice->cess,
                "omon" => sprintf("%02d%04d", $invoice->month, $invoice->year)
            ];

            if ($invoice->nature_of_document === 'clttxa') {
                $supecoaData["clttxa"][] = $invoiceData;
            } elseif ($invoice->nature_of_document === 'paytxa') {
                $supecoaData["paytxa"][] = $invoiceData;
            }
        }
        return $supecoaData;
    }

    public static function prepareEcomData($data,$type){
        $ecom = [];
        if($type == 'ecob2b'){
            $ecom = self::prepareEcoB2BData($data);
        }elseif($type == 'ecob2c'){
            $ecom = self::prepareEcoB2CData($data);
        }elseif($type == 'ecourp2b'){
            $ecom = self::prepareEcoUrp2bData($data);
        }elseif($type == 'ecourp2c'){
            $ecom = self::prepareEcoUrp2cData($data);
        }

        return $ecom;
    }

    public static function prepareEcoB2BData($data)
    {
        $ecomData = [
                "b2b" => []
        ];

        $groupedInvoices = [];

        foreach ($data as $invoice) {
            $items = [
                [
                    "num" => rand(1000, 9999),
                    "itm_det" => [
                        "txval" => $invoice->taxable_amt,
                        "rt" => $invoice->rate,
                        "iamt" => $invoice->igst,
                        "csamt" => $invoice->cess
                    ]
                ]
            ];

            $invoiceEntry = [
                "inum" => $invoice->doc_no,
                "idt" => date('d-m-Y', strtotime($invoice->doc_date)),
                "val" => $invoice->value_of_supplies_made,
                "pos" => (string)$invoice->pos,
                "inv_typ" => $invoice->doc_type,
                "itms" => $items,
                "flag" => $invoice->reverse_charge,
                "sply_ty" => $invoice->supply_type,
            ];

            $stin = $invoice->supplier_gstin;
            $rtin = $invoice->party_gstin;
            $key = $stin . '|' . $rtin;

            if (!isset($groupedInvoices[$key])) {
                $groupedInvoices[$key] = [
                    "stin" => $stin,
                    "rtin" => $rtin,
                    "inv" => []
                ];
            }

            $groupedInvoices[$key]["inv"][] = $invoiceEntry;
        }

        $ecomData["b2b"] = array_values($groupedInvoices);

        return $ecomData;

    }

    public static function prepareEcoB2CData($data)
    {
        $ecomData = [
                "b2c" => []
        ];

        foreach ($data as $invoice) {
            $ecomData["b2c"][] = [
                "stin" => $invoice->supplier_gstin,
                "pos" => (string)$invoice->pos,
                "sply_ty" => $invoice->supply_type,
                "txval" => $invoice->taxable_amt,
                "rt" => $invoice->rate,
                "iamt" => $invoice->igst,
                "camt" => $invoice->cgst,
                "samt" => $invoice->sgst,
                "csamt" => $invoice->cess,
                "flag" => $invoice->reverse_charge,
            ];
        }

        return $ecomData;
    }

    public static function prepareEcoURP2BData($data)
    {
        $ecomData = [
                "urp2b" => []
        ];

        $groupedInvoices = [];

        foreach ($data as $invoice) {
            $items = [
                [
                    "num" => rand(1000, 9999),
                    "itm_det" => [
                        "txval" => $invoice->taxable_amt,
                        "rt" => $invoice->rate,
                        "iamt" => $invoice->igst,
                        "csamt" => $invoice->cess
                    ]
                ]
            ];

            $invoiceEntry = [
                "inum" => $invoice->doc_no,
                "idt" => date('d-m-Y', strtotime($invoice->doc_date)),
                "val" => $invoice->value_of_supplies_made,
                "pos" => (string)$invoice->pos,
                "inv_typ" => $invoice->doc_type,
                "itms" => $items,
                "flag" => $invoice->reverse_charge,
                "sply_ty" => $invoice->supply_type,
            ];

            $rtin = $invoice->party_gstin;

            if (!isset($groupedInvoices[$rtin])) {
                $groupedInvoices[$rtin] = [
                    "rtin" => $rtin,
                    "inv" => []
                ];
            }

            $groupedInvoices[$rtin]["inv"][] = $invoiceEntry;
        }

        $ecomData["urp2b"] = array_values($groupedInvoices);

        return $ecomData;

    }

    public static function prepareEcoURP2CData($data)
    {
        $ecomData = [
                "urp2c" => []
        ];


        foreach ($data as $invoice) {
            $ecomData["urp2c"][] = [
                "pos" => (string)$invoice->pos,
                "sply_ty" => $invoice->supply_type,
                "txval" => $invoice->taxable_amt,
                "rt" => $invoice->rate,
                "iamt" => $invoice->igst,
                "camt" => $invoice->cgst,
                "samt" => $invoice->sgst,
                "flag" => $invoice->reverse_charge,
                "csamt" => $invoice->cess
            ];
        }


        return $ecomData;

    }

    public static function prepareEcomaData($data,$type){
        $ecoma = [];
        if($type == 'ecoab2b'){
            $ecoma = self::prepareEcoaB2BData($data);
        }elseif($type == 'ecoab2c'){
            $ecoma = self::prepareEcoaB2CData($data);
        }elseif($type == 'ecoaurp2b'){
            $ecoma = self::prepareEcoaUrp2bData($data);
        }elseif($type == 'ecoaurp2c'){
            $ecoma = self::prepareEcoaUrp2cData($data);
        }

        return $ecoma;
    }

    public static function prepareEcoaB2BData($data)
    {
        $ecomData = [
            "b2ba" => []
        ];

        $groupedInvoices = [];

        foreach ($data as $invoice) {
            $items = [
                [
                    "num" => rand(1000, 9999),
                    "itm_det" => [
                        "txval" => $invoice->taxable_amt,
                        "rt" => $invoice->rate,
                        "iamt" => $invoice->igst,
                        "csamt" => $invoice->cess
                    ]
                ]
            ];

            $invoiceEntry = [
                "oinum" => $invoice->doc_no,
                "oidt" => date('d-m-Y', strtotime($invoice->doc_date)),
                "inum" => $invoice->revised_doc_no,
                "idt" => date('d-m-Y', strtotime($invoice->revised_doc_date)),
                "val" => $invoice->value_of_supplies_made,
                "pos" => (string)$invoice->pos,
                "inv_typ" => $invoice->doc_type,
                "itms" => $items,
                "flag" => $invoice->reverse_charge,
                "sply_ty" => $invoice->supply_type,
            ];

            $stin = $invoice->supplier_gstin;
            $rtin = $invoice->party_gstin;
            $key = $stin . '|' . $rtin;

            if (!isset($groupedInvoices[$key])) {
                $groupedInvoices[$key] = [
                    "stin" => $stin,
                    "rtin" => $rtin,
                    "inv" => []
                ];
            }

            $groupedInvoices[$key]["inv"][] = $invoiceEntry;
        }

        $ecomData["b2ba"] = array_values($groupedInvoices);

        return $ecomData;

    }

    public static function prepareEcoaB2CData($data)
    {
        $ecomData = [
            "b2ca" => []
        ];

        $groupedByPos = [];
        foreach ($data as $invoice) {
            $pos = (string)$invoice->pos;

            $item = [
                "rt" => $invoice->rate,
                "txval" => $invoice->taxable_amt,
                "iamt" => $invoice->igst,
                "csamt" => $invoice->cess
            ];

            $invoiceData = [
                "sply_ty" => $invoice->supply_type,
                "stin" => $invoice->supplier_gstin,
                "ostin" => $invoice->supplier_gstin,
                "omon" => sprintf("%02d%04d", $invoice->month, $invoice->year),
                "itms" => [$item],
                "flag" => $invoice->reverse_charge
            ];

            if (!isset($groupedByPos[$pos])) {
                $groupedByPos[$pos] = [];
            }
            $groupedByPos[$pos][] = $invoiceData;
        }

        foreach ($groupedByPos as $pos => $invoices) {
            $ecomData["b2ca"][] = [
                "pos" => $pos,
                "posItms" => $invoices
            ];
        }

        return $ecomData;
    }

    public static function prepareEcoaUrp2bData($data)
    {
        $ecomData = [
            "urp2ba" => []
        ];

        $groupedInvoices = [];

        foreach ($data as $invoice) {
            $items = [
                [
                    "num" => rand(1000, 9999),
                    "itm_det" => [
                        "txval" => $invoice->taxable_amt,
                        "rt" => $invoice->rate,
                        "iamt" => $invoice->igst,
                        "csamt" => $invoice->cess
                    ]
                ]
            ];

            $invoiceEntry = [
                "oinum" => $invoice->doc_no,
                "oidt" => date('d-m-Y', strtotime($invoice->doc_date)),
                "inum" => $invoice->revised_doc_no,
                "idt" => date('d-m-Y', strtotime($invoice->revised_doc_date)),
                "val" => $invoice->value_of_supplies_made,
                "pos" => (string)$invoice->pos,
                "inv_typ" => $invoice->doc_type,
                "itms" => $items,
                "flag" => $invoice->reverse_charge,
                "sply_ty" => $invoice->supply_type,
            ];

            $rtin = $invoice->party_gstin;

            if (!isset($groupedInvoices[$rtin])) {
                $groupedInvoices[$rtin] = [
                    "rtin" => $rtin,
                    "inv" => []
                ];
            }

            $groupedInvoices[$rtin]["inv"][] = $invoiceEntry;
        }

        $ecomData["urp2ba"] = array_values($groupedInvoices);

        return $ecomData;
    }

    public static function prepareEcoaUrp2cData($data)
    {
        $ecomData = [
            "urp2ca" => []
        ];


        foreach ($data as $invoice) {
            $items = [
                [
                    "rt" => $invoice->rate,
                    "txval" => $invoice->taxable_amt,
                    "iamt" => $invoice->igst,
                    "csamt" => $invoice->cess
                ]
            ];

            $ecomData["urp2ca"][] = [
                "sply_ty" => $invoice->supply_type,
                "pos" => (string)$invoice->pos,
                "items" => $items,
                "omon" => sprintf("%02d%04d", $invoice->month, $invoice->year),
                "flag" => $invoice->reverse_charge,
            ];
        }


        return $ecomData;

    }

    public static function prepareDocIssueData($data){
        $docIssueData = [];

        foreach ($data as $invoice) {
            $docNum = $invoice->doc_no;
            $docType = $invoice->doc_type;
            $netIssue = $invoice->total_number - $invoice->cancelled;

            $docs = [
                "num" => $docNum,
                "to" => $invoice->sr_no_to,
                "from" => $invoice->sr_no_from,
                "totnum" => $invoice->total_number,
                "cancel" => $invoice->cancelled,
                "net_issue" => $netIssue
            ];

            if (!isset($docIssueData[$docType])) {
                $docIssueData[$docType] = [
                    "doc_num" => $docNum,
                    "doc_typ" => $docType,
                    "docs" => []
                ];
            }

            $docIssueData[$docType]["docs"][] = $docs;
        }

        $output = [
            "doc_det" => array_values($docIssueData)
        ];

        return $output;
    }

    public static function prepareAtData($data){
        $invoicesArray = [];
        $data = $data->groupBy('pos');

        foreach ($data as $pos => $invoices) {
            $invArray = [];

            foreach ($invoices as $invoice) {
                $invArray[] = [
                        "rt" => $invoice->rate,
                        "ad_amt" => $invoice->taxable_amt,
                        "iamt" => $invoice->igst,
                        "csamt" => $invoice->cess,
                ];
            }

            $invoicesArray[] = [
                "pos" => $pos,
                "itms" => $invArray
            ];
        }
        return $invoicesArray;
    }

    public static function prepareAtaData($data){
        $invoiceData = [];

        foreach ($data as $invoice) {
            $items = [
                [
                    "rt" => $invoice->rate,
                    "ad_amt" => $invoice->taxable_amt,
                    "iamt" => $invoice->igst,
                    "csamt" => $invoice->cess,
                ]
            ];

            $invarr["omon"] = sprintf("%02d%04d", $invoice->month, $invoice->year);

            if($invoice->pos){
                $invarr["pos"] = $invoice->pos;
            }

            $invarr["itms"] = $items;

            if($invoice->supply_type){
                $invarr["sply_typ"] = $invoice->supply_type;
            }

            if (!empty($invoice->applicable_tax_rate)) {
                $invarr["diff_percent"] = $invoice->applicable_tax_rate;
            }

            $invoiceData[] = $invarr;
        }
        return $invoiceData;
    }

    public static function prepareTxpdData($data){
        $invoiceData = [];

        foreach ($data as $invoice) {
            $items = [
                [
                    "rt" => $invoice->rate,
                    "ad_amt" => $invoice->taxable_amt,
                    "iamt" => $invoice->igst,
                    "csamt" => $invoice->cess,
                ]
            ];

            if($invoice->pos){
                $invarr["pos"] = $invoice->pos;
            }

            $invarr["itms"] = $items;

            if($invoice->supply_type){
                $invarr["sply_typ"] = $invoice->supply_type;
            }

            if (!empty($invoice->applicable_tax_rate)) {
                $invarr["diff_percent"] = $invoice->applicable_tax_rate;
            }

            $invoiceData[] = $invarr;
        }
        return $invoiceData;
    }

    public static function prepareTxpdaData($data){
        $invoiceData = [];

        foreach ($data as $invoice) {
            $items = [
                [
                    "rt" => $invoice->rate,
                    "ad_amt" => $invoice->taxable_amt,
                    "iamt" => $invoice->igst,
                    "csamt" => $invoice->cess,
                ]
            ];

            $invarr["omon"] = sprintf("%02d%04d", $invoice->month, $invoice->year);

            if($invoice->pos){
                $invarr["pos"] = $invoice->pos;
            }

            $invarr["itms"] = $items;

            if($invoice->supply_type){
                $invarr["sply_typ"] = $invoice->supply_type;
            }

            if (!empty($invoice->applicable_tax_rate)) {
                $invarr["diff_percent"] = $invoice->applicable_tax_rate;
            }

            $invoiceData[] = $invarr;
        }
        return $invoiceData;
    }

    public static function prepareNilData($data){
        $invoiceArr = [];

        foreach ($data as $invoice) {
            $invoiceArr[] = [
                    "sply_typ" => $invoice->supply_type,
                    "expt_amt" => $invoice->expt_amt,
                    "nil_amt" => $invoice->nil_amt,
                    "ngsup_amt" => $invoice->non_gst_amt,
            ];

        }

        $invoiceData['inv'] = $invoiceArr;

        return $invoiceData;
    }

    public static function prepareExpData($data){
        $invoicesArray = [];
        $data = $data->groupBy('exp_type');

        foreach ($data as $expType => $invoices) {
            $invArray = [];

            foreach ($invoices as $invoice) {
                 $items = [
                    [
                        "txval" => $invoice->taxable_amt,
                        "rt" => $invoice->rate,
                        "iamt" => $invoice->igst,
                        "csamt" => $invoice->cess
                    ]
                ];

                $invoiceData = [
                    "inum" => $invoice->invoice_no,
                    "idt" => date('d-m-Y', strtotime($invoice->invoice_date)),
                    "sbpcode" => $invoice->port_code,
                    "sbnum" => $invoice->shipping_bill_no,
                    "sbdt" => date('d-m-Y', strtotime($invoice->shipping_bill_date)),
                    "val" => $invoice->invoice_amt,
                    "itms" => $items
                ];

                $invArray[] = $invoiceData;
            }

            $invoicesArray[] = [
                "exp_type" => $expType,
                "inv" => $invArray
            ];
        }
        return $invoicesArray;
    }

    public static function prepareExpaData($data){
        $invoicesArray = [];
        $data = $data->groupBy('exp_type');

        foreach ($data as $expType => $invoices) {
            $invArray = [];

            foreach ($invoices as $invoice) {
                 $items = [
                    [
                        "txval" => $invoice->taxable_amt,
                        "rt" => $invoice->rate,
                        "iamt" => $invoice->igst,
                        "csamt" => $invoice->cess
                    ]
                ];

                $invoiceData = [
                    "oinum" => $invoice->invoice_no,
                    "oidt" => date('d-m-Y', strtotime($invoice->invoice_date)),
                    "inum" => $invoice->revised_invoice_no,
                    "idt" => date('d-m-Y', strtotime($invoice->revised_invoice_date)),
                    "sbnum" => $invoice->shipping_bill_no,
                    "sbdt" => date('d-m-Y', strtotime($invoice->shipping_bill_date)),
                    "sbpcode" => $invoice->port_code,
                    "val" => $invoice->invoice_amt,
                    "itms" => $items
                ];

                $invArray[] = $invoiceData;
            }

            $invoicesArray[] = [
                "exp_type" => $expType,
                "inv" => $invArray
            ];
        }
        return $invoicesArray;
    }

    public static function prepareHsnData($data){
        $invoicesArray = [];
        foreach ($data as $invoice) {
            $invoicesArray[] = [
                "num" => rand(1000, 9999),
                "hsn_sc" => $invoice->hsn_code,
                "desc" => $invoice->description,
                "uqc" => $invoice->uqc,
                "qty" => $invoice->qty,
                "rt" => $invoice->rate,
                "txval" => $invoice->taxable_amt,
                "iamt" => $invoice->igst,
                "camt" => $invoice->cgst,
                "samt" => $invoice->sgst,
                "csamt" => $invoice->cess
            ];

        }

        return $invoicesArray;
    }

    public static function pushSalesInvoiceVoucherData(int $docId)
    {
        $documentHeader = ErpSaleInvoice::find($docId);
        if (!isset($documentHeader) && !$documentHeader) {
            return array(
                'status' => false,
                'message' => 'Transaction not found'
            );
        }
        //Check for only sales invoice
        if (!($documentHeader->document_type === ConstantHelper::SI_SERVICE_ALIAS
        || ($documentHeader->document_type === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS))) {
            return array(
                'status' => true,
                'message' => 'Not Required',
                'data' => array()
            );
        }
        //Retrieve voucher
        $voucher = $documentHeader->voucher;
        if (!isset($voucher) && !$voucher) {
            return array(
                'status' => false,
                'message' => 'Voucher not found'
            );
        }
        //Retrieve party details
        $party = $documentHeader?->customer;
        if (!isset($party) && !$party) {
            return array(
                'status' => false,
                'message' => 'Party details not found'
            );
        }
        $partyName = $party->company_name;
        $partyGSTIN = $party->compliances?->gstin_no;
        // if (!$partyName || !$partyGSTIN) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Party details not found'
        //     );
        // }
        //Get orgnization and it's GSTIN
        $organization = Organization::find($documentHeader -> organization_id);
        if (!isset($organization) && !$organization) {
            return array(
                'status' => false,
                'message' => 'Supplier details not found'
            );
        }
        //Orgnization GSTIN
        $supplierGSTIN = $organization->gst_number ?? env('EINVOICE_GSTIN', '');
        $supplierName = $organization->name;
        $documentDate = Carbon::parse($documentHeader -> document_date);
        $year = Carbon::parse($documentDate) -> year;
        $month = Carbon::parse($documentDate) -> month;

        //Place of supply
        $shippingAddress = $documentHeader -> shipping_address_details;
        $stateId = $shippingAddress ?-> state_id;
        $placeOfSupply = State::find($stateId);
        $placeOfSupplyName = $placeOfSupply ?-> name;

        //Envoice Type
        $invoiceType = ErpGstInvoiceType::where('name', strtolower($documentHeader ->  gst_invoice_type)) -> first();

        $gstRItemRow = [];

        $gstRData = [
            'voucher_id' => $voucher -> id,
            'invoice_id' => $documentHeader -> id,
            'invoice_no' => $documentHeader -> document_number,
            'invoice_date' => $documentHeader -> document_date,
            // 'revised_invoice_no' => null, //TBC
            // 'revised_invoice_date' => null, //TBC
            // 'supply_type' => null, //TBC
            'invoice_type' => 'R',
            'invoice_type_id' => $invoiceType ?-> id, //TBC
            'group_id' => $documentHeader -> group_id,
            'company_id' => $documentHeader -> company_id,
            'organization_id' => $documentHeader -> organization_id,
            'party_name' => $partyName,
            'party_gstin' => $partyGSTIN,
            // 'voucher_type' => null, //TBC
            'voucher_no' => $voucher -> voucher_no,
            'taxable_amt' => $documentHeader -> total_amount - $documentHeader -> total_tax_value,
            'pos' => $placeOfSupply ?-> state_code, //TBC
            'place_of_supply' => $placeOfSupplyName, //TBC
            // 'reverse_charge' => 'N', //TBC
            // 'uqc' => null,
            // 'e_commerce_gstin' => null, //TBC
            // 'revised_ecom_gstin' => null, //TBC
            // 'ecom_operator_name' => null, //TBC
            'invoice_amt' => $documentHeader->total_amount,
            // 'applicable_tax_rate' => null,
            // 'is_conflict' => null,
            // 'conflict_msg' => null,
            // 'note_date' => null, //TBC
            // 'note_type' => null, //TBC
            // 'note_value' => null, //TBC
            // 'note_number' => null, //TBC
            // 'revised_note_no' => null, //TBC
            // 'revised_note_date' => null, //TBC
            // 'ur_type' => null, //TBC
            // 'exp_type' => null, //TBC
            // 'port_code' => null, //TBC
            // 'shipping_bill_no' => null, //TBC
            // 'shipping_bill_date' => null, //TBC
            // 'description' => null, //TBC
            // 'expt_amt' => null, //TBC
            // 'non_gst_amt' => null, //TBC
            // 'nil_amt' => null, //TBC
            // 'qty' => null, //TBC
            // 'nature_of_document' => null, //TBC
            // 'sr_no_from' => null, //TBC
            // 'sr_no_to' => null, //TBC
            // 'total_number' => null, //TBC
            // 'cancelled' => null, //TBC
            // 'net_value_of_supplies' => null, //TBC
            'supplier_gstin' => $supplierGSTIN,
            'supplier_name' => $supplierName,
            // 'doc_no' => null, //TBC
            // 'doc_date' => null, //TBC
            // 'revised_doc_no' => null, //TBC
            // 'revised_doc_date' => null, //TBC
            // 'doc_type' => null, //TBC
            // 'value_of_supplies_made' => null, //TBC
            'year' => $year,
            'month' => $month,
        ];

        foreach ($documentHeader -> items as $itemData) {
            $gstRItemRow[] = array_merge($gstRData, [
                'rate' => $itemData -> sgst_value['rate'] + $itemData -> cgst_value['rate'] + $itemData -> igst_value['rate'],
                'sgst' => $itemData -> sgst_value['value'],
                'cgst' => $itemData -> cgst_value['value'],
                'igst' => $itemData -> igst_value['value'],
                'cess' => $itemData -> cess_value['value'],
                'hsn_code' => $itemData -> hsn ?-> code
            ]);
        }

        return array(
            'data' => $gstRItemRow,
            'message' => 'GSTR data returned successfully',
            'status' => 'success'
        );
    }

    public static function pushSalesReturnVoucherData(int $docId)
    {
        $documentHeader = ErpSaleReturn::find($docId);
        if (!isset($documentHeader) && !$documentHeader) {
            return array(
                'status' => false,
                'message' => 'Transaction not found'
            );
        }
        //Retrieve voucher
        $voucher = $documentHeader->voucher;
        if (!isset($voucher) && !$voucher) {
            return array(
                'status' => false,
                'message' => 'Voucher not found'
            );
        }
        //Retrieve party details
        $party = $documentHeader?->customer;
        if (!isset($party) && !$party) {
            return array(
                'status' => false,
                'message' => 'Party details not found'
            );
        }
        $partyName = $party->company_name;
        $partyGSTIN = $party->compliances?->gstin_no;
        // if (!$partyName || !$partyGSTIN) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Party details not found'
        //     );
        // }
        //Get orgnization and it's GSTIN
        $organization = Organization::find($documentHeader -> organization_id);
        if (!isset($organization) && !$organization) {
            return array(
                'status' => false,
                'message' => 'Supplier details not found'
            );
        }
        //Orgnization GSTIN
        $supplierGSTIN = $organization->gst_number ?? env('EINVOICE_GSTIN', '');
        $supplierName = $organization->name;
        $documentDate = Carbon::parse($documentHeader -> document_date);
        $year = Carbon::parse($documentDate) -> year;
        $month = Carbon::parse($documentDate) -> month;

        //Place of supply
        $shippingAddress = $documentHeader -> shipping_address_details;
        $stateId = $shippingAddress ?-> state_id;
        $placeOfSupply = State::find($stateId);
        $placeOfSupplyName = $placeOfSupply ?-> name;

        //Envoice Type
        $invoiceType = ErpGstInvoiceType::where('name', EInvoiceHelper::CREDIT_NOTE_INVOICE_TYPE) -> first();

        $gstRItemRow = [];

        $gstRData = [
            'voucher_id' => $voucher -> id,
            'invoice_id' => $documentHeader -> id,
            'invoice_no' => $documentHeader -> document_number,
            'invoice_date' => $documentHeader -> document_date,
            // 'revised_invoice_no' => null, //TBC
            // 'revised_invoice_date' => null, //TBC
            // 'supply_type' => null, //TBC
            'invoice_type' => $documentHeader -> gst_invoice_type,
            'invoice_type_id' => $invoiceType ?-> id, //TBC
            'group_id' => $documentHeader -> group_id,
            'company_id' => $documentHeader -> company_id,
            'organization_id' => $documentHeader -> organization_id,
            'party_name' => $partyName,
            'party_gstin' => $partyGSTIN,
            // 'voucher_type' => null, //TBC
            'voucher_no' => $voucher -> voucher_no,
            'taxable_amt' => $documentHeader -> total_amount - $documentHeader -> total_tax_value,
            'pos' => $placeOfSupply ?-> state_code, //TBC
            'place_of_supply' => $placeOfSupplyName, //TBC
            'reverse_charge' => 'N', //TBC
            // 'uqc' => null,
            // 'e_commerce_gstin' => null, //TBC
            // 'revised_ecom_gstin' => null, //TBC
            // 'ecom_operator_name' => null, //TBC
            'invoice_amt' => $documentHeader->total_amount,
            // 'applicable_tax_rate' => null,
            // 'is_conflict' => null,
            // 'conflict_msg' => null,
            // 'note_date' => null, //TBC
            // 'note_type' => null, //TBC
            // 'note_value' => null, //TBC
            // 'note_number' => null, //TBC
            // 'revised_note_no' => null, //TBC
            // 'revised_note_date' => null, //TBC
            // 'ur_type' => null, //TBC
            // 'exp_type' => null, //TBC
            // 'port_code' => null, //TBC
            // 'shipping_bill_no' => null, //TBC
            // 'shipping_bill_date' => null, //TBC
            // 'description' => null, //TBC
            // 'expt_amt' => null, //TBC
            // 'non_gst_amt' => null, //TBC
            // 'nil_amt' => null, //TBC
            // 'qty' => null, //TBC
            // 'nature_of_document' => null, //TBC
            // 'sr_no_from' => null, //TBC
            // 'sr_no_to' => null, //TBC
            // 'total_number' => null, //TBC
            // 'cancelled' => null, //TBC
            // 'net_value_of_supplies' => null, //TBC
            'supplier_gstin' => $supplierGSTIN,
            'supplier_name' => $supplierName,
            // 'doc_no' => null, //TBC
            // 'doc_date' => null, //TBC
            // 'revised_doc_no' => null, //TBC
            // 'revised_doc_date' => null, //TBC
            // 'doc_type' => null, //TBC
            // 'value_of_supplies_made' => null, //TBC
            'year' => $year,
            'month' => $month,
        ];

        foreach ($documentHeader -> items as $itemData) {
            $gstRItemRow[] = array_merge($gstRData, [
                'rate' => $itemData -> sgst_value['rate'] + $itemData -> cgst_value['rate'] + $itemData -> igst_value['rate'],
                'sgst' => $itemData -> sgst_value['value'],
                'cgst' => $itemData -> cgst_value['value'],
                'igst' => $itemData -> igst_value['value'],
                'cess' => $itemData -> cess_value['value'],
                'hsn_code' => $itemData -> hsn ?-> code
            ]);
        }

        return array(
            'data' => $gstRItemRow,
            'message' => 'GSTR data returned successfully',
            'status' => 'success'
        );
    }

    public static function pushPurchaseReturnVoucherData(int $docId)
    {
        $documentHeader = PRHeader::find($docId);
        if (!isset($documentHeader) && !$documentHeader) {
            return array(
                'status' => false,
                'message' => 'Transaction not found'
            );
        }
        //Retrieve voucher
        $voucher = $documentHeader->voucher;
        if (!isset($voucher) && !$voucher) {
            return array(
                'status' => false,
                'message' => 'Voucher not found'
            );
        }
        //Retrieve party details
        $party = $documentHeader?->customer;
        if (!isset($party) && !$party) {
            return array(
                'status' => false,
                'message' => 'Party details not found'
            );
        }
        $partyName = $party->company_name;
        $partyGSTIN = $party->compliances?->gstin_no;
        // if (!$partyName || !$partyGSTIN) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Party details not found'
        //     );
        // }
        //Get orgnization and it's GSTIN
        $organization = Organization::find($documentHeader -> organization_id);
        if (!isset($organization) && !$organization) {
            return array(
                'status' => false,
                'message' => 'Supplier details not found'
            );
        }
        //Orgnization GSTIN
        $supplierGSTIN = $organization->gst_number ?? env('EINVOICE_GSTIN', '');
        $supplierName = $organization->name;
        $documentDate = Carbon::parse($documentHeader -> document_date);
        $year = Carbon::parse($documentDate) -> year;
        $month = Carbon::parse($documentDate) -> month;

        //Place of supply
        $shippingAddress = $documentHeader -> shipping_address_details;
        $stateId = $shippingAddress ?-> state_id;
        $placeOfSupply = State::find($stateId);
        $placeOfSupplyName = $placeOfSupply ?-> name;

        //Envoice Type
        $invoiceType = ErpGstInvoiceType::where('name', strtolower(EInvoiceHelper::CREDIT_NOTE_INVOICE_TYPE)) -> first();

        $gstRItemRow = [];

        $gstRData = [
            'voucher_id' => $voucher -> id,
            'invoice_id' => $documentHeader -> id,
            'invoice_no' => $documentHeader -> document_number,
            'invoice_date' => $documentHeader -> document_date,
            // 'revised_invoice_no' => null, //TBC
            // 'revised_invoice_date' => null, //TBC
            // 'supply_type' => null, //TBC
            'invoice_type' => 'R',
            'invoice_type_id' => $invoiceType ?-> id, //TBC
            'group_id' => $documentHeader -> group_id,
            'company_id' => $documentHeader -> company_id,
            'organization_id' => $documentHeader -> organization_id,
            'party_name' => $partyName,
            'party_gstin' => $partyGSTIN,
            // 'voucher_type' => null, //TBC
            'voucher_no' => $voucher -> voucher_no,
            'taxable_amt' => $documentHeader -> total_amount - $documentHeader -> total_tax_value,
            'pos' => $placeOfSupply ?-> state_code, //TBC
            'place_of_supply' => $placeOfSupplyName, //TBC
            'reverse_charge' => 'N', //TBC
            // 'uqc' => null,
            // 'e_commerce_gstin' => null, //TBC
            // 'revised_ecom_gstin' => null, //TBC
            // 'ecom_operator_name' => null, //TBC
            'invoice_amt' => $documentHeader->total_amount,
            // 'applicable_tax_rate' => null,
            // 'is_conflict' => null,
            // 'conflict_msg' => null,
            // 'note_date' => null, //TBC
            // 'note_type' => null, //TBC
            // 'note_value' => null, //TBC
            // 'note_number' => null, //TBC
            // 'revised_note_no' => null, //TBC
            // 'revised_note_date' => null, //TBC
            // 'ur_type' => null, //TBC
            // 'exp_type' => null, //TBC
            // 'port_code' => null, //TBC
            // 'shipping_bill_no' => null, //TBC
            // 'shipping_bill_date' => null, //TBC
            // 'description' => null, //TBC
            // 'expt_amt' => null, //TBC
            // 'non_gst_amt' => null, //TBC
            // 'nil_amt' => null, //TBC
            // 'qty' => null, //TBC
            // 'nature_of_document' => null, //TBC
            // 'sr_no_from' => null, //TBC
            // 'sr_no_to' => null, //TBC
            // 'total_number' => null, //TBC
            // 'cancelled' => null, //TBC
            // 'net_value_of_supplies' => null, //TBC
            'supplier_gstin' => $supplierGSTIN,
            'supplier_name' => $supplierName,
            // 'doc_no' => null, //TBC
            // 'doc_date' => null, //TBC
            // 'revised_doc_no' => null, //TBC
            // 'revised_doc_date' => null, //TBC
            // 'doc_type' => null, //TBC
            // 'value_of_supplies_made' => null, //TBC
            'year' => $year,
            'month' => $month,
        ];

        foreach ($documentHeader -> items as $itemData) {
            $gstRItemRow[] = array_merge($gstRData, [
                'rate' => $itemData -> sgst_value['rate'] + $itemData -> cgst_value['rate'] + $itemData -> igst_value['rate'],
                'sgst' => $itemData -> sgst_value['value'],
                'cgst' => $itemData -> cgst_value['value'],
                'igst' => $itemData -> igst_value['value'],
                'cess' => $itemData -> cess_value['value'],
                'hsn_code' => $itemData -> hsn ?-> code
            ]);
        }

        return array(
            'data' => $gstRItemRow,
            'message' => 'GSTR data returned successfully',
            'status' => 'success'
        );
    }

    public static function pushVoucherDataToGstrTable(string $referenceService, int $referenceDocId)
    {
        $gstrData = array();
        if ($referenceService === ConstantHelper::SI_SERVICE_ALIAS || $referenceService === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
            $response = self::pushSalesInvoiceVoucherData($referenceDocId);
            if ($response['status'] === 'success' && isset($response['data'])) {
                $gstrData = $response['data'];
            } else {
                return array(
                    'message' => $response['message'],
                    'status' => false
                );
            }
        }
        else if ($referenceService === ConstantHelper::SR_SERVICE_ALIAS) {
            $response = self::pushSalesReturnVoucherData($referenceDocId);
            if ($response['status'] === 'success' && isset($response['data'])) {
                $gstrData = $response['data'];
            }
            else {
                return array(
                    'message' => $response['message'],
                    'status' => false
                );
            }
        }
        else if ($referenceService === ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS) {
            $response = self::pushPurchaseReturnVoucherData($referenceDocId);
            if ($response['status'] === 'success' && isset($response['data'])) {
                $gstrData = $response['data'];
            }
            else {
                return array(
                    'message' => $response['message'],
                    'status' => false
                );
            }
        //Default case if GSTR data is not required
        }
        else {
            return array(
                'message' => 'Voucher posted successfully',
                'status' => true
            );
        }
        //GSTR data set
        if ($gstrData && count($gstrData) > 0) {
            GstrCompiledData::insert($gstrData);
            return array(
                'message' => 'Voucher posted successfully',
                'status' => true
            );
        //No data found for GSTR
        } else {
            return array(
                'message' => 'Voucher posted successfully',
                'status' => true
            );
        }
    }

}
