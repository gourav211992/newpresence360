<?php

namespace App\Helpers\RefinedIndex;
use App\Models\Legal;

class indexFilterHelper
{

    const Index_FILTERS = [
        [
            'colSpan' => 'auto',
            'label' => 'Services',
            'id' => 'doc_service_filter',
            'requestName' => 'services',
            'term' => 'document_services',
            'value_key' => 'id',
            'label_key' => 'name',
            'type' => 'auto_complete'
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Doc No',
            'id' => 'doc_number_filter',
            'requestName' => 'document_number',
            'term' => 'index_documents',
            'value_key' => 'id',
            'label_key' => 'document_number',
            'type' => 'auto_complete',
            'dependent' => ['doc_service_filter']

        ],
        [
            'colSpan' => 'auto',
            'label' => 'Party',
            'id' => 'party_filter',
            'requestName' => 'party_id',
            'term' => 'party_name',
            'value_key' => 'id',
            'label_key' => 'item_name',
            'type' => 'auto_complete'
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Status',
            'id' => 'status',
            'requestName' => 'status',
            'term' => 'document_statuses',
            'value_key' => 'id',
            'label_key' => 'name',
            'type' => 'auto_complete'
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Company',
            'id' => 'company_filter',
            'requestName' => 'company_id',
            'term' => 'companies',
            'value_key' => 'id',
            'label_key' => 'name',
            'type' => 'auto_complete',
            'dependent' => ['organization_filter']

        ],
        [
            'colSpan' => 'auto',
            'label' => 'Organization',
            'id' => 'organization_filter',
            'requestName' => 'organization_id',
            'term' => 'organizations',
            'value_key' => 'id',
            'label_key' => 'name',
            'type' => 'auto_complete',
            'dependent' => ['location_filter']
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Location',
            'id' => 'store_name',
            'requestName' => 'location_id',
            'term' => 'location',
            'value_key' => 'id',
            'label_key' => 'store_name',
            'type' => 'auto_complete'
        ],
    ];
}
