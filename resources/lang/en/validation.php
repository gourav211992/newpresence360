<?php

return [
    'required' => '*Required',
    'required_if' => '*Required',
    'unique' => 'The :attribute has already been taken.',
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    // Add other validation messages as needed
    'attributes' => [
        'attributes.*.attribute_group_id' => 'Attribute Group ID',
        'attributes.*.attribute_id' => 'Attribute',
        'component_item_name.*' => 'Item',
        'components.*.qty' => 'Consumption',
        'vendor_id' => 'vendor',
        'currency_id' => 'currency',
        'payment_term_id' => 'payment term',
    ],
];
